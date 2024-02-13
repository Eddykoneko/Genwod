<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Exercice;
use App\Entity\Commentaire;
use App\Form\Commentaire1Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/exercice/{id}/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setCreatedAt(new DateTimeImmutable());
        $commentaire->setUserId($this->getUser());
        $commentaire->setExerciceId($exercice);
        $form = $this->createForm(Commentaire1Type::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_exercice_show', [
                'id' => $exercice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
            'exercice' => $exercice,
        ]);
    }
}
