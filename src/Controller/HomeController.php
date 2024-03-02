<?php

namespace App\Controller;

use App\Repository\ExerciceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ExerciceRepository $exerciceRepository, SessionInterface $session): Response
    {
       // $exercice = $exerciceRepository->findRandom($session);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
           // 'exercice' => $exercice
        ]);
    }
}
