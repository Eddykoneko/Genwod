<?php
namespace App\Controller;

use App\Repository\ExerciceRepository;
use App\Repository\LeaderboardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/leaderboard')]
class LeaderboardController extends AbstractController
{
    #[Route('/', name: 'app_leaderboard_index', methods: ['GET'])]
    public function index(ExerciceRepository $exerciceRepository, LeaderboardRepository $leaderboardRepository): Response
    {
        // Récupérer tous les exercices
        $exercices = $exerciceRepository->findAll();

        // Récupérer le classement global
        $globalLeaderboard = $leaderboardRepository->findGlobalLeaderboard();

        // Récupérer le classement par exercice
        $exerciseLeaderboards = [];
        foreach ($exercices as $exercice) {
            $exerciseLeaderboards[$exercice->getId()] = $leaderboardRepository->findBy(['exercice' => $exercice]);
        }

        return $this->render('leaderboard/index.html.twig', [
            'exercices' => $exercices,
            'globalLeaderboard' => $globalLeaderboard,
            'exerciseLeaderboards' => $exerciseLeaderboards,
        ]);
    }
}