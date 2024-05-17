<?php

namespace App\Controller;

use App\Entity\LikeDislike;
use App\Form\LikeDislikeType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LikeDislikeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/likedislike')]
class AdminLikeDislikeController extends AbstractController
{
    #[Route('/', name: 'app_admin_like_dislike_index', methods: ['GET'])]
    public function index(LikeDislikeRepository $likeDislikeRepository): Response
    {
        return $this->render('admin_like_dislike/index.html.twig', [
            'like_dislikes' => $likeDislikeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_like_dislike_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $likeDislike = new LikeDislike();
        $form = $this->createForm(LikeDislikeType::class, $likeDislike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($likeDislike);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_like_dislike_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_like_dislike/new.html.twig', [
            'like_dislike' => $likeDislike,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_like_dislike_show', methods: ['GET'])]
    public function show(LikeDislike $likeDislike): Response
    {
        return $this->render('admin_like_dislike/show.html.twig', [
            'like_dislike' => $likeDislike,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_like_dislike_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LikeDislike $likeDislike, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LikeDislikeType::class, $likeDislike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_like_dislike_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_like_dislike/edit.html.twig', [
            'like_dislike' => $likeDislike,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_like_dislike_delete', methods: ['POST'])]
    public function delete(Request $request, LikeDislike $likeDislike, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$likeDislike->getId(), $request->request->get('_token'))) {
            $entityManager->remove($likeDislike);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_like_dislike_index', [], Response::HTTP_SEE_OTHER);
    }
}
