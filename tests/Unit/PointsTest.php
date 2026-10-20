<?php

namespace App\Tests\Unit;

use App\Entity\Seance;
use App\Entity\Wod;
use App\Enum\Difficulte;
use App\Enum\Mode;
use App\Service\Points;
use PHPUnit\Framework\TestCase;

final class PointsTest extends TestCase
{
    public function testBareme(): void
    {
        $points = new Points();
        $wod = (new Wod())->setDifficulte(Difficulte::Difficile);

        self::assertSame(10 + 10 + 5 + 5, $points->calculer((new Seance())->setWod($wod)->setMode(Mode::Rx), true));
        self::assertSame(10 + 10, $points->calculer((new Seance())->setWod($wod)->setMode(Mode::Scaled), false));
        self::assertSame(10, $points->calculer((new Seance())->setWod($wod->setDifficulte(Difficulte::Facile))->setMode(Mode::Scaled), false));
    }
}
