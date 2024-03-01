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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HistoriqueExerciceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/leaderboard')]
class LeaderboardController extends AbstractController
{
    #[Route('/', name: 'app_leaderboard_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, ExerciceRepository $exerciceRepository, HistoriqueExerciceRepository $historiqueExerciceRepository, $id = null): Response    {
        $exercices = $exerciceRepository->findAll();

        // Récupérez l'exercice sélectionné
        $id = $request->get('exercice');
        
        // Récupérez l'historique de l'exercice
        $historique = $historiqueExerciceRepository->findBy(['exercice' => $id]);

        $leaderboard = [];
        foreach ($exercices as $exercice) {
            $leaderboard[$exercice->getId()] = $historiqueExerciceRepository->createQueryBuilder('h')
                ->select('u.nom, u.prenom, SUM(h.nombreRepetition) as totalRepetitions')
                ->join('h.user_id', 'u')
                ->where('h.exercice_id = :exerciseId')
                ->setParameter('exerciseId', $exercice->getId())
                ->groupBy('u.id')
                ->orderBy('totalRepetitions', 'DESC')
                ->getQuery()
                ->getResult();
        }
    
        return $this->render('leaderboard/index.html.twig', [
            'exercices' => $exercices,
            'leaderboard' => $leaderboard,
            'id' => $id,
            'historique' => $historique,
        ]);
    }

    #[Route('/exercice/{id?}', name: 'app_leaderboard_exercise', methods: ['GET'])]
    public function exercise($id, EntityManagerInterface $entityManager, ExerciceRepository $exerciceRepository): Response
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
    
        $exercices = $exerciceRepository->findAll();
    
        return $this->render('leaderboard/index.html.twig', [
            'leaderboard' => $leaderboard,
            'exercices' => $exercices,
            'id' => $id,
        ]);
    }

    public function saveExercise(Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupérez les données de l'exercice à partir de la requête
    $data = $request->request->all();

    // Créez un nouvel objet HistoriqueExercice
    $historiqueExercice = new HistoriqueExercice();
    $historiqueExercice->setUserId($data['user_id']);
    $historiqueExercice->setExerciceId($data['exercice_id']);
    $historiqueExercice->setNombreRepetition($data['nombre_repetition']);

    // Enregistrez l'objet HistoriqueExercice dans la base de données
    $entityManager->persist($historiqueExercice);
    $entityManager->flush();

    // Créez un tableau leaderboard
    $leaderboard = [];

    // Ajoutez une nouvelle entrée dans le tableau leaderboard
    $leaderboard[$data['exercice_id']][] = [
        'prenom' => $data['prenom'],
        'nom' => $data['nom'],
        'totalRepetitions' => $data['nombre_repetition'],
    ];

    // Renvoyez une réponse
    return $this->redirectToRoute('app_leaderboard_index', [
        'leaderboard' => $leaderboard,
    ]
    );
}

}
