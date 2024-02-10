<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Entity\LikeDislike;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LikeDislikeController extends AbstractController
{
    #[Route('/exercice/{id}/like', name: 'exercice_like')]
    public function like(Exercice $exercice, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        // Vérifiez si l'utilisateur a déjà liké l'exercice
        $existingLike = $exercice->getLikeDislikes()->filter(function(LikeDislike $likeDislike) use ($user) {
            return $likeDislike->getUserId() === $user && $likeDislike->getStatut() === 'like';
        })->first();

        // Supprimez le like existant s'il y en a un
        if ($existingLike) {
            $exercice->removeLikeDislike($existingLike);
            $manager->remove($existingLike);
            $manager->flush();
            return $this->redirectToRoute('app_exercice_show', ['id' => $exercice->getId()]);
        }

        // Créez un nouveau like
        $like = new LikeDislike();
        $like->setUserId($user);
        $like->setExerciceId($exercice);
        $like->setStatut('like');

        $exercice->addLikeDislike($like);
        $manager->persist($like);
        $manager->flush();

        return $this->redirectToRoute('app_exercice_show', ['id' => $exercice->getId()]);
    }
}
