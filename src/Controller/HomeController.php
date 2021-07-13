<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Entity\User;
use App\Form\ActivityBlockeeType;
use App\Form\ActivityBlockerType;
use App\Repository\LicensePlateRepository;
use App\Service\CounterService;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/block_someone", name="block-someone")
     */
    public function blockSomeone(Request $request, MailService $mailer, LicensePlateRepository $licensePlateRepo, CounterService $counterService): Response
    {
        $activity = new Activity();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $nrOfLP = $counterService->countLP($currentUser);

        if($nrOfLP == 0)
        {
            $this->addFlash("danger", 'You need to add your first car!');
            return $this->redirectToRoute('add-car');
        }
        elseif($nrOfLP == 1)
        {
            $form = $this->createForm(ActivityBlockerType::class, $activity,[
                'oneCar' => true,
                'multipleCars' => false,
            ]);
        }
        elseif($nrOfLP > 1)
        {
            $form = $this->createForm(ActivityBlockerType::class, $activity,[
                'oneCar' => false,
                'multipleCars' => true,
            ]);
        }
        else
        {
            $this->addFlash("danger", 'Something is wrong');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $blockeeEntry = $licensePlateRepo->findOneBy(['licensePlate'=>$activity->getBlockee()]);

            if($blockeeEntry)
            {
                $blockerEntry = $licensePlateRepo->findOneBy(['licensePlate' => $activity->getBlocker()]);
                $mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockeeEntry->getUser(), $blockerEntry->getLicensePlate());
                $activity->setStatus(1);
                $this->addFlash('success', 'An email has been sent to the user with the car '.$blockeeEntry);
            }
            else
            {
                $licensePlate = new LicensePlate();

                $initialLP = $activity->getBlockee();
                $finalLP = preg_replace('/[^0-9a-zA-Z]/', '', $initialLP);
                $licensePlate->setLicensePlate(strtoupper($finalLP));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($licensePlate);
                $entityManager->flush();

                $this->addFlash('danger', 'The blockee does not have an account. An email will be sent after registration!');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }
        return $this->render('home/block-someone.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/unblock_me", name="unblock-me")
     */
    public function unblockMe(Request $request, MailService $mailer, LicensePlateRepository $licensePlateRepo, CounterService $counterService): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $activity = new Activity();

        $nrOfLP = $counterService->countLP($currentUser);

        if($nrOfLP == 1)
        {
            $form = $this->createForm(ActivityBlockeeType::class, $activity,[
                'oneCar' => true,
                'multipleCars' => false,
            ]);
        }
        elseif($nrOfLP > 1)
        {
            $form = $this->createForm(ActivityBlockeeType::class, $activity,[
                'oneCar' => false,
                'multipleCars' => true,
            ]);
        }
        elseif($nrOfLP == 0)
        {
            $this->redirectToRoute('add-car');
            $form = $this->createForm(ActivityBlockeeType::class, $activity,[
                'oneCar' => false,
                'multipleCars' => true,
            ]);
            $this->addFlash("danger", 'You need to add your first car!');
        }
        else
        {
            $this->addFlash("danger", 'dhgfdhjfdj');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $blockerEntry = $licensePlateRepo->findOneBy(['licensePlate'=>$activity->getBlocker()]);
            if($blockerEntry)
            {
                $blockeeEntry = $licensePlateRepo->findOneBy(['licensePlate' => $activity->getBlockee()]);
                $mailer->sendBlockerEmail($blockeeEntry->getUser(), $blockerEntry->getUser(), $blockeeEntry->getLicensePlate());
                $activity->setStatus(1);
                $this->addFlash('success', 'An email has been sent to the user with the car '.$blockeeEntry);
            }
            else
            {
                $licensePlate = new LicensePlate();

                $initialLP = $activity->getBlocker();
                $finalLP = preg_replace('/[^0-9a-zA-Z]/', '', $initialLP);
                $licensePlate->setLicensePlate(strtoupper($finalLP));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($licensePlate);
                $entityManager->flush();

                $this->addFlash('danger', 'The blockee does not have an account. An email will be sent after registration!');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('home/unblock-me.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

