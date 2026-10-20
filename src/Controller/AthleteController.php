<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\SeanceRepository;
use App\Repository\WodRepository;
use App\Service\Statistiques;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AthleteController extends AbstractController
{
    #[Route('/athletes/{pseudo}', name: 'app_athlete', methods: ['GET'])]
    public function __invoke(
        #[MapEntity(mapping: ['pseudo' => 'pseudo'])] User $athlete,
        Statistiques $stats,
        SeanceRepository $seances,
        WodRepository $wods,
    ): Response {
        return $this->render('athlete/show.html.twig', [
            'athlete' => $athlete,
            'resume' => $stats->resume($athlete),
            'heatmap' => $stats->heatmap($athlete),
            'records' => \array_slice($seances->recordsPersonnels($athlete), 0, 8),
            'wods' => array_values(array_filter($wods->crees($athlete), static fn ($w) => $w->isPublic())),
        ]);
    }
}
