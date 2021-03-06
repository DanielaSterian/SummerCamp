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
            ->to($blockee->getUserIdentifier())
            ->subject('Someone blocked you')
            ->text('The person '.$blocker->getUserIdentifier(). ' with the car '.$license_plate.' blocked you!');

        $this->mailer->send($email);
    }

    public function sendBlockerEmail(User $blocker, User $blockee, string $license_plate)
    {
        $email = (new Email())
            ->from('daniela@example.com')
            ->to($blockee->getUserIdentifier())
            ->subject('You blocked someone!')
            ->text('The person '.$blocker->getUserIdentifier(). ' with the car '.$license_plate. ' wants you to unblock him');

        $this->mailer->send($email);
    }

    public function sendRegistrationEmail(User $user)
    {
        $email = (new Email())
            ->from('daniela@example.com')
            ->to($user->getUserIdentifier())
            ->subject('Welcome to WhoBlockedMe, !'.$user->getFirstName() )
            ->text('Your password is: '.$user->getPlainPassword());

        $this->mailer->send($email);
    }
}