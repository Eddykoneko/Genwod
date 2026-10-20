<?php

namespace App\Controller;

use App\Repository\SeanceRepository;
use App\Repository\WodRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil', methods: ['GET'])]
    public function __invoke(WodRepository $wods, SeanceRepository $seances, ClockInterface $horloge): Response
    {
        return $this->render('accueil/index.html.twig', [
            'wodDuJour' => $wods->wodDuJour($horloge->now()),
            'populaires' => $wods->populaires(6),
            'stats' => [
                'wods' => $wods->compterPublics(),
                'seances' => $seances->count([]),
                'athletes' => $seances->compterAthletesActifs(),
            ],
        ]);
    }
}
