<?php

namespace App\Controller;

use App\Entity\LicensePlate;
use App\Entity\User;
use App\Form\LicensePlateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/profile/{id}", name="profile")
     */
    public function details(User $user, UserInterface $currentUser): Response
    {
        if($user->getEmail() == $currentUser->getUserIdentifier())
        {
            return $this->render('user/profile.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->redirectToRoute('index');
    }

//    /**
//     * @Route("/edit_profile/{id}", name="edit-profile")
//     */
//    public function editProfile(User $user, UserInterface $currentUser): Response
//    {
//        if($user->getEmail() == $currentUser->getUserIdentifier())
//        {
//            $entityManager = $this->getDoctrine()->getRepository(User::class);
//
//        }
//    }
}
