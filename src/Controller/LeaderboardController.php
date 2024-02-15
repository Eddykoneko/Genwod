<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Exercice;
use App\Entity\Leaderboard;
use App\Repository\UserRepository;
use App\Repository\ExerciceRepository;
use App\Repository\LeaderboardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/leaderboard')]
class LeaderboardController extends AbstractController
{
    #[Route('/', name: 'app_leaderboard_index', methods: ['GET'])]
    public function index(Leaderboard $leaderboard, User $user, Exercice $exercice): Response
    {
        $user = $leaderboard->getUserId();
        $exercice = $leaderboard->getExerciceId();
        return $this->render('leaderboard/index.html.twig', [
            'leaderboards' => $leaderboard,
            'user' => $user,
            'exercice' => $exercice,
        ]);
    }

    #[Route('/{id}', name: 'app_leaderboard_show', methods: ['GET'])]
    public function show($id, LeaderboardRepository $leaderboardRepository): Response
    {
        $leaderboard = $leaderboardRepository->find($id);
        if (!$leaderboard) {
            throw $this->createNotFoundException('Le leaderboard demandé n\'existe pas.');
        }
        
        $user = $leaderboard->getUserId(); 
        $exercice = $leaderboard->getExerciceId();
    
        return $this->render('leaderboard/show.html.twig', [
            'leaderboards' => $leaderboard,
            'user' => $user,
            'exercice' => $exercice,
        ]);
    }
    

}
