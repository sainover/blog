<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Console\Exception\RuntimeException;
use Twig\Environment;

class EmailsService
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer, Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function sendEmailConfirmation(User $user): void
    {
        $message = (new \Swift_Message('Email confirmation'))
            ->setFrom('blog.noveo@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'emails/email_confirmation.html.twig', [
                        'user' => $user,
                    ]
                ),
                'text/html'
            )
        ;

        if (0 === $this->mailer->send($message)) {
            throw new RuntimeException('An error occurred while sending the message.');
        }
    }

    public function sendPasswordResetting(User $user)
    {
        $message = (new \Swift_Message('Password reset'))
            ->setFrom('blog.noveo@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'emails/password_resetting.html.twig', [
                        'user' => $user,
                    ]
                ),
                'text/html'
            )
        ;

        if (0 === $this->mailer->send($message)) {
            throw new RuntimeException('An error occurred while sending the message.');
        }
    }
}
