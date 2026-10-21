<?php

namespace App\Tests\Unit;

use App\Entity\Mouvement;
use App\Enum\Categorie;
use App\Enum\Equipement;
use App\Enum\Unite;

/**
 * Construit le catalogue réel (config/donnees/catalogue.php) en mémoire, sans base de données.
 */
final class CatalogueEnMemoire
{
    /** @return list<Mouvement> */
    public static function mouvements(): array
    {
        $donnees = require \dirname(__DIR__, 2).'/config/donnees/catalogue.php';
        $mouvements = [];
        foreach ($donnees['mouvements'] as $i => $ligne) {
            [$slug, $nom, $categorie, $equipement, $unite, $volume, $charge, $technique] = $ligne;
            // Comme MouvementRepository::findBy(['actif' => true]) : les mouvements réservés sont exclus.
            if (false === ($ligne[9] ?? true)) {
                continue;
            }
            $mouvement = (new Mouvement())
                ->setSlug($slug)
                ->setNom($nom)
                ->setCategorie(Categorie::from($categorie))
                ->setEquipement(Equipement::from($equipement))
                ->setUnite(Unite::from($unite))
                ->setVolumeBase($volume)
                ->setChargeRx($charge)
                ->setTechnique($technique);
            // L'id sert à l'ordre stable du catalogue.
            (new \ReflectionProperty(Mouvement::class, 'id'))->setValue($mouvement, $i + 1);
            $mouvements[] = $mouvement;
        }

        return $mouvements;
    }
}
