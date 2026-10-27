<?php

namespace App\Tests\Unit;

use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\WodType;
use App\Generator\CriteresGeneration;
use PHPUnit\Framework\TestCase;

final class CriteresGenerationTest extends TestCase
{
    public function testAllerRetourParLaQueryString(): void
    {
        $criteres = new CriteresGeneration(WodType::Emom, 16, Difficulte::Difficile, [Equipement::Barre, Equipement::Rameur], '0badcafe');

        self::assertEquals($criteres, CriteresGeneration::depuisQuery($criteres->versQuery()));
    }

    public function testLesValeursInvalidesSontNeutralisees(): void
    {
        $criteres = CriteresGeneration::depuisQuery([
            'type' => 'n-importe-quoi',
            'duree' => '9999',
            'difficulte' => '<script>',
            'materiel' => ['barre', 'fusee', ['tableau']],
            'graine' => '../../etc/passwd',
        ]);

        self::assertSame(WodType::Amrap, $criteres->type);
        self::assertSame(40, $criteres->duree);
        self::assertSame(Difficulte::Moyen, $criteres->difficulte);
        self::assertSame([Equipement::Barre], $criteres->equipements);
        self::assertMatchesRegularExpression('/^[a-f0-9]{8}$/', (string) $criteres->graine);
    }

    public function testLesFormatsReservesAuxBenchmarksNeSontPasGeneres(): void
    {
        self::assertSame(WodType::Amrap, CriteresGeneration::depuisQuery(['type' => 'for_load'])->type);
        self::assertSame(WodType::Amrap, CriteresGeneration::depuisQuery(['type' => 'max_reps'])->type);
        self::assertSame(WodType::DeathBy, CriteresGeneration::depuisQuery(['type' => 'death_by'])->type);
    }
}
