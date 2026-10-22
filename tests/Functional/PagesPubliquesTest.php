<?php

namespace App\Tests\Functional;

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

        self::assertCount(36, $officiels);
        foreach ($officiels as $wod) {
            self::assertNotNull($wod->getBenchmark(), (string) $wod->getTitre());
            self::assertNotEmpty($wod->getLignes(), (string) $wod->getTitre());
        }
        self::assertSame('21-15-9 For Time · cap 10 min', $this->wod('Fran')->getFormat());
        self::assertSame('10 tours For Time · cap 15 min', $this->wod('Open 20.1')->getFormat());
        self::assertSame('Max Burpees (cible à +15 cm)', $this->wod('Open 12.1')->getLignes()->first()?->getLibelle());
    }

    public function testFiltrerLesHeroWods(): void
    {
        $this->client->request('GET', '/wods?benchmark=hero');

        self::assertSelectorTextContains('.results', 'Murph');
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
