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
        $exercices = $exerciceRepository->findBySearch($search);
        return $this->render('recherche_exercice/index.html.twig', [
            'controller_name' => 'RechercheExerciceController',
            'exercices' => $exercices
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
