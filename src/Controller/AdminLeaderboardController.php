<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Form\LeaderboardType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LeaderboardRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/leaderboard')]
class AdminLeaderboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_leaderboard_index', methods: ['GET'])]
    public function index(LeaderboardRepository $leaderboardRepository): Response
    {
        return $this->render('admin_leaderboard/index.html.twig', [
            'leaderboards' => $leaderboardRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_admin_leaderboard_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $leaderboard = new Leaderboard();
        $form = $this->createForm(LeaderboardType::class, $leaderboard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($leaderboard);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_leaderboard_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_leaderboard/new.html.twig', [
            'leaderboard' => $leaderboard,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_admin_leaderboard_show', methods: ['GET'])]
    public function show(Leaderboard $leaderboard): Response
    {
        return $this->render('admin_leaderboard/show.html.twig', [
            'leaderboard' => $leaderboard,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_admin_leaderboard_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Leaderboard $leaderboard, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LeaderboardType::class, $leaderboard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_leaderboard_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_leaderboard/edit.html.twig', [
            'leaderboard' => $leaderboard,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_admin_leaderboard_delete', methods: ['POST'])]
    public function delete(Request $request, Leaderboard $leaderboard, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$leaderboard->getId(), $request->request->get('_token'))) {
            $entityManager->remove($leaderboard);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_leaderboard_index', [], Response::HTTP_SEE_OTHER);
    }
}
