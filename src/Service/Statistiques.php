<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\SeanceRepository;
use Psr\Clock\ClockInterface;

/**
 * Statistiques du tableau de bord : totaux, série de jours consécutifs, heatmap.
 */
final class Statistiques
{
    public const SEMAINES_HEATMAP = 18;

    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly ClockInterface $horloge,
    ) {
    }

    /**
     * @return array{total: array{seances: int, points: int, rx: int}, mois: array{seances: int, points: int, rx: int}, serie: int, meilleureSerie: int}
     */
    public function resume(User $athlete): array
    {
        $aujourdhui = $this->aujourdhui();
        [$serie, $meilleure] = self::series($this->seances->datesEntrainement($athlete), $aujourdhui);

        return [
            'total' => $this->seances->totaux($athlete),
            'mois' => $this->seances->totaux($athlete, $aujourdhui->modify('first day of this month')),
            'serie' => $serie,
            'meilleureSerie' => $meilleure,
        ];
    }

    /**
     * Calcule la série en cours et la meilleure série de jours consécutifs.
     * La série en cours reste valable si la dernière séance date d'hier.
     *
     * @param list<string> $dates dates Y-m-d distinctes, triées de la plus récente à la plus ancienne
     *
     * @return array{0: int, 1: int}
     */
    public static function series(array $dates, \DateTimeImmutable $aujourdhui): array
    {
        if ([] === $dates) {
            return [0, 0];
        }

        $meilleure = 1;
        $courante = 1;
        for ($i = 1, $n = \count($dates); $i < $n; ++$i) {
            $ecart = (new \DateTimeImmutable($dates[$i]))->diff(new \DateTimeImmutable($dates[$i - 1]))->days;
            $courante = 1 === $ecart ? $courante + 1 : 1;
            $meilleure = max($meilleure, $courante);
        }

        // Série actuelle : on remonte depuis la séance la plus récente.
        $derniere = new \DateTimeImmutable($dates[0]);
        $ecartAujourdhui = $derniere->diff($aujourdhui)->days;
        if ($ecartAujourdhui > 1) {
            return [0, $meilleure];
        }

        $serie = 1;
        for ($i = 1, $n = \count($dates); $i < $n; ++$i) {
            if (1 !== (new \DateTimeImmutable($dates[$i]))->diff(new \DateTimeImmutable($dates[$i - 1]))->days) {
                break;
            }
            ++$serie;
        }

        return [$serie, $meilleure];
    }

    /**
     * Grille de la heatmap : des semaines (lundi → dimanche), chaque jour avec son nombre de séances.
     *
     * @return list<list<array{date: \DateTimeImmutable, nb: int, futur: bool}>>
     */
    public function heatmap(User $athlete): array
    {
        $aujourdhui = $this->aujourdhui();
        $debut = $aujourdhui->modify('monday this week')->modify(\sprintf('-%d weeks', self::SEMAINES_HEATMAP - 1));
        $activite = $this->seances->activiteParJour($athlete, $debut);

        $semaines = [];
        for ($s = 0; $s < self::SEMAINES_HEATMAP; ++$s) {
            $semaine = [];
            for ($j = 0; $j < 7; ++$j) {
                $date = $debut->modify(\sprintf('+%d days', $s * 7 + $j));
                $semaine[] = ['date' => $date, 'nb' => $activite[$date->format('Y-m-d')] ?? 0, 'futur' => $date > $aujourdhui];
            }
            $semaines[] = $semaine;
        }

        return $semaines;
    }

    private function aujourdhui(): \DateTimeImmutable
    {
        return $this->horloge->now()->setTime(0, 0);
    }
}
