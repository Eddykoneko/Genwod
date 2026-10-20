<?php

namespace App\Tests\Unit;

use App\Service\Statistiques;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatistiquesTest extends TestCase
{
    /** @return iterable<string, array{list<string>, array{int, int}}> */
    public static function cas(): iterable
    {
        yield 'aucune séance' => [[], [0, 0]];
        yield 'séance aujourd\'hui' => [['2026-10-20'], [1, 1]];
        yield 'série en cours depuis hier' => [['2026-10-19', '2026-10-18', '2026-10-17'], [3, 3]];
        yield 'série cassée' => [['2026-10-15', '2026-10-14'], [0, 2]];
        yield 'meilleure série dans le passé' => [['2026-10-20', '2026-10-10', '2026-10-09', '2026-10-08', '2026-10-07'], [1, 4]];
    }

    /**
     * @param list<string>    $dates
     * @param array{int, int} $attendu
     */
    #[DataProvider('cas')]
    public function testSeries(array $dates, array $attendu): void
    {
        self::assertSame($attendu, Statistiques::series($dates, new \DateTimeImmutable('2026-10-20')));
    }
}
