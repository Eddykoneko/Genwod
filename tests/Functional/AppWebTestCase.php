<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Entity\Wod;
use App\Repository\WodRepository;
use App\Service\CatalogueImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Base des tests fonctionnels : base SQLite remise à zéro, catalogue importé.
 */
abstract class AppWebTestCase extends WebTestCase
{
    use ResetDatabase;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        static::getContainer()->get(CatalogueImporter::class)->importer();
    }

    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function creerAthlete(string $pseudo = 'Testeur', bool $admin = false): User
    {
        $user = (new User())
            ->setEmail(strtolower($pseudo).'@example.test')
            ->setPseudo($pseudo)
            ->setPassword('$2y$04$aaaaaaaaaaaaaaaaaaaaaOaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')
            ->setIsVerified(true)
            ->setRoles($admin ? ['ROLE_ADMIN'] : []);
        $this->em()->persist($user);
        $this->em()->flush();

        return $user;
    }

    protected function wod(string $titre): Wod
    {
        $wod = static::getContainer()->get(WodRepository::class)->findOneBy(['titre' => $titre]);
        self::assertNotNull($wod, "WOD « $titre » introuvable");

        return $wod;
    }
}
