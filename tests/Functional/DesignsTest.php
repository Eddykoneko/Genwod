<?php

namespace App\Tests\Functional;

use App\EventListener\DesignListener;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Les propositions de refonte graphique ont leurs propres gabarits : chacun doit se rendre sans erreur.
 */
final class DesignsTest extends AppWebTestCase
{
    /** @return iterable<string, array{string}> */
    public static function designs(): iterable
    {
        foreach (['', ...array_keys(DesignListener::DESIGNS)] as $design) {
            yield '' === $design ? 'actuel' : $design => [$design];
        }
    }

    #[DataProvider('designs')]
    public function testLesPagesSeRendent(string $design): void
    {
        $this->client->loginUser($this->creerAthlete());
        $urls = ['/', '/generateur', '/generateur?type=emom&duree=12&difficulte=moyen&graine=0000abcd', '/wods', '/wods?benchmark=hero',
            '/wods/'.$this->wod('Fran')->getSlug(), '/wods/'.$this->wod('Chelsea')->getSlug(), '/classement', '/mon-espace', '/a-propos'];

        $this->client->request('GET', '/?design='.$design);
        self::assertResponseIsSuccessful();
        self::assertSame($design, $this->client->getCookieJar()->get(DesignListener::COOKIE)?->getValue() ?? '');

        foreach ($urls as $url) {
            $crawler = $this->client->request('GET', $url);
            self::assertResponseIsSuccessful($url);
            if ('' !== $design) {
                self::assertSame($design, $crawler->filter('html')->attr('data-design'), $url);
            }
        }
    }

    public function testUnDesignInconnuEstIgnore(): void
    {
        $crawler = $this->client->request('GET', '/?design=pirate');

        self::assertResponseIsSuccessful();
        self::assertNull($crawler->filter('html')->attr('data-design'));
    }
}
