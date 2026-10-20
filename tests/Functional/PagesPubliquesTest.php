<?php

namespace App\Tests\Functional;

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
