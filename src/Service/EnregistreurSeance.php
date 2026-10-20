<?php

namespace App\Service;

use App\Entity\Seance;
use App\Entity\User;
use App\Entity\Wod;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Enregistre un résultat : détection du record personnel et attribution des points.
 */
final class EnregistreurSeance
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SeanceRepository $seances,
        private readonly Points $points,
    ) {
    }

    /** @return bool true si la séance est un nouveau record personnel */
    public function enregistrer(Seance $seance, User $athlete, Wod $wod): bool
    {
        $seance->setAthlete($athlete)->setWod($wod);

        $precedente = $this->seances->meilleurePerformance($athlete, $wod);
        // Un record n'a de sens que si le WOD a déjà été réalisé.
        $record = null !== $precedente && $this->seances->estMeilleure($seance, $precedente);

        $seance->setPoints($this->points->calculer($seance, $record));
        $this->em->persist($seance);
        $this->em->flush();

        return $record;
    }
}
