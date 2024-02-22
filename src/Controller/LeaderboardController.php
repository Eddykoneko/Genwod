<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Exercice;
use App\Entity\Leaderboard;
use App\Entity\HistoriqueExercice;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LeaderboardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/leaderboard')]
class LeaderboardController extends AbstractController
{
    // #[Route('/', name: 'app_leaderboard_index', methods: ['GET'])]
    // public function index(UserRepository $userRepository): Response
    // {
    //     // Récupérer tous les utilisateurs
    //     $users = $userRepository->findAll();
    
    //     // Triez les utilisateurs par score en ordre décroissant
    //     usort($users, function($a, $b) {
    //         return $b->getScore() - $a->getScore();
    //     });
    
    //     // Si vous voulez seulement le top 10, vous pouvez utiliser array_slice
    //     $users = array_slice($users, 0, 10);
    
    //     return $this->render('leaderboard/index.html.twig', [
    //         'users' => $users,
    //     ]);
    // }

    // #[Route('/{id}', name: 'app_leaderboard_show', methods: ['GET'])]
    // public function show($id, LeaderboardRepository $leaderboardRepository): Response
    // {
    //     $leaderboard = $leaderboardRepository->find($id);
    //     if (!$leaderboard) {
    //         throw $this->createNotFoundException('Le leaderboard demandé n\'existe pas.');
    //     }
        
    //     $user = $leaderboard->getUserId(); 
    //     $exercice = $leaderboard->getExerciceId();
    
    //     return $this->render('leaderboard/show.html.twig', [
    //         'leaderboards' => $leaderboard,
    //         'user' => $user,
    //         'exercice' => $exercice,
    //     ]);
    // }

    #[Route('/exercice/{id?}', name: 'app_leaderboard_exercise', methods: ['GET'])]
    public function exercise($id, EntityManagerInterface $entityManager): Response
{
    $repository = $entityManager->getRepository(HistoriqueExercice::class);

    $queryBuilder = $repository->createQueryBuilder('h')
        ->select('u.nom, u.prenom, SUM(h.nombreRepetition) as totalRepetitions')
        ->join('h.user_id', 'u')
        ->where('h.exercice_id = :exerciseId')
        ->setParameter('exerciseId', $id)
        ->groupBy('u.id')
        ->orderBy('totalRepetitions', 'DESC');

    $leaderboard = $queryBuilder->getQuery()->getResult();

    return $this->render('leaderboard/index.html.twig', [
        'leaderboard' => $leaderboard,
    ]);
}

}
