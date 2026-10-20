<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

final class RegistrationController extends AbstractController
{
    public function __construct(private readonly EmailVerifier $emailVerifier)
    {
    }

    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em, Security $security): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app_tableau_de_bord');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, (string) $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();

            $this->emailVerifier->envoyerConfirmation($user);
            $this->addFlash('success', \sprintf('Bienvenue %s ! Un lien de confirmation vient de vous être envoyé par e-mail.', $user->getPseudo()));

            // Connexion automatique après l'inscription.
            return $security->login($user, 'form_login', 'main') ?? $this->redirectToRoute('app_tableau_de_bord');
        }

        return $this->render('registration/register.html.twig', ['form' => $form]);
    }

    #[Route('/verifier-email', name: 'app_verify_email', methods: ['GET'])]
    public function verifyUserEmail(Request $request, UserRepository $users, #[MapQueryParameter] ?int $id = null): Response
    {
        $user = null !== $id ? $users->find($id) : null;
        if (null === $user) {
            $this->addFlash('danger', 'Lien de confirmation invalide.');

            return $this->redirectToRoute('app_accueil');
        }

        try {
            $this->emailVerifier->confirmer($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', 'Ce lien de confirmation est invalide ou a expiré.');

            return $this->redirectToRoute('app_accueil');
        }

        $this->addFlash('success', 'Adresse e-mail confirmée, merci !');

        return $this->redirectToRoute(null !== $this->getUser() ? 'app_tableau_de_bord' : 'app_login');
    }

    #[Route('/verifier-email/renvoyer', name: 'app_verify_email_resend', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function renvoyer(#[CurrentUser] User $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('renvoyer-confirmation', (string) $request->request->get('_token')) && !$user->isVerified()) {
            $this->emailVerifier->envoyerConfirmation($user);
            $this->addFlash('success', 'Un nouveau lien de confirmation vous a été envoyé.');
        }

        return $this->redirectToRoute('app_tableau_de_bord');
    }
}
