<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class AdminCommandTest extends AppWebTestCase
{
    public function testDonnerLesDroitsDAdministration(): void
    {
        $athlete = $this->creerAthlete();
        self::assertFalse($athlete->isAdmin());

        self::assertNotNull(static::$kernel);
        $commande = new CommandTester((new Application(static::$kernel))->find('app:admin'));

        self::assertSame(Command::FAILURE, $commande->execute(['email' => 'personne@exemple.fr']));
        self::assertSame(Command::SUCCESS, $commande->execute(['email' => strtoupper((string) $athlete->getEmail())]));

        $this->em()->refresh($athlete);
        self::assertTrue($athlete->isAdmin());
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $athlete->getRoles());
    }
}
