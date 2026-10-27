<?php

namespace App\Generator;

use App\Entity\Mouvement;
use App\Entity\Wod;
use App\Entity\WodMouvement;
use App\Enum\Categorie;
use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\Unite;
use App\Enum\WodType;
use App\Repository\MouvementRepository;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * Le cœur de Genwod : compose un WOD équilibré à partir du catalogue de mouvements.
 *
 * Règles principales :
 *  - on ne propose que des mouvements faisables avec le matériel choisi ;
 *  - on varie les familles (cardio, gymnastique, haltéro…) pour équilibrer la séance ;
 *  - les volumes dépendent du format, de la durée et de la difficulté, puis sont arrondis
 *    à des valeurs « naturelles » (15, 21, 400 m…) ;
 *  - tout est déterministe pour une graine donnée.
 */
final class WodGenerator
{
    private const NOMS = ['Tempête', 'Avalanche', 'Forge', 'Fournaise', 'Tornade', 'Déferlante', 'Éruption', 'Furie', 'Foudre', 'Marée', 'Rafale', 'Secousse', 'Canicule', 'Bourrasque', 'Charge'];
    private const COMPLEMENTS = ['de fer', 'd\'acier', 'nocturne', 'sauvage', 'polaire', 'écarlate', 'sans fin', 'du lundi', 'de minuit', 'brûlante', 'magnétique', 'électrique', 'souterraine', 'infernale', 'de l\'aube'];
    private const REPS_NATURELLES = [3, 5, 6, 7, 8, 9, 10, 12, 14, 15, 16, 18, 20, 21, 24, 25, 30, 35, 40, 50, 60, 75, 80, 100, 150];
    private const METRES_NATURELS = [100, 150, 200, 250, 300, 400, 500, 600, 800, 1000, 1200, 1500, 2000];

    public function __construct(private readonly MouvementRepository $mouvements)
    {
    }

    public function generer(CriteresGeneration $criteres): Wod
    {
        $rng = new Randomizer(new Xoshiro256StarStar(hash('sha256', serialize($criteres->versQuery()), true)));
        $catalogue = $this->catalogueDisponible($criteres);
        if ([] === $catalogue) {
            throw new \LogicException('Le catalogue de mouvements est vide : lancez « php bin/console app:catalogue:importer ».');
        }

        $wod = (new Wod())
            ->setType($criteres->type)
            ->setDifficulte($criteres->difficulte)
            // Un Death By s'arrête à l'échec : la durée n'est qu'un time cap, qui doit laisser le temps de monter.
            ->setDuree(WodType::DeathBy === $criteres->type ? max(10, $criteres->duree) : $criteres->duree)
            ->setGraine($criteres->graine)
            ->setTitre(self::NOMS[$rng->getInt(0, \count(self::NOMS) - 1)].' '.self::COMPLEMENTS[$rng->getInt(0, \count(self::COMPLEMENTS) - 1)]);

        [$nbMouvements, $tours, $facteur] = $this->structure($criteres, $rng);
        $wod->setTours($tours);

        foreach ($this->choisirMouvements($catalogue, $nbMouvements, $criteres, $rng) as $mouvement) {
            $volume = $mouvement->getVolumeBase() * $facteur * $criteres->difficulte->facteurVolume();
            $quantite = WodType::DeathBy === $criteres->type
                // Death By : la quantité est le pas ajouté chaque minute (1 burpee, 2 air squats…).
                ? max(1, min(5, (int) round($volume)))
                : $this->arrondir($volume, $mouvement->getUnite());
            $wod->addLigne((new WodMouvement())->setMouvement($mouvement)->setQuantite($quantite));
        }
        $wod->setConsignes($this->consignes($wod));

        return $wod;
    }

    /** @return list<Mouvement> */
    private function catalogueDisponible(CriteresGeneration $criteres): array
    {
        $materiel = [Equipement::Aucun, ...$criteres->equipements];

        return array_values(array_filter(
            $this->mouvements->findBy(['actif' => true], ['id' => 'ASC']),
            static fn (Mouvement $m) => \in_array($m->getEquipement(), $materiel, true)
                // Pas de mouvements très techniques en facile.
                && !($m->isTechnique() && Difficulte::Facile === $criteres->difficulte)
                // Un Death By se compte en répétitions, sur un mouvement qui se dégrade : pas de cardio.
                && !(WodType::DeathBy === $criteres->type && (Unite::Reps !== $m->getUnite() || Categorie::Cardio === $m->getCategorie())),
        ));
    }

