<?php

namespace App\Service;

use App\Entity\Mouvement;
use App\Entity\Wod;
use App\Entity\WodMouvement;
use App\Enum\Categorie;
use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\Unite;
use App\Enum\WodType;
use App\Repository\MouvementRepository;
use App\Repository\WodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Importe (ou met à jour) le catalogue de mouvements et les WODs éditoriaux.
 * Idempotent : on peut le relancer à chaque déploiement.
 */
final class CatalogueImporter
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MouvementRepository $mouvements,
        private readonly WodRepository $wods,
        #[Autowire('%kernel.project_dir%/config/donnees/catalogue.php')]
        private readonly string $fichier,
    ) {
    }

    /** @return array{mouvements: int, wods: int} nombre d'éléments créés */
    public function importer(): array
    {
        /** @var array{mouvements: list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: int, 6: ?string, 7: bool, 8: string}>, wods: list<array{0: string, 1: string, 2: string, 3: int, 4: ?int, 5: string, 6: list<array{0: string, 1: int}>}>} $donnees */
        $donnees = require $this->fichier;
        $crees = ['mouvements' => 0, 'wods' => 0];

        $parSlug = [];
        foreach ($donnees['mouvements'] as [$slug, $nom, $categorie, $equipement, $unite, $volume, $charge, $technique, $description]) {
            $mouvement = $this->mouvements->findOneBy(['slug' => $slug]);
            if (null === $mouvement) {
                $mouvement = (new Mouvement())->setSlug($slug);
                $this->em->persist($mouvement);
                ++$crees['mouvements'];
            }
            $mouvement
                ->setNom($nom)
                ->setCategorie(Categorie::from($categorie))
                ->setEquipement(Equipement::from($equipement))
                ->setUnite(Unite::from($unite))
                ->setVolumeBase($volume)
                ->setChargeRx($charge)
                ->setTechnique($technique)
                ->setDescription($description);
            $parSlug[$slug] = $mouvement;
        }

        foreach ($donnees['wods'] as [$titre, $type, $difficulte, $duree, $tours, $consignes, $lignes]) {
            if (null !== $this->wods->findOneBy(['titre' => $titre, 'officiel' => true])) {
                continue;
            }
            $wod = (new Wod())
                ->setTitre($titre)
                ->setType(WodType::from($type))
                ->setDifficulte(Difficulte::from($difficulte))
                ->setDuree($duree)
                ->setTours($tours)
                ->setConsignes($consignes)
                ->setOfficiel(true);
            foreach ($lignes as [$slug, $quantite]) {
                $wod->addLigne((new WodMouvement())->setMouvement($parSlug[$slug])->setQuantite($quantite));
            }
            $this->em->persist($wod);
            ++$crees['wods'];
        }

        $this->em->flush();

        return $crees;
    }
}
