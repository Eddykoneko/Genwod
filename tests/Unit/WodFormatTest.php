<?php

namespace App\Tests\Unit;

use App\Entity\Mouvement;
use App\Entity\Wod;
use App\Entity\WodMouvement;
use App\Enum\WodType;
use PHPUnit\Framework\TestCase;

final class WodFormatTest extends TestCase
{
    public function testSchemaDeRepetitions(): void
    {
        $fran = (new Wod())->setTitre('Fran')->setType(WodType::ForTime)->setDuree(10)->setSchemaReps('21-15-9');
        $fran->addLigne($ligne = (new WodMouvement())->setMouvement((new Mouvement())->setNom('Thrusters'))->setQuantite(21)->setNote('43/30 kg'));

        self::assertSame('21-15-9 For Time · cap 10 min', $fran->getFormat());
        self::assertSame('21-15-9 Thrusters (43/30 kg)', $ligne->getLibelle());
    }

    public function testEmomToutChaqueMinute(): void
    {
        $chelsea = (new Wod())->setType(WodType::Emom)->setDuree(30)->setToutChaqueMinute(true);

        self::assertSame('EMOM 30 min · tout chaque minute', $chelsea->getFormat());
    }

    public function testQuantiteMax(): void
    {
        $wod = (new Wod())->setType(WodType::Amrap)->setDuree(7);
        $wod->addLigne($ligne = (new WodMouvement())->setMouvement((new Mouvement())->setNom('Burpees'))->setQuantite(0));

        self::assertSame('Max Burpees', $ligne->getLibelle());
    }

    public function testDeathByAfficheLaProgression(): void
    {
        $deathBy = (new Wod())->setType(WodType::DeathBy)->setDuree(20);
        $deathBy->addLigne($ligne = (new WodMouvement())->setMouvement((new Mouvement())->setNom('Thrusters')->setChargeRx('43/30 kg'))->setQuantite(2));

        self::assertSame('Death By · cap 20 min', $deathBy->getFormat());
        self::assertSame('2-4-6… Thrusters (43/30 kg)', $ligne->getLibelle());
        self::assertSame('Thrusters (43/30 kg)', $ligne->getMouvementLibelle());
    }
}
