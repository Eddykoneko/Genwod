<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getErrors(true, false)); // Ajoutez cette ligne pour voir les erreurs de validation
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
            $user,
            $form->get('plainPassword')->getData()
            )
        );

                // set the creation date
            $user->setCreatedAt(new DateTimeImmutable());

            // set the role
            $user->setRoles(['ROLE_UNVERIFIED']);

            // set the lastname
            $user->setNom($form->get('nom')->getData());

            // set the firstname
            $user->setPrenom($form->get('prenom')->getData());

            // set the age
            $user->setAge($form->get('age')->getData());
            
            // set the poids
            $user->setPoids($form->get('poids')->getData());

            // set the taille
            $user->setTaille($form->get('taille')->getData());

            // set the genre
            $user->setGenre($form->get('genre')->getData());

            $entityManager->persist($user);
            $entityManager->flush();

            // Générer un lien de confirmation d'email et l'envoyer à l'utilisateur
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@genwod.com', 'No Reply'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Ajouter un message flash pour indiquer à l'utilisateur de vérifier son email
            $this->addFlash('success', 'Un email de confirmation a été envoyé. Veuillez vérifier votre boîte mail.');

            // Rediriger vers la page d'accueil ou une page spécifique
            return $this->redirectToRoute('app_home'); // Changez 'home' par la route de votre choix
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', 'Votre adresse e-mail a été vérifiée. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
    }


}