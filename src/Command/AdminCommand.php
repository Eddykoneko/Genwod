<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:admin', description: 'Donne les droits d\'administration à un compte existant.')]
final class AdminCommand
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(SymfonyStyle $io, #[Argument('Adresse e-mail du compte')] string $email): int
    {
        $user = $this->users->findOneBy(['email' => mb_strtolower(trim($email))]);
        if (null === $user) {
            $io->error(\sprintf('Aucun compte avec l\'adresse « %s ».', $email));

            return Command::FAILURE;
        }

        // ROLE_USER est implicite (User::getRoles()) : seul ROLE_ADMIN est stocké en plus.
        $user->setRoles(array_values(array_unique([...array_diff($user->getRoles(), ['ROLE_USER']), 'ROLE_ADMIN'])));
        $this->em->flush();
        $io->success(\sprintf('%s est maintenant administrateur.', $user->getPseudo()));

        return Command::SUCCESS;
    }
}
