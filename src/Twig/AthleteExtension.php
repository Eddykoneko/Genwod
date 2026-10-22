<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\SeanceRepository;
use App\Service\Statistiques;
use Twig\Attribute\AsTwigFunction;

/**
 * Données de progression accessibles depuis n'importe quel gabarit (série dans l'en-tête, badges).
 */
final class AthleteExtension
{
    /** @var array<int, array{total: array{seances: int, points: int, rx: int}, mois: array{seances: int, points: int, rx: int}, serie: int, meilleureSerie: int}> */
    private array $resumes = [];

    public function __construct(
        private readonly Statistiques $statistiques,
        private readonly SeanceRepository $seances,
    ) {
    }

    /** @return array{total: array{seances: int, points: int, rx: int}, mois: array{seances: int, points: int, rx: int}, serie: int, meilleureSerie: int} */
    #[AsTwigFunction('resume_athlete')]
    public function resume(User $athlete): array
    {
        return $this->resumes[(int) $athlete->getId()] ??= $this->statistiques->resume($athlete);
    }

    /**
     * Badges simples à débloquer, avec la progression vers le suivant.
     *
     * @return list<array{code: string, titre: string, obtenu: bool, valeur: int, objectif: int}>
     */
    #[AsTwigFunction('badges_athlete')]
    public function badges(User $athlete): array
    {
        $resume = $this->resume($athlete);
        $records = \count($this->seances->recordsPersonnels($athlete));

        $badges = [];
        foreach ([[1, 'Première séance'], [10, '10 séances'], [50, '50 séances'], [100, '100 séances']] as [$objectif, $titre]) {
            $badges[] = ['code' => (string) $objectif, 'titre' => $titre, 'obtenu' => $resume['total']['seances'] >= $objectif, 'valeur' => $resume['total']['seances'], 'objectif' => $objectif];
        }
        foreach ([[3, 'Série de 3 jours'], [7, 'Série de 7 jours'], [14, 'Série de 14 jours']] as [$objectif, $titre]) {
            $badges[] = ['code' => $objectif.'j', 'titre' => $titre, 'obtenu' => $resume['meilleureSerie'] >= $objectif, 'valeur' => $resume['meilleureSerie'], 'objectif' => $objectif];
        }
        foreach ([[5, '5 WODs différents'], [20, '20 WODs différents']] as [$objectif, $titre]) {
            $badges[] = ['code' => 'W'.$objectif, 'titre' => $titre, 'obtenu' => $records >= $objectif, 'valeur' => $records, 'objectif' => $objectif];
        }

        return $badges;
    }
}
