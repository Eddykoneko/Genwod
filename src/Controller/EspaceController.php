<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ProfilType;
use App\Repository\SeanceRepository;
use App\Repository\WodRepository;
use App\Service\Statistiques;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * L'espace personnel de l'athlète (protégé par access_control : ^/mon-espace).
 */
#[Route('/mon-espace')]
#[IsGranted('ROLE_USER')]
final class EspaceController extends AbstractController
{
    #[Route('', name: 'app_tableau_de_bord', methods: ['GET'])]
    public function tableauDeBord(#[CurrentUser] User $user, Statistiques $stats, SeanceRepository $seances): Response
    {
        return $this->render('espace/tableau_de_bord.html.twig', [
            'resume' => $stats->resume($user),
            'heatmap' => $stats->heatmap($user),
            'dernieres' => $seances->dernieres($user, 6),
            'records' => \array_slice($seances->recordsPersonnels($user), 0, 6),
        ]);
    }

    #[Route('/seances', name: 'app_espace_seances', methods: ['GET'])]
    public function seances(#[CurrentUser] User $user, SeanceRepository $seances): Response
    {
        return $this->render('espace/seances.html.twig', [
            'seances' => $seances->dernieres($user, 200),
            'records' => $seances->recordsPersonnels($user),
        ]);
    }

    #[Route('/favoris', name: 'app_espace_favoris', methods: ['GET'])]
    public function favoris(#[CurrentUser] User $user): Response
    {
        return $this->render('espace/favoris.html.twig', [
            'wods' => $user->getFavoris()->map(static fn ($f) => $f->getWod())->toArray(),
        ]);
    }

    #[Route('/mes-wods', name: 'app_espace_wods', methods: ['GET'])]
    public function wods(#[CurrentUser] User $user, WodRepository $wods): Response
    {
        return $this->render('espace/wods.html.twig', ['wods' => $wods->crees($user)]);
    }

    #[Route('/profil', name: 'app_espace_profil', methods: ['GET', 'POST'])]
    public function profil(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $profil = $this->createForm(ProfilType::class, $user);
        $profil->handleRequest($request);
        if ($profil->isSubmitted() && $profil->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('app_espace_profil', [], Response::HTTP_SEE_OTHER);
        }
        if ($profil->isSubmitted()) {
            // Évite d'afficher un pseudo invalide dans la navigation.
            $em->refresh($user);
        }

        $motDePasse = $this->createForm(ChangePasswordFormType::class, null, ['verifier_actuel' => true]);
        $motDePasse->handleRequest($request);
        if ($motDePasse->isSubmitted() && $motDePasse->isValid()) {
            $user->setPassword($hasher->hashPassword($user, (string) $motDePasse->get('plainPassword')->getData()));
            $em->flush();
            $this->addFlash('success', 'Mot de passe modifié.');

            return $this->redirectToRoute('app_espace_profil', [], Response::HTTP_SEE_OTHER);
        }

        $status = ($profil->isSubmitted() || $motDePasse->isSubmitted()) ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK;

        return $this->render('espace/profil.html.twig', [
            'profil' => $profil,
            'motDePasse' => $motDePasse,
        ], new Response(status: $status));
    }

    /** Droit d'accès et à la portabilité (RGPD) : toutes les données du compte en JSON. */
    #[Route('/export', name: 'app_espace_export', methods: ['GET'])]
    public function export(#[CurrentUser] User $user, SeanceRepository $seances, WodRepository $wods): JsonResponse
    {
        $donnees = [
            'compte' => [
                'email' => $user->getEmail(),
                'pseudo' => $user->getPseudo(),
                'niveau' => $user->getNiveau()?->value,
                'bio' => $user->getBio(),
                'taille' => $user->getTaille(),
                'poids' => $user->getPoids(),
                'inscritLe' => $user->getCreatedAt()->format(\DATE_ATOM),
            ],
            'seances' => array_map(static fn ($s) => [
                'wod' => $s->getWod()?->getTitre(),
                'date' => $s->getRealiseeLe()?->format('Y-m-d'),
                'mode' => $s->getMode()->value,
                'score' => $s->getScoreLisible(),
                'ressenti' => $s->getRessenti(),
                'notes' => $s->getNotes(),
                'points' => $s->getPoints(),
            ], $seances->dernieres($user, \PHP_INT_MAX)),
            'favoris' => $user->getFavoris()->map(static fn ($f) => $f->getWod()->getTitre())->toArray(),
            'wodsCrees' => array_map(static fn ($w) => $w->getTitre(), $wods->crees($user)),
        ];

        $response = new JsonResponse($donnees, json: false);
        $response->setEncodingOptions(\JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'genwod-mes-donnees.json'));

        return $response;
    }

    /** Droit à l'effacement : suppression définitive du compte et de toutes ses données. */
    #[Route('/supprimer', name: 'app_espace_supprimer', methods: ['POST'])]
    public function supprimer(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, Security $security): Response
    {
        if (!$this->isCsrfTokenValid('supprimer-compte', (string) $request->request->get('_token'))
            || !$hasher->isPasswordValid($user, (string) $request->request->get('password'))) {
            $this->addFlash('danger', 'Mot de passe incorrect : le compte n\'a pas été supprimé.');

            return $this->redirectToRoute('app_espace_profil', [], Response::HTTP_SEE_OTHER);
        }

        $security->logout(false);
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Votre compte et toutes vos données ont été supprimés. À bientôt !');

        return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
    }
}
