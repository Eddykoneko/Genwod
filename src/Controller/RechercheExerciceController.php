<?php

namespace App\Controller;


use App\Repository\ExerciceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RechercheExerciceController extends AbstractController
{
    #[Route("/recherche", name: "app_recherche_index")]
    public function index(Request $request,ExerciceRepository $exerciceRepository): Response
    {
        $search = $request->query->get('search');
        $dureeMin = $request->query->get('duree_min');
        $dureeMax = $request->query->get('duree_max');

        $dureeMin = is_numeric($dureeMin) ? (int) $dureeMin : null;
        $dureeMax = is_numeric($dureeMax) ? (int) $dureeMax : null;

        $exercices = $exerciceRepository->findBySearch($search, $dureeMin, $dureeMax);
        return $this->render('recherche_exercice/index.html.twig', [
            'controller_name' => 'RechercheExerciceController',
            'exercices' => $exercices,
            'search' => $search,
            'duree_min' => $dureeMin,
            'duree_max' => $dureeMax,
        ]);
    }
// {
//     private $repository;

//     public function __construct(ExerciceRepository $repository)
//     {
//         $this->repository = $repository;
//     }

//     #
//     public function index(Request $request,ExerciceRepository $exerciceRepository) : Response
//         {
//             $search = $request->query->get('search');
//             $exercices = $exerciceRepository->findBySearch($search);
//             return $this->render('recherche_exercice/index.html.twig', [
//                 'controller_name' => 'RechercheExerciceController',
//                 'exercices' => $exercices
//             ]);
//         }
    // {
    //     $search = $request->query->get('search');
    //     $form = $this->createForm(RechercheExerciceType::class);
    //     $form->handleRequest($request);

    //     $exercices = [];

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $data = $form->getData();

    //         // Utilisez le repository pour filtrer les exercices
    //         $exercices = $this->repository->filterExercices($data);
    //     }

    //     return $this->render('recherche_exercice/index.html.twig', [
    //         'form' => $form->createView(),
    //         'exercices' => $exercices,
    //     ]);
    // }
}
