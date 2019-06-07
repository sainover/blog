<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
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

        if ($this->mailer->send($message) == 0) {
            throw new RuntimeException('An error occurred while sending the message.');
        }
    }
}
