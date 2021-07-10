<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Form\ActivityBlockerType;
use App\Repository\LicensePlateRepository;
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
    public function blockSomeone(Request $request, MailService $mailer, LicensePlateRepository $licensePlateRepo): Response
    {
        $activity = new Activity();

        $form = $this->createForm(ActivityBlockerType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $blockeeEntry = $licensePlateRepo->findOneBy(['licensePlate'=>$activity->getBlockee()]);
            if($blockeeEntry)
            {
                $blockerEntry = $licensePlateRepo->findOneBy(['licensePlate' => $activity->getBlocker()]);
                $mailer->sendBlockeeEmail($blockerEntry->getUser(), $blockeeEntry->getUser(), $blockerEntry->getLicensePlate());
                $activity->setStatus(1);
                $this->addFlash('success', 'The mail was sent to the user with car '.$blockeeEntry);
            }
            else
            {
                $licensePlate = new LicensePlate();
                $entityManager = $this->getDoctrine()->getManager();
                $licensePlate->setLicensePlate($activity->getBlockee());
                $entityManager->persist($licensePlate);
                $entityManager->flush();

                $this->addFlash('danger', 'The blockee do not have an account. The mail will be send after registration!');
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

}

