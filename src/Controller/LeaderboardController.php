<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Repository\ExerciceRepository;
use App\Repository\LeaderboardRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/leaderboard')]
class LeaderboardController extends AbstractController
{
    #[Route('/', name: 'app_leaderboard_index', methods: ['GET'])]
    public function index(LeaderboardRepository $leaderboardRepository, UserRepository $userRepository, ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('leaderboard/index.html.twig', [
            'leaderboards' => $leaderboardRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_leaderboard_show', methods: ['GET'])]
    public function show(Leaderboard $leaderboard): Response
    {
        
        return $this->render('leaderboard/show.html.twig', [
            'leaderboard' => $leaderboard,
        ]);
    }

}
