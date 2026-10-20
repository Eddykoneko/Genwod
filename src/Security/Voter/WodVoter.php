<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Wod;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Un WOD privé n'est visible que par son auteur ; seul l'auteur (ou un admin) peut le modifier.
 *
 * @extends Voter<string, Wod>
 */
final class WodVoter extends Voter
{
    public const VOIR = 'WOD_VOIR';
    public const GERER = 'WOD_GERER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VOIR, self::GERER], true) && $subject instanceof Wod;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        $estAuteur = $user instanceof User && $subject->getAuteur()?->getId() === $user->getId();
        $estAdmin = $user instanceof User && $user->isAdmin();

        return match ($attribute) {
            self::VOIR => $subject->isPublic() || $estAuteur || $estAdmin,
            self::GERER => $estAuteur || $estAdmin,
            default => false,
        };
    }
}
