<?php

namespace App\Controller;


use App\Entity\Exercice;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/exercice')]
class ExerciceController extends AbstractController
{
    #[Route('/', name: 'app_exercice_index', methods: ['GET'])]
    public function index(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('exercice/index.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }

    #[Route('/{id}/commentaire', name: 'app_exercice_show', methods: ['GET'])]
    public function show(Exercice $exercice): Response
    {
        
        $commentaires = $exercice->getCommentaires();
        return $this->render('exercice/show.html.twig', [
            'exercice' => $exercice,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/{id}/historique', name: 'app_exercice_historique', methods: ['GET'])]
    public function historique(Exercice $exercice): Response
    {
        // Récupère l'historique des exercices pour l'exercice donné
        $historiqueExercices = $exercice->getHistoriqueExercices();

        return $this->render('historique_exercice/index.html.twig', [
            'exercice' => $exercice,
            'historiqueExercices' => $historiqueExercices,
        ]);
    }

    #[Route('/{id}/leaderboard', name: 'app_exercice_leaderboard', methods: ['GET'])]
    public function leaderboard(Exercice $exercice): Response
    {
        // Récupère le leaderboard pour l'exercice donné
        $leaderboards = $exercice->getLeaderboards();

        return $this->render('leaderboard/index.html.twig', [
            'exercice' => $exercice,
            'leaderboards' => $leaderboards,
        ]);
    }

    public function deleteExercice(Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        // Récupérez et supprimez d'abord tous les enregistrements liés dans historique_exercice
        foreach ($exercice->getHistoriqueExercices() as $historiqueExercice) {
            $entityManager->remove($historiqueExercice);
        }

        // Ensuite, supprimez l'exercice lui-même
        $entityManager->remove($exercice);
        $entityManager->flush();

        $this->addFlash('success', 'Exercice supprimé avec succès.');
        return $this->redirectToRoute('app_exercice_index');
 // Add a return statement to return a Response object
    }

}