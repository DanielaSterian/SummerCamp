<?php

namespace App\Controller;

use App\Entity\LicensePlate;
use App\Entity\User;
use App\Form\LicensePlateType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ActivityRepository;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function details(): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        return $this->render('user/profile.html.twig', [
            'user' => $currentUser,
        ]);
    }

    /**
     * @Route("/edit_profile", name="edit-profile")
     */
    public function editProfile(Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $userId = $currentUser->getId();

        $form = $this->createForm(UserType::class, $currentUser,[
            'forPass'=>false,
        ]);

        if($currentUser)
        {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($currentUser);
                $entityManager->flush();

                $this->addFlash('success', 'The changes was saved!');

                return $this->redirectToRoute('edit-profile');
            }
        }
        return $this->render('user/edit-profile.html.twig', [
            'form' => $form->CreateView(),
        ]);
    }

    /**
     * @Route("/add_car", name="add-car")
     */
    public function addCar(Request $request, ActivityRepository $activityRepository, MailerInterface $mailer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $licensePlate = new LicensePlate();

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $userId = $currentUser->getId();

        $form = $this->createForm(LicensePlateType::class, $licensePlate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $currentUser->addLicensePlate($licensePlate);

            $entityManager->persist($licensePlate);
            $entityManager->flush();

//            if($currentActivity = $activityRepository->findByBlockee($licensePlate))
//            {
//                $email = (new Email())
//                    ->from('daniela@example.com')
//                    ->to($currentActivity->getBlockee())
//                    ->subject("You have been blocked!")
//                    ->text("You have been block by: {$currentActivity->getBlocker()}");
//
//                $mailer->send($email);
//            }
            $this->addFlash('success', 'The car was added!');

            return $this->redirectToRoute('list-cars');

//            $referer = $request->headers->get('referer');
//            return new RedirectResponse($referer);

//            return $this->redirect($request->request->get('referer'));
        }
        return $this->render('user/add-car.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/list_cars", name="list-cars")
     */
    public function listCars()
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $userId = $currentUser->getId();

        $licensePlates = $currentUser->getLicensePlates();

        return $this->render('user/list-cars.html.twig', [
            'licensePlates' => $licensePlates,
            'user' => $currentUser,
        ]);
    }

    /**
     * @Route("/delete_car/{id}", name="delete-car")
     */
    public function deleteCar(LicensePlate $licensePlate): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if($currentUser->hasLicensePlate($licensePlate))
        {
            $entityManager->remove($licensePlate);
            $entityManager->flush();

            $this->addFlash('success',
                'The car was deleted!');
        }
        return $this->redirectToRoute('list-cars');
    }

    /**
     * @Route("/edit-car/{id}", name="edit-car")
     */
    public function editCar(Request $request, LicensePlate $licensePlate):Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if($currentUser->hasLicensePlate($licensePlate))
        {
            $form = $this->createForm(LicensePlateType::class, $licensePlate);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($licensePlate);
                $entityManager->flush();

                $this->addFlash('success', 'The license plate was updated!');

                return $this->redirectToRoute('list-cars');
            }
            return $this->render('user/edit-car.html.twig', [
                'form' => $form->CreateView(),
            ]);
        }
        return $this->redirectToRoute('list-cars');
    }
}
