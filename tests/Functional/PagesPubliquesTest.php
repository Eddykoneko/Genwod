<?php

namespace App\Tests\Functional;

use App\Enum\Benchmark;
use App\Repository\WodRepository;
use PHPUnit\Framework\Attributes\DataProvider;

final class PagesPubliquesTest extends AppWebTestCase
{
    /** @return iterable<array{string}> */
    public static function urls(): iterable
    {
        foreach (['/', '/generateur', '/wods', '/classement', '/classement?periode=semaine', '/a-propos', '/connexion', '/inscription', '/mot-de-passe-oublie', '/mentions-legales', '/confidentialite', '/sitemap.xml'] as $url) {
            yield $url => [$url];
        }
    }

    #[DataProvider('urls')]
    public function testLaPageRepond(string $url): void
    {
        $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    public function testLAccueilAfficheLeWodDuJour(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertSelectorTextContains('.hero', 'WOD du jour');
        self::assertCount(1, $crawler->filter('.board'));
    }

    public function testLaFicheDUnWod(): void
    {
        $this->client->request('GET', '/wods/'.$this->wod('Cindy')->getSlug());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Cindy');
        self::assertSelectorTextContains('.board-format', 'AMRAP 20 min');
        self::assertSelectorCount(3, '.board-lines li');
    }

    /** La sélection officielle ne contient que des WODs de référence de la communauté CrossFit. */
    public function testLaSelectionOfficielleNeContientQueDesBenchmarks(): void
    {
        $officiels = static::getContainer()->get(WodRepository::class)->findBy(['officiel' => true]);

        self::assertCount(70, $officiels);
        foreach ($officiels as $wod) {
            self::assertNotNull($wod->getBenchmark(), (string) $wod->getTitre());
            self::assertNotEmpty($wod->getLignes(), (string) $wod->getTitre());
        }
        self::assertSame('21-15-9 For Time · cap 10 min', $this->wod('Fran')->getFormat());
        self::assertSame('10 tours For Time · cap 15 min', $this->wod('Open 20.1')->getFormat());
        $burpees = $this->wod('Open 12.1')->getLignes()->first();
        self::assertNotFalse($burpees);
        self::assertSame('Max Burpees (cible à +15 cm)', $burpees->getLibelle());

        self::assertSame('10-9-8-7-6-5-4-3-2-1 For Time · cap 40 min', $this->wod('Linda')->getFormat());
        self::assertSame('For Time · cap 120 min', $this->wod('CHAD1000x')->getFormat());
        self::assertSame('7 tours For Time · cap 50 min', $this->wod('The Seven')->getFormat());
        // Filthy Fifty : 10 mouvements × 50 répétitions.
        $filthy = $this->wod('Filthy Fifty');
        self::assertSame(500, array_sum(array_map(static fn ($l) => $l->getQuantite(), $filthy->getLignes()->toArray())));
        self::assertSame(Benchmark::Classique, $filthy->getBenchmark());
        self::assertSame('Max reps · 3 tours · 5 × 1 min · repos 1 min', $this->wod('Fight Gone Bad')->getFormat());
        self::assertSame('AMRAP · 5 × 3 min · repos 1 min', $this->wod('The Chief')->getFormat());
        self::assertSame('15-12-9 For Load', $this->wod('Gwen')->getFormat());
    }

    /**
     * Les échelles de l'Open sont saisies ligne par ligne : on vérifie le total officiel de répétitions.
     *
     * @return iterable<string, array{string, int}>
     */
    public static function totauxOfficiels(): iterable
    {
        yield '17.1' => ['Open 17.1', 225];
        yield '18.2' => ['Open 18.2', 110];
        yield '21.1' => ['Open 21.1', 605];
        yield '26.1' => ['Open 26.1', 354];
    }

    #[DataProvider('totauxOfficiels')]
    public function testLeTotalDeRepetitionsDesEchellesEstExact(string $titre, int $total): void
    {
        $wod = $this->wod($titre);
        $schema = $wod->getSchemaReps();
        $somme = 0;
        foreach ($wod->getLignes() as $ligne) {
            $somme += null !== $schema ? array_sum(array_map('intval', explode('-', $schema))) : $ligne->getQuantite();
        }

        self::assertSame($total, $somme);
    }

    public function testFiltrerLesHeroWods(): void
    {
        $this->client->request('GET', '/wods?benchmark=hero');

        self::assertSelectorTextContains('.results', 'The Seven');
        self::assertSelectorTextContains('.results .badge-origine', 'Hero');
        self::assertSelectorTextNotContains('.results', 'Cindy');
    }

    public function testUnWodInexistantDonneUne404(): void
    {
        $this->client->request('GET', '/wods/n-existe-pas');

        self::assertResponseStatusCodeSame(404);
    }

    public function testLeGenerateurRedirigeVersUneUrlAvecGraine(): void
    {
        $this->client->request('GET', '/generateur?type=emom&duree=12&difficulte=facile');

        self::assertResponseRedirects();
        self::assertMatchesRegularExpression('/graine=[a-f0-9]{8}/', (string) $this->client->getResponse()->headers->get('Location'));

        $this->client->followRedirect();
        self::assertSelectorTextContains('.board-format', 'EMOM 12 min');
    }

    public function testLesEnTetesDeSecurite(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseHeaderSame('X-Content-Type-Options', 'nosniff');
        self::assertResponseHeaderSame('X-Frame-Options', 'SAMEORIGIN');
    }
}
