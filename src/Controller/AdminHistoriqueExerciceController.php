<?php

namespace App\Controller;

use App\Entity\HistoriqueExercice;
use App\Form\HistoriqueExerciceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HistoriqueExerciceRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/historiqueexercice')]
class AdminHistoriqueExerciceController extends AbstractController
{
    #[Route('/', name: 'app_admin_historique_exercice_index', methods: ['GET'])]
    public function index(HistoriqueExerciceRepository $historiqueExerciceRepository): Response
    {
        return $this->render('admin_historique_exercice/index.html.twig', [
            'historique_exercices' => $historiqueExerciceRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_admin_historique_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $historiqueExercice = new HistoriqueExercice();
        $form = $this->createForm(HistoriqueExerciceType::class, $historiqueExercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($historiqueExercice);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_historique_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_historique_exercice/new.html.twig', [
            'historique_exercice' => $historiqueExercice,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_admin_historique_exercice_show', methods: ['GET'])]
    public function show(HistoriqueExercice $historiqueExercice): Response
    {
        return $this->render('admin_historique_exercice/show.html.twig', [
            'historique_exercice' => $historiqueExercice,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_admin_historique_exercice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HistoriqueExercice $historiqueExercice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HistoriqueExerciceType::class, $historiqueExercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_historique_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_historique_exercice/edit.html.twig', [
            'historique_exercice' => $historiqueExercice,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_admin_historique_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, HistoriqueExercice $historiqueExercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$historiqueExercice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($historiqueExercice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_historique_exercice_index', [], Response::HTTP_SEE_OTHER);
    }
}
