<?php

namespace App\Service;

use App\Entity\Mouvement;
use App\Entity\Wod;
use App\Entity\WodMouvement;
use App\Enum\Benchmark;
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

    /** @return array{mouvements: int, wods: int, retires: int} éléments créés / retirés de la sélection officielle */
    public function importer(): array
    {
        /** @var array{mouvements: list<array<int, mixed>>, wods: list<array<string, mixed>>} $donnees */
        $donnees = require $this->fichier;
        $crees = ['mouvements' => 0, 'wods' => 0, 'retires' => 0];

        $parSlug = [];
        foreach ($donnees['mouvements'] as $ligne) {
            [$slug, $nom, $categorie, $equipement, $unite, $volume, $charge, $technique, $description] = $ligne;
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
                ->setDescription($description)
                ->setActif($ligne[9] ?? true);
            $parSlug[$slug] = $mouvement;
        }

        $titres = [];
        foreach ($donnees['wods'] as $d) {
            $titres[] = $d['titre'];
            $wod = $this->wods->findOneBy(['titre' => $d['titre'], 'officiel' => true]);
            if (null === $wod) {
                $wod = (new Wod())->setTitre($d['titre'])->setOfficiel(true);
                $this->em->persist($wod);
                ++$crees['wods'];
            }

            $wod->setType(WodType::from($d['type']))
                ->setDifficulte(Difficulte::from($d['difficulte']))
                ->setDuree($d['duree'])
                ->setTours($d['tours'] ?? null)
                ->setSchemaReps($d['schema'] ?? null)
                ->setToutChaqueMinute($d['toutChaqueMinute'] ?? false)
                ->setBlocs($d['blocs'] ?? null)
                ->setTravailSecondes($d['travail'] ?? null)
                ->setReposSecondes($d['repos'] ?? null)
                ->setParStation($d['parStation'] ?? false)
                ->setBenchmark(Benchmark::from($d['benchmark']))
                ->setConsignes($d['consignes'] ?? null);

            // Les lignes sont reconstruites à chaque import (la référence fait foi).
            foreach ($wod->getLignes()->toArray() as $ancienne) {
                $wod->removeLigne($ancienne);
            }
            foreach ($d['lignes'] as $l) {
                $wod->addLigne((new WodMouvement())->setMouvement($parSlug[$l[0]])->setQuantite($l[1])->setNote($l[2] ?? null));
            }
        }

        // Un WOD officiel qui n'est plus dans la sélection reste en ligne, mais comme WOD communautaire.
        foreach ($this->wods->findBy(['officiel' => true]) as $wod) {
            if (!\in_array($wod->getTitre(), $titres, true)) {
                $wod->setOfficiel(false)->setBenchmark(null);
                ++$crees['retires'];
            }
        }

        $this->em->flush();

        return $crees;
    }
}
