<?php

namespace App\Service;

use App\Entity\Favori;
use App\Entity\User;
use App\Entity\Wod;
use App\Entity\WodLike;
use App\Repository\FavoriRepository;
use App\Repository\WodLikeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * « J'aime » et favoris : bascule on/off, avec compteur de likes dénormalisé sur le WOD.
 */
final class Interactions
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WodLikeRepository $likes,
        private readonly FavoriRepository $favoris,
    ) {
    }

    public function aime(User $user, Wod $wod): bool
    {
        return null !== $this->likes->findOneBy(['user' => $user, 'wod' => $wod]);
    }

    public function estFavori(User $user, Wod $wod): bool
    {
        return null !== $this->favoris->findOneBy(['user' => $user, 'wod' => $wod]);
    }

    /** @return bool nouvel état */
    public function basculerLike(User $user, Wod $wod): bool
    {
        $like = $this->likes->findOneBy(['user' => $user, 'wod' => $wod]);
        if (null !== $like) {
            $this->em->remove($like);
            $wod->setLikesCount($wod->getLikesCount() - 1);
        } else {
            $this->em->persist(new WodLike($user, $wod));
            $wod->setLikesCount($wod->getLikesCount() + 1);
        }
        $this->em->flush();

        return null === $like;
    }

    /** @return bool nouvel état */
    public function basculerFavori(User $user, Wod $wod): bool
    {
        $favori = $this->favoris->findOneBy(['user' => $user, 'wod' => $wod]);
        if (null !== $favori) {
            $this->em->remove($favori);
        } else {
            $this->em->persist(new Favori($user, $wod));
        }
        $this->em->flush();

        return null === $favori;
    }
}
