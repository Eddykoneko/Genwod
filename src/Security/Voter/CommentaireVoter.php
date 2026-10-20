<?php

namespace App\Security\Voter;

use App\Entity\Commentaire;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Commentaire>
 */
final class CommentaireVoter extends Voter
{
    public const SUPPRIMER = 'COMMENTAIRE_SUPPRIMER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::SUPPRIMER === $attribute && $subject instanceof Commentaire;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return $user->isAdmin() || $subject->getAuteur()?->getId() === $user->getId();
    }
}
