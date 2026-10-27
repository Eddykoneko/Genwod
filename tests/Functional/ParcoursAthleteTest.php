<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\SeanceRepository;
use App\Repository\UserRepository;
use App\Repository\WodRepository;

final class ParcoursAthleteTest extends AppWebTestCase
{
    public function testInscriptionPuisConnexionAutomatique(): void
    {
        $crawler = $this->client->request('GET', '/inscription');
        $this->client->submit($crawler->selectButton('Créer mon compte')->form([
            'registration_form[email]' => 'nouvelle@example.test',
            'registration_form[pseudo]' => 'NouvelleRecrue',
            'registration_form[plainPassword][first]' => 'Burpees-Thrusters-2026',
            'registration_form[plainPassword][second]' => 'Burpees-Thrusters-2026',
            'registration_form[agreeTerms]' => true,
        ]));

        self::assertResponseRedirects();
        self::assertEmailCount(1);
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'nouvelle@example.test']);
        self::assertInstanceOf(User::class, $user);
        self::assertFalse($user->isVerified());

        $this->client->followRedirect();
        $this->client->request('GET', '/mon-espace');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'NouvelleRecrue');
    }

    public function testUnMotDePasseFaibleEstRefuse(): void
    {
        $crawler = $this->client->request('GET', '/inscription');
        $this->client->submit($crawler->selectButton('Créer mon compte')->form([
            'registration_form[email]' => 'faible@example.test',
            'registration_form[pseudo]' => 'Faible',
            'registration_form[plainPassword][first]' => 'azerty',
            'registration_form[plainPassword][second]' => 'azerty',
            'registration_form[agreeTerms]' => true,
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.form-errors');
    }

    public function testEnregistrerUneSeanceEtBattreSonRecord(): void
    {
        $athlete = $this->creerAthlete();
        $this->client->loginUser($athlete);
        $helen = $this->wod('Helen');
        $url = '/wods/'.$helen->getSlug();

        foreach (['11:30', '10:15'] as $temps) {
            $crawler = $this->client->request('GET', $url);
            $this->client->submit($crawler->selectButton('Enregistrer ma séance')->form([
                'seance[tempsSecondes]' => $temps,
                'seance[mode]' => 'rx',
            ]));
            self::assertResponseRedirects();
            $this->client->followRedirect();
        }

        self::assertSelectorTextContains('.flashes', 'Nouveau record personnel : 10:15');
        $seances = static::getContainer()->get(SeanceRepository::class)->findBy(['athlete' => $athlete], ['id' => 'ASC']);
        self::assertCount(2, $seances);
        self::assertSame([20, 25], array_map(static fn ($s) => $s->getPoints(), $seances));
        self::assertSelectorTextContains('#classement', 'Testeur');
    }

    public function testNoterUneChargeSurGwen(): void
    {
        $this->client->loginUser($athlete = $this->creerAthlete());
        $url = '/wods/'.$this->wod('Gwen')->getSlug();

        foreach (['70', '72.5'] as $charge) {
            $crawler = $this->client->request('GET', $url);
            $this->client->submit($crawler->selectButton('Enregistrer ma séance')->form([
                'seance[chargeKg]' => $charge,
                'seance[mode]' => 'rx',
            ]));
            self::assertResponseRedirects();
            $this->client->followRedirect();
        }

        self::assertSelectorTextContains('.flashes', 'Nouveau record personnel : 72,5 kg');
        $seances = static::getContainer()->get(SeanceRepository::class)->findBy(['athlete' => $athlete], ['id' => 'ASC']);
        self::assertSame(['70 kg', '72,5 kg'], array_map(static fn ($s) => $s->getScoreLisible(), $seances));
    }

    public function testFightGoneBadEnIntervalles(): void
    {
        $this->client->loginUser($this->creerAthlete());
        $this->client->request('GET', '/wods/'.$this->wod('Fight Gone Bad')->getSlug());

        self::assertSelectorExists('[data-chrono-blocs-value="3"][data-chrono-travail-value="60"][data-chrono-repos-value="60"][data-chrono-par-station-value="true"]');
        self::assertSelectorExists('[data-chrono-cible="reps"]');
    }

    public function testUnTempsMalFormeEstSignale(): void
    {
        $this->client->loginUser($this->creerAthlete());
        $crawler = $this->client->request('GET', '/wods/'.$this->wod('Helen')->getSlug());
        $this->client->submit($crawler->selectButton('Enregistrer ma séance')->form(['seance[tempsSecondes]' => 'vite']));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.form-errors', 'mm:ss');
    }

    public function testLikerPuisRetirerSonLike(): void
    {
        $this->client->loginUser($this->creerAthlete());
        $url = '/wods/'.$this->wod('Cindy')->getSlug();

        $crawler = $this->client->request('GET', $url);
        $this->client->submit($crawler->filter('#interactions-'.$this->wod('Cindy')->getId().' form')->first()->form());
        self::assertSame(1, $this->wod('Cindy')->getLikesCount());

        $crawler = $this->client->request('GET', $url);
        $this->client->submit($crawler->filter('#interactions-'.$this->wod('Cindy')->getId().' form')->first()->form());
        $this->em()->clear();
        self::assertSame(0, $this->wod('Cindy')->getLikesCount());
    }

    public function testEnregistrerUnDeathByGenereEtNoterSonScore(): void
    {
        $this->client->loginUser($athlete = $this->creerAthlete());
        $this->client->request('GET', '/generateur?type=death_by&duree=12&difficulte=moyen&graine=0000abcd');
        $this->client->submitForm('Enregistrer et lancer le chrono', ['titre' => 'Mon Death By']);

        self::assertResponseRedirects();
        $crawler = $this->client->followRedirect();
        self::assertSelectorTextContains('h1', 'Mon Death By');
        self::assertSelectorExists('[data-chrono-type-value="death_by"][data-chrono-pas-value]');
        self::assertCount(1, static::getContainer()->get(WodRepository::class)->findBy(['auteur' => $athlete]));

        $this->client->submit($crawler->selectButton('Enregistrer ma séance')->form([
            'seance[tours]' => 11,
            'seance[reps]' => 4,
            'seance[mode]' => 'rx',
        ]));
        self::assertResponseRedirects();
        $seance = static::getContainer()->get(SeanceRepository::class)->findOneBy(['athlete' => $athlete]);
        self::assertSame('11 min + 4 reps', $seance?->getScoreLisible());
    }

    public function testCommenter(): void
    {
        $this->client->loginUser($this->creerAthlete());
        $crawler = $this->client->request('GET', '/wods/'.$this->wod('Karen')->getSlug());
        $this->client->submit($crawler->selectButton('Publier')->form(['commentaire[contenu]' => '150 wall balls, mes cuisses pleurent.']));
        $this->client->followRedirect();

        self::assertSelectorTextContains('#commentaires-liste', 'mes cuisses pleurent');
    }

    public function testExportRgpdEtSuppressionDuCompte(): void
    {
        $athlete = $this->creerAthlete('AuRevoir');
        $hasher = static::getContainer()->get('security.user_password_hasher');
        $athlete->setPassword($hasher->hashPassword($athlete, 'Un-Mot-De-Passe-Solide'));
        $this->em()->flush();
        $this->client->loginUser($athlete);

        $this->client->request('GET', '/mon-espace/export');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertStringContainsString('AuRevoir', (string) $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/mon-espace/profil');
        $this->client->submit($crawler->selectButton('Supprimer définitivement')->form(['password' => 'Un-Mot-De-Passe-Solide']));

        self::assertResponseRedirects('/');
        self::assertNull(static::getContainer()->get(UserRepository::class)->findOneBy(['pseudo' => 'AuRevoir']));
    }
}
