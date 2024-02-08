<?php

namespace App\Controller;


use App\Entity\Exercice;
use App\Repository\ExerciceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

}