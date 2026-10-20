<?php

namespace App\Controller;

use App\Repository\SeanceRepository;
use App\Service\Points;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class ClassementController extends AbstractController
{
    private const PERIODES = ['semaine' => 'Cette semaine', 'mois' => 'Ce mois-ci', 'tout' => 'Depuis toujours'];

    #[Route('/classement', name: 'app_classement', methods: ['GET'])]
    public function __invoke(SeanceRepository $seances, ClockInterface $horloge, #[MapQueryParameter] string $periode = 'mois'): Response
    {
        $periode = \array_key_exists($periode, self::PERIODES) ? $periode : 'mois';
        $aujourdhui = $horloge->now()->setTime(0, 0);
        $depuis = match ($periode) {
            'semaine' => $aujourdhui->modify('monday this week'),
            'mois' => $aujourdhui->modify('first day of this month'),
            default => null,
        };

        return $this->render('classement/index.html.twig', [
            'classement' => $seances->classementGlobal($depuis),
            'periode' => $periode,
            'periodes' => self::PERIODES,
            'bareme' => ['base' => Points::BASE, 'rx' => Points::BONUS_RX, 'record' => Points::BONUS_RECORD],
        ]);
    }
}
