<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Envoi et validation du lien signé de confirmation d'adresse e-mail.
 */
final class EmailVerifier
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $helper,
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function envoyerConfirmation(User $user): void
    {
        $signature = $this->helper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()],
        );

        $this->mailer->send((new TemplatedEmail())
            ->to((string) $user->getEmail())
            ->subject('Confirmez votre adresse e-mail · Genwod')
            ->htmlTemplate('emails/confirmation.html.twig')
            ->context([
                'pseudo' => $user->getPseudo(),
                'lien' => $signature->getSignedUrl(),
                'expiration' => $signature->getExpiresAtIntervalInstance(),
            ]));
    }

    /** @throws VerifyEmailExceptionInterface */
    public function confirmer(Request $request, User $user): void
    {
        $this->helper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), (string) $user->getEmail());
        $user->setIsVerified(true);
        $this->em->flush();
    }
}
