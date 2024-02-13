<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Exercice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavoriController extends AbstractController
{
    #[Route('/favori/{id}', name: 'app_favori')] // Ajout de {id} pour capturer l'ID de l'exercice
    public function favori(Exercice $exercice, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        // Vérifiez si l'utilisateur a déjà mis l'exercice en favori
        $existingFavori = $exercice->getFavoris()->filter(function(Favoris $favori) use ($user) {
            return $favori->getUserId() === $user && $favori->getStatut() === 'favori';
        })->first();

        // Supprimez le favori existant s'il y en a un
        if ($existingFavori) {
            $exercice->removeFavori($existingFavori);
            $manager->remove($existingFavori);
            $manager->flush();
            return $this->redirectToRoute('app_exercice_show', ['id' => $exercice->getId()]);
        }

        // Créez un nouveau favori
        $favori = new Favoris();
        $favori->setUserId($user); // Assurez-vous que la méthode s'appelle setUser et non setUserId
        $favori->setExerciceId($exercice); // Assurez-vous que la méthode s'appelle setExercice et non setExerciceId
        $favori->setStatut('favori');

        $exercice->addFavori($favori);
        $manager->persist($favori);
        $manager->flush();

        return $this->redirectToRoute('app_exercice_show', ['id' => $exercice->getId()]);
    }
}
