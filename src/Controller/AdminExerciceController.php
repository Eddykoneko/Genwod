<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Form\ExerciceType;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/exercice')]
class AdminExerciceController extends AbstractController
{
    #[Route('/', name: 'app_admin_exercice_index', methods: ['GET'])]
    public function index(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('admin_exercice/index.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_admin_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exercice = new Exercice();
        $exercice->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exercice);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_exercice/new.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_admin_exercice_show', methods: ['GET'])]
    public function show(Exercice $exercice): Response
    {
        return $this->render('admin_exercice/show.html.twig', [
            'exercice' => $exercice,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_admin_exercice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_exercice/edit.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_admin_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exercice->getId(), $request->request->get('_token'))) {

        // Récupérez et supprimez d'abord tous les enregistrements liés dans historique_exercice
        foreach ($exercice->getHistoriqueExercices() as $historiqueExercice) {
            $entityManager->remove($historiqueExercice);
        }

        // Récupérez et supprimez tous les commentaires liés à l'exercice
        foreach ($exercice->getCommentaires() as $commentaire) {
            $entityManager->remove($commentaire);
        }

        // Récupérez et supprimez tous les leaderboards liés à l'exercice
        foreach ($exercice->getLeaderboards() as $leaderboard) {
        $entityManager->remove($leaderboard);
        }

        // Récupérez et supprimez tous les leaderboards liés à l'exercice
        foreach ($exercice->getFavoris() as $favori) {
        $entityManager->remove($favori);
        }

        // Ensuite, supprimez l'exercice lui-même
        $entityManager->remove($exercice);
        $entityManager->flush();

        // Redirigez ou affichez un message de confirmation
        // ...

    //     return new Response(); // Add a return statement to return a Response object
    // }
        }

        return $this->redirectToRoute('app_admin_exercice_index', [], Response::HTTP_SEE_OTHER);
    }
}
