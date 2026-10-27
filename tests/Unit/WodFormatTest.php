<?php

namespace App\Tests\Unit;

use App\Entity\Mouvement;
use App\Entity\Seance;
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

    public function testFormatsParIntervalles(): void
    {
        $fightGoneBad = (new Wod())->setType(WodType::MaxReps)->setBlocs(3)->setTravailSecondes(60)->setReposSecondes(60)->setParStation(true);
        foreach (['Wall balls', 'SDHP', 'Box jumps', 'Push press', 'Rameur'] as $nom) {
            $fightGoneBad->addLigne((new WodMouvement())->setMouvement((new Mouvement())->setNom($nom))->setQuantite(0));
        }
        $theChief = (new Wod())->setType(WodType::Amrap)->setBlocs(5)->setTravailSecondes(180)->setReposSecondes(60);

        self::assertSame('Max reps · 3 tours · 5 × 1 min · repos 1 min', $fightGoneBad->getFormat());
        self::assertSame('AMRAP · 5 × 3 min · repos 1 min', $theChief->getFormat());
        self::assertSame('15-12-9 For Load', (new Wod())->setType(WodType::ForLoad)->setSchemaReps('15-12-9')->getFormat());
        self::assertSame('5 tours Max reps', (new Wod())->setType(WodType::MaxReps)->setTours(5)->getFormat());
    }

    public function testScoreEnCharge(): void
    {
        $seance = (new Seance())->setWod((new Wod())->setType(WodType::ForLoad))->setChargeKg(102.5);

        self::assertSame('102,5 kg', $seance->getScoreLisible());
        self::assertSame(1025, $seance->getValeurScore());
        self::assertSame('80 kg', $seance->setChargeKg(80.0)->getScoreLisible());
    }
}
