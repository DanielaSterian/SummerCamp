<?php


namespace App\Service;


use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private MailerInterface $mailer;

    /**
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendBlockeeEmail(User $blocker, User $blockee, string $license_plate)
    {
        $email = (new Email())
            ->from('daniela@example.com')
            ->to($blocker->getUserIdentifier())
            ->subject('Someone blocked you')
            ->text('The person '.$blockee->getUserIdentifier(). ' with car '.$license_plate.' blocked you!');

        $this->mailer->send($email);
    }

    public function sendBlockerEmail(User $blocker, User $blockee, string $license_plate)
    {
        $email = (new Email())
            ->from('daniela@example.com')
            ->to($blocker->getUserIdentifier())
            ->subject('Someone blocked you')
            ->text('The person '.$blockee->getUserIdentifier(). 'with the car '.$license_plate. ' wants to unblock her');

        $this->mailer->send($email);
    }
}