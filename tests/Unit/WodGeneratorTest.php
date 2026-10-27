<?php

namespace App\Tests\Unit;

use App\Entity\WodMouvement;
use App\Enum\Categorie;
use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\Unite;
use App\Enum\WodType;
use App\Generator\CriteresGeneration;
use App\Generator\WodGenerator;
use App\Repository\MouvementRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WodGeneratorTest extends TestCase
{
    private WodGenerator $generateur;

    protected function setUp(): void
    {
        $catalogue = CatalogueEnMemoire::mouvements();
        $depot = $this->createStub(MouvementRepository::class);
        $depot->method('findBy')->willReturn($catalogue);
        $this->generateur = new WodGenerator($depot);
    }

    public function testLaMemeGraineDonneLeMemeWod(): void
    {
        $criteres = new CriteresGeneration(WodType::Amrap, 15, Difficulte::Moyen, [Equipement::Kettlebell], 'abcdef12');

        $a = $this->generateur->generer($criteres);
        $b = $this->generateur->generer($criteres);

        self::assertSame($a->getTitre(), $b->getTitre());
        self::assertSame($this->libelles($a->getLignes()->toArray()), $this->libelles($b->getLignes()->toArray()));
    }

    public function testDesGrainesDifferentesVarientLesWods(): void
    {
        $wods = [];
        foreach (['00000001', '00000002', '00000003', '00000004', '00000005'] as $graine) {
            $wod = $this->generateur->generer(new CriteresGeneration(WodType::ForTime, 15, Difficulte::Moyen, [], $graine));
            $wods[] = implode('|', $this->libelles($wod->getLignes()->toArray()));
        }

        self::assertGreaterThan(1, \count(array_unique($wods)));
    }

    /** Le générateur ne doit jamais proposer de matériel que l'athlète n'a pas. */
    public function testRespecteLeMaterielDisponible(): void
    {
        for ($i = 0; $i < 30; ++$i) {
            $criteres = new CriteresGeneration(WodType::cases()[$i % 4], 20, Difficulte::Difficile, [Equipement::Kettlebell], \sprintf('%08x', $i));
            foreach ($this->generateur->generer($criteres)->getLignes() as $ligne) {
                self::assertContains($ligne->getMouvement()?->getEquipement(), [Equipement::Aucun, Equipement::Kettlebell]);
            }
        }
    }

    /** @return iterable<string, array{WodType, int, int}> */
    public static function structures(): iterable
    {
        yield 'AMRAP court' => [WodType::Amrap, 10, 3];
        yield 'AMRAP long' => [WodType::Amrap, 20, 4];
        yield 'EMOM 12 → 4 mouvements' => [WodType::Emom, 12, 4];
        yield 'EMOM 9 → 3 mouvements' => [WodType::Emom, 9, 3];
        yield 'EMOM 10 → 2 mouvements' => [WodType::Emom, 10, 2];
        yield 'Death By → 1 mouvement' => [WodType::DeathBy, 16, 1];
    }

    #[DataProvider('structures')]
    public function testNombreDeMouvementsSelonLeFormat(WodType $type, int $duree, int $attendu): void
    {
        $wod = $this->generateur->generer(new CriteresGeneration($type, $duree, Difficulte::Moyen, Equipement::selectionnables(), 'cafe0001'));

        self::assertCount($attendu, $wod->getLignes());
    }

    public function testUnSeulCardioEtUnSeulGainageParWod(): void
    {
        for ($i = 0; $i < 40; ++$i) {
            $wod = $this->generateur->generer(new CriteresGeneration(WodType::ForTime, 30, Difficulte::Moyen, Equipement::selectionnables(), \sprintf('%08x', $i * 7919)));
            $lignes = $wod->getLignes()->toArray();

            self::assertLessThanOrEqual(1, \count(array_filter($lignes, static fn (WodMouvement $l) => Categorie::Cardio === $l->getMouvement()?->getCategorie())));
            self::assertLessThanOrEqual(1, \count(array_filter($lignes, static fn (WodMouvement $l) => Unite::Secondes === $l->getUnite())));
        }
    }

    public function testDeathByEnRepetitionsAvecUnPasParMinute(): void
    {
        for ($i = 0; $i < 20; ++$i) {
            $wod = $this->generateur->generer(new CriteresGeneration(WodType::DeathBy, 6, Difficulte::cases()[$i % 3], Equipement::selectionnables(), \sprintf('%08x', $i)));
            // Le time cap laisse toujours le temps de monter en charge.
            self::assertSame(10, $wod->getDuree());
            $ligne = $wod->getLignes()->first();
            self::assertNotFalse($ligne);
            self::assertSame(Unite::Reps, $ligne->getUnite());
            self::assertNotSame(Categorie::Cardio, $ligne->getMouvement()?->getCategorie());
            self::assertGreaterThanOrEqual(1, $ligne->getQuantite());
            self::assertLessThanOrEqual(5, $ligne->getQuantite());
        }
    }

    public function testLesVolumesSontArrondisADesValeursNaturelles(): void
    {
        $wod = $this->generateur->generer(new CriteresGeneration(WodType::Amrap, 20, Difficulte::Difficile, Equipement::selectionnables(), '12345678'));

        foreach ($wod->getLignes() as $ligne) {
            $naturelles = match ($ligne->getUnite()) {
                Unite::Metres => [100, 150, 200, 250, 300, 400, 500, 600, 800, 1000, 1200, 1500, 2000],
                Unite::Secondes => range(30, 180, 15),
                default => [3, 5, 6, 7, 8, 9, 10, 12, 14, 15, 16, 18, 20, 21, 24, 25, 30, 35, 40, 50, 60, 75, 80, 100, 150],
            };
            self::assertContains($ligne->getQuantite(), $naturelles);
        }
    }

    public function testLeFacileExclutLesMouvementsTechniques(): void
    {
        for ($i = 0; $i < 20; ++$i) {
            $wod = $this->generateur->generer(new CriteresGeneration(WodType::Amrap, 15, Difficulte::Facile, Equipement::selectionnables(), \sprintf('%08x', $i)));
            foreach ($wod->getLignes() as $ligne) {
                self::assertFalse($ligne->getMouvement()?->isTechnique(), $ligne->getLibelle());
            }
        }
    }

    /**
     * @param array<int, WodMouvement> $lignes
     *
     * @return list<string>
     */
    private function libelles(array $lignes): array
    {
        return array_values(array_map(static fn (WodMouvement $l) => $l->getLibelle(), $lignes));
    }
}
