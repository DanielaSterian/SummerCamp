<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\MailService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function Sodium\add;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser())
        {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="user_registration")
     */
    public function register(MailerInterface $mailer, Request $request, UserPasswordEncoderInterface $passwordEncoder, MailService $mail)
    {
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'forPass' => false,
        ]);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('imageFile')->getData();

            if(!empty($image))
            {
                $imageName = md5(uniqid()).'.'.$image->guessExtension();
                try
                {
                    $image->move(
                        $this->getParameter('images_directory'),
                        $imageName
                    );
                }
                catch (FileException $e)
                {
                    $this->addFlash('danger', 'Could not upload the image.');
                    $this->redirectToRoute('register');
                }

                $user->setImage($imageName);
            }
            // 3) Encode the password (you could also do this via Doctrine listener)
            //alternative: rand_byt(max_length)

            $generatedPassword = sha1(random_bytes(5));
            $user->setPlainPassword($generatedPassword);
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            $mail->sendRegistrationEmail($user);

            $this->addFlash(
                'success',
                'Your password has been sent to your email'
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'security/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("change_password", name="change-password")
     */
    public function changePassword(Request $request,UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $form = $this->createForm(UserType::class, $currentUser, [
            'forPass' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            if($form->isValid())
            {
                if($passwordEncoder->isPasswordValid($currentUser, $form->get('currentPassword')->getData()))
                {
                    $password = $passwordEncoder->encodePassword($currentUser, $currentUser->getPlainPassword());
                    $currentUser->setPassword($password);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();

                    $email = (new Email())
                        ->from('daniela@example.com')
                        ->to($currentUser->getEmail())
                        ->subject("Welcome to WhoBlockedMe!")
                        ->text("Your new password is: {$currentUser->getPlainPassword()}");

                    $mailer->send($email);

                    $this->addFlash('success', 'Password was changed successfully!');
                    return $this->redirectToRoute('profile');
                }
                else
                {
                    $this->addFlash('danger', 'Current password is not correct!');
                }
            }
//            else
//                {
//                    $this->addFlash('danger', 'The password must have between 5-20 characters!');
//                }

        }

        return $this->render(
            'security/change-password.html.twig',
            array('form' => $form->createView())
        );

    }
}
