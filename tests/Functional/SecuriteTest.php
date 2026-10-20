<?php

namespace App\Tests\Functional;

use App\Repository\WodRepository;

final class SecuriteTest extends AppWebTestCase
{
    public function testLEspacePersoExigeUneConnexion(): void
    {
        $this->client->request('GET', '/mon-espace');

        self::assertResponseRedirects('/connexion');
    }

    public function testLAdministrationEstReserveeAuxAdmins(): void
    {
        $this->client->loginUser($this->creerAthlete('Simple'));
        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testUnAdminAccedeAuBackOffice(): void
    {
        $this->client->loginUser($this->creerAthlete('Chef', admin: true));
        $this->client->request('GET', '/admin');
        self::assertResponseIsSuccessful();

        foreach (['/admin/wod', '/admin/mouvement', '/admin/user', '/admin/seance', '/admin/commentaire'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseIsSuccessful($url);
        }
    }

    /** Un jeton CSRF invalide lève une exception d'authentification : retour à la connexion. */
    public function testUnLikeSansJetonCsrfEstRefuse(): void
    {
        $this->client->loginUser($this->creerAthlete());
        $this->client->request('POST', '/wods/'.$this->wod('Helen')->getSlug().'/like');

        self::assertResponseRedirects('/connexion');
        $this->em()->clear();
        self::assertSame(0, $this->wod('Helen')->getLikesCount());
    }

    public function testOnNePeutPasSupprimerLeWodDUnAutre(): void
    {
        $this->client->loginUser($this->creerAthlete());
        $crawler = $this->client->request('GET', '/wods/'.$this->wod('Helen')->getSlug());
        self::assertCount(0, $crawler->filter('form[action$="/supprimer"]'), 'Le bouton de suppression ne doit pas être proposé.');

        $this->client->request('POST', '/wods/'.$this->wod('Helen')->getSlug().'/supprimer', ['_token' => 'forge']);

        self::assertFalse($this->client->getResponse()->isSuccessful());
        $this->em()->clear();
        $this->wod('Helen');
    }

    public function testLAuteurPeutSupprimerSonWod(): void
    {
        $this->client->loginUser($auteur = $this->creerAthlete('Auteur'));
        $this->client->request('GET', '/generateur?type=amrap&duree=10&difficulte=facile&graine=00c0ffee');
        $this->client->submitForm('Enregistrer et lancer le chrono', ['titre' => 'A supprimer']);
        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->filter('form[action$="/supprimer"]')->form());

        self::assertResponseRedirects('/mon-espace/mes-wods');
        $this->em()->clear();
        self::assertNull(static::getContainer()->get(WodRepository::class)->findOneBy(['titre' => 'A supprimer']));
    }
}
