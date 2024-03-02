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

        // dd($form->getErrors());
        if ($form->isSubmitted() && $form->isValid()) {

           // dump($form->getErrors(true, false)); // Ajoutez cette ligne pour voir les erreurs de validation

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
            $user->setRoles(['ROLE_USER']);

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

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@genwod.com', 'No Reply'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }
// dd('je suis la');
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

/*

            //this controller allows us to register ourselves
    #[Route('/inscription', 'security.registration', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher,
        ): Response {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(RegistrationType::class, $user);
        $form->add('userInfo', AdditionnalType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            // Retrieve the userInfo object from the form
            $userInfo = $form['userInfo']->getData();
            $user->setUserInfo($userInfo);

            // Hash the user's password
            $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $manager->persist($user);  
            
            $userInfo ->setRelation($user);
            $manager->persist($userInfo);
            // dd($userInfo);  
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre compte a bien été créé !'
            );

            return $this->redirectToRoute('security.login');
        }else {
            if (!$form->get('recaptcha')->getData() && $form->isSubmitted()) {
                $this->addFlash('danger', 'Le champ reCAPTCHA doit être coché.');
                return $this->render('pages/security/registration.html.twig', [
                    'form' => $form->createView()
                ]);
            }
        }
        return $this->render('pages/security/registration.html.twig', [
            'form' => $form->createView()
        ]);
      

    }


*/     

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
