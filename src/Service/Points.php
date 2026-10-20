<?php

namespace App\Service;

use App\Entity\Seance;
use App\Enum\Mode;

/**
 * Barème des points du classement global :
 * 10 pts par séance + bonus de difficulté (0/5/10) + 5 pts en RX + 5 pts si record personnel.
 */
final class Points
{
    public const BASE = 10;
    public const BONUS_RX = 5;
    public const BONUS_RECORD = 5;

    public function calculer(Seance $seance, bool $recordPersonnel): int
    {
        $points = self::BASE + ($seance->getWod()?->getDifficulte()->bonusPoints() ?? 0);
        if (Mode::Rx === $seance->getMode()) {
            $points += self::BONUS_RX;
        }
        if ($recordPersonnel) {
            $points += self::BONUS_RECORD;
        }

        return $points;
    }
}
