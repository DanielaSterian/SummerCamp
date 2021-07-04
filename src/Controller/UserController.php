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

    /**
     * @Route("/add_car/{id}", name="add-car")
     */
    public function addCar(User $user, Request $request, UserInterface $currentUser): Response
    {
        if($user->getEmail() == $currentUser->getUserIdentifier())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $licensePlate = new LicensePlate();

            $form = $this->createForm(LicensePlateType::class, $licensePlate);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $user->addLicensePlate($licensePlate);

                $entityManager->persist($licensePlate);
                $entityManager->flush();

                return $this->render('user/list-cars.html.twig', [
                    'user' => $user,
                ]);
            }

            return $this->render('user/add-car.html.twig',[
                'form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/list_cars/{id}", name="list-cars")
     */
    public function listCars(User $user, UserInterface $currentUser)
    {
        if($user->getEmail() == $currentUser->getUserIdentifier())
        {
            $licensePlates = $user->getLicensePlates();

            return $this->render('user/list-cars.html.twig',[
                'licensePlates' => $licensePlates,
            ]);
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/delete_car/{id}", name="delete-car")
     */
    public function deleteCar(LicensePlate $licensePlate): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        if($licensePlate != null)
        {
            $entityManager->remove($licensePlate);
            $entityManager->flush();
        }
        return $this->redirectToRoute('home');
    }


}
