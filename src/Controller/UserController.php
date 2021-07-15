<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Entity\User;
use App\Form\LicensePlateType;
use App\Form\UserType;
use App\Repository\LicensePlateRepository;
use App\Service\ActivityService;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ActivityRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

        $form = $this->createForm(UserType::class, $currentUser,[
            'forPass'=>false,
            'forUsual' => true,
        ]);

        if($currentUser)
        {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $image = $form->get('imageFile')->getData();

                if(!empty($image))
                {
                    $filesystem = new Filesystem();
                    if($currentUser->getImage())
                    {
                        $filesystem->remove($this->getParameter('images_directory') . '/' . $currentUser->getImage());
                    }

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

                    $currentUser->setImage($imageName);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($currentUser);
                $entityManager->flush();

                $this->addFlash('success', 'The changes was saved!');

                return $this->redirectToRoute('edit-profile');
            }
        }
        return $this->render('user/edit-profile.html.twig', [
            'form' => $form->CreateView(),
            'user' => $currentUser
        ]);
    }

    /**
     * @Route("/add_car", name="add-car")
     */
    public function addCar(Request $request, ActivityService $activityService,LicensePlateRepository $licensePlateRepo, MailService $mailer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $lp = new LicensePlate();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $form = $this->createForm(LicensePlateType::class, $lp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $finalLP = preg_replace('/[^0-9a-zA-Z]/', '', $lp->getLicensePlate());
            $lp->setLicensePlate(strtoupper($finalLP));
            $entrylicensePlate = $licensePlateRepo->findOneBy(['licensePlate' => $lp->getLicensePlate()]);

                if($entrylicensePlate && !$entrylicensePlate->getUser())
                {
                    $entrylicensePlate->setUser($currentUser);

                    $blocker = $activityService->whoBlockedMe($lp->getLicensePlate());
                    if($blocker)
                    {
                        $blockerLP = $licensePlateRepo->findOneBy(['licensePlate' => $blocker]);
                        $activity = $entityManager->getRepository(Activity::class)->findOneBy(['blocker' => $blockerLP->getLicensePlate()]);

                        $mailer->sendBlockeeEmail($blockerLP->getUser(), $entrylicensePlate->getUser(), $blockerLP->getLicensePlate());
                        $activity->setStatus(1);
                        $this->addFlash('warning', 'Your car has been blocked by '.$activity->getBlocker());
                    }

                    $blockee = $activityService->iveBlockedSomebody($lp->getLicensePlate());
                    if($blockee)
                    {
                        $blockeeLP = $licensePlateRepo->findOneBy(['licensePlate' => $blockee]);
                        $activity = $entityManager->getRepository(Activity::class)->findOneBy(['blockee' => $blockeeLP->getLicensePlate()]);
                        $mailer->sendBlockerEmail($blockeeLP->getUser(), $entrylicensePlate->getUser(), $blockeeLP->getLicensePlate());
                        $activity->setStatus(1);
                        $this->addFlash('warning', 'You blocked the car '.$activity->getBlockee());
                    }
                }
                else {
                    $currentUser->addLicensePlate($lp);
                    $entityManager->persist($lp);
                }

                $entityManager->flush();
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
    public function editCar(Request $request, LicensePlate $licensePlate, ActivityRepository $activityRepo):Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();

        if($currentUser->hasLicensePlate($licensePlate))
        {
            $oldLicensePlate = $licensePlate->getLicensePlate();

            $form = $this->createForm(LicensePlateType::class, $licensePlate);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $lp = $licensePlate->getLicensePlate();
                $activities = $activityRepo->findByBlockersAndBlockees($oldLicensePlate);

                foreach ($activities as $activity)
                {
                    /** @var Activity $activity */
                    if($activity->getBlockee() == $oldLicensePlate)
                    {
                        $activity->setBlockee($lp);
                    }
                    elseif($activity->getBlocker() == $oldLicensePlate)
                    {
                        $activity->setBlocker($lp);
                    }
                }

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

    /**
     * @Route("change_password", name="change-password")
     */
    public function changePassword(Request $request,UserPasswordEncoderInterface $passwordEncoder, MailService $mailer): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $form = $this->createForm(UserType::class, $currentUser, [
            'forPass' => true,
            'forUsual' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
                if($passwordEncoder->isPasswordValid($currentUser, $form->get('currentPassword')->getData()))
                {
                    $password = $passwordEncoder->encodePassword($currentUser, $currentUser->getPlainPassword());
                    $currentUser->setPassword($password);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();

                    $mailer->sendRegistrationEmail($currentUser);

                    $this->addFlash('success', 'Password was changed successfully!');
                    return $this->redirectToRoute('profile');
                }
                else
                {
                    $this->addFlash('danger', 'Current password is not correct!');
                }

//            else
//                {
//                    $this->addFlash('danger', 'The password must have between 5-20 characters!');
//                }

        }

        return $this->render(
            'user/change-password.html.twig',
            array('form' => $form->createView(),
                'user'=>$currentUser)
        );

    }
}