    /**
     * @return array{0: int, 1: int|null, 2: float} [nombre de mouvements, tours imposés, facteur de volume]
     */
    private function structure(CriteresGeneration $c, Randomizer $rng): array
    {
        return match ($c->type) {
            WodType::ForTime => match (true) {
                $c->duree <= 10 => [3, 3, 0.8],
                $c->duree <= 20 => (1 === $rng->getInt(0, 1)) ? [3, 5, 0.8] : [3, 3, 1.1],
                // Long time cap : un « chipper » (une seule fois, gros volumes) ou 5 tours.
                default => (1 === $rng->getInt(0, 1)) ? [5, 1, 3.0] : [3, 5, 1.1],
            },
            WodType::Amrap => [$c->duree <= 12 ? 3 : 4, null, $c->duree >= 20 ? 1.2 : 1.0],
            WodType::Emom => [$this->mouvementsEmom($c->duree), null, 0.55],
            // Un seul mouvement ; le facteur ramène le volume de base à un pas par minute (burpees 12 → 1).
            WodType::DeathBy => [1, null, 0.1],
        };
    }

    /** En EMOM on préfère un nombre de mouvements qui divise la durée (cycles complets). */
    private function mouvementsEmom(int $duree): int
    {
        foreach ([4, 3, 2] as $nb) {
            if (0 === $duree % $nb) {
                return $nb;
            }
        }

        return 3;
    }

    /**
     * Sélection équilibrée : on tourne entre les familles de mouvements pour ne pas
     * enchaîner deux mouvements qui sollicitent la même chose. Au plus un cardio et un
     * exercice statique (gainage…), et le matériel choisi par l'athlète est privilégié.
     *
     * @param list<Mouvement> $catalogue
     *
     * @return list<Mouvement>
     */
    private function choisirMouvements(array $catalogue, int $nombre, CriteresGeneration $c, Randomizer $rng): array
    {
        $parFamille = [];
        foreach ($rng->shuffleArray($catalogue) as $mouvement) {
            $parFamille[$mouvement->getCategorie()->value][] = $mouvement;
        }
        // Dans chaque famille, les mouvements utilisant le matériel choisi passent devant.
        foreach ($parFamille as &$mouvements) {
            usort($mouvements, static fn (Mouvement $a, Mouvement $b) => (Equipement::Aucun === $a->getEquipement()) <=> (Equipement::Aucun === $b->getEquipement()));
        }
        unset($mouvements);

        // Les familles qui exploitent le matériel choisi d'abord, le cardio en tête pour un For Time / AMRAP.
        $avecMateriel = [];
        foreach ($parFamille as $famille => $mouvements) {
            $avecMateriel[$famille] = Equipement::Aucun !== $mouvements[0]->getEquipement();
        }
        $familles = $rng->shuffleArray(array_keys($parFamille));
        $rang = static fn (string $famille): int => match (true) {
            Categorie::Cardio->value === $famille && \in_array($c->type, [WodType::ForTime, WodType::Amrap], true) => 0,
            $avecMateriel[$famille] ?? false => 1,
            default => 2,
        };
        usort($familles, static fn (string $a, string $b) => $rang($a) <=> $rang($b));

        $choisis = [];
        $cardioPris = false;
        $statiquePris = false;
        while (\count($choisis) < $nombre && [] !== $familles) {
            foreach ($familles as $i => $famille) {
                if (\count($choisis) >= $nombre) {
                    break;
                }
                if (Categorie::Cardio->value === $famille && $cardioPris) {
                    unset($familles[$i]);
                    continue;
                }
                $mouvement = array_shift($parFamille[$famille]);
                while (null !== $mouvement && $statiquePris && Unite::Secondes === $mouvement->getUnite()) {
                    $mouvement = array_shift($parFamille[$famille]);
                }
                if (null === $mouvement) {
                    unset($familles[$i]);
                    continue;
                }
                $choisis[] = $mouvement;
                $cardioPris = $cardioPris || Categorie::Cardio->value === $famille;
                $statiquePris = $statiquePris || Unite::Secondes === $mouvement->getUnite();
            }
        }

        return $choisis;
    }

    private function arrondir(float $valeur, Unite $unite): int
    {
        $candidats = match ($unite) {
            Unite::Metres => self::METRES_NATURELS,
            Unite::Secondes => range(30, 180, 15),
            default => self::REPS_NATURELLES,
        };

        $meilleur = $candidats[0];
        foreach ($candidats as $candidat) {
            if (abs($candidat - $valeur) < abs($meilleur - $valeur)) {
                $meilleur = $candidat;
            }
        }

        return $meilleur;
    }

    private function consignes(Wod $wod): string
    {
        $lignes = [$wod->getType()->explication()];
        $lignes[] = match ($wod->getType()) {
            WodType::ForTime => 1 === $wod->getTours()
                ? 'Chipper : chaque mouvement une seule fois, dans l\'ordre.'
                : \sprintf('Enchaînez %d tours complets.', $wod->getTours()),
            WodType::Amrap => 'Notez vos tours complets et les répétitions du tour entamé.',
            WodType::Emom => \sprintf('Minute 1 : mouvement 1, minute 2 : mouvement 2… en boucle pendant %d minutes.', $wod->getDuree()),
            WodType::DeathBy => \sprintf('Score : minutes complétées + répétitions de la minute ratée (time cap %d min).', $wod->getDuree()),
        };
        $lignes[] = 'Échauffement conseillé : 10 minutes, en reprenant les mouvements du jour à vide.';

        return implode("\n", $lignes);
    }
}
