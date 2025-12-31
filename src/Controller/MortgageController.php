<?php

namespace App\Controller;

use App\Entity\Mortgage;
use App\Entity\MortgageInstallment;
use App\Form\MortgageInstallmentType;
use App\Form\MortgageType;
use App\Security\Voter\MortgageVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MortgageController extends BaseController
{
    const CONTROLLER_NAME = "MortgageController";

    #[Route('/mortgage/index', name: 'app_mortgage_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $mortgages = $user ? $em->getRepository(Mortgage::class)->findAllForUser($user) : [];

        return $this->render('mortgage/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'mortgages' => $mortgages,
        ]);
    }

    #[Route('/mortgage/new', name: 'app_mortgage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException();
        }

        $mortgage = new Mortgage();
        $form = $this->createForm(MortgageType::class, $mortgage, ['user' => $user]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $mortgage->setName("MOR - " . $mortgage->getShip()->getName());

            $em->persist($mortgage);
            $em->flush();
            return $this->redirectToRoute('app_mortgage_index');
        }

        $payment = new MortgageInstallment();
        $payment->setMortgage($mortgage);

        return $this->renderTurbo('mortgage/edit.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'mortgage' => $mortgage,
            'form' => $form,
            'last_payment' => $mortgage->getMortgageInstallments()->last(),
        ]);
    }

    #[Route('/mortgage/edit/{id}', name: 'app_mortgage_edit', methods: ['GET', 'POST'])]
    #[\Symfony\Component\Security\Http\Attribute\IsGranted(MortgageVoter::EDIT, subject: 'mortgage')]
    public function edit(
        #[CurrentUser] ?\App\Entity\User $user,
        #[MapEntity(expr: 'repository.findOneForUser(id, user)')] ?Mortgage $mortgage,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$mortgage) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(MortgageType::class, $mortgage, ['user' => $user]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if (
                !$this->isGranted(MortgageVoter::EDIT, $mortgage)
            ) {
                $this->addFlash('error', 'Mortgage Signed, Action Denied!');
                return $this->redirectToRoute('app_mortgage_edit', ['id' => $mortgage->getId()]);
            }

            $action = $request->request->get('action');

            if ($action === 'sign') {
                $mortgage->setSigned(true);
            }

            $em->persist($mortgage);
            $em->flush();
            return $this->redirectToRoute('app_mortgage_edit', ['id' => $mortgage->getId()]);
        }

        $summary = $mortgage->calculate();

        $payment = new MortgageInstallment();
        $payment->setMortgage($mortgage);
        $paymentForm = $this->createForm(MortgageInstallmentType::class, $payment, ['summary' => $summary]);

        return $this->renderTurbo('mortgage/edit.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'mortgage' => $mortgage,
            'summary' => $summary,
            'form' => $form,
            'payment_form' => $paymentForm->createView(),
            'last_payment' => $mortgage->getMortgageInstallments()->last(),
        ]);
    }

    #[Route('/mortgage/delete/{id}', name: 'app_mortgage_delete', methods: ['GET', 'POST'])]
    #[IsGranted(MortgageVoter::DELETE, subject: 'mortgage')]
    public function delete(
        #[CurrentUser] ?\App\Entity\User $user,
        #[MapEntity(expr: 'repository.findOneForUser(id, user)')] ?Mortgage $mortgage,
        EntityManagerInterface $em
    ): Response
    {
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$mortgage) {
            throw new NotFoundHttpException();
        }

        $em->remove($mortgage);
        $em->flush();

        return $this->redirectToRoute('app_mortgage_index');
    }

    #[Route('/mortgage/{id}/pay', name: 'app_mortgage_pay', methods: ['GET', 'POST'])]
    public function pay(
        #[CurrentUser] ?\App\Entity\User $user,
        #[MapEntity(expr: 'repository.findOneForUser(id, user)')] ?Mortgage $mortgage,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$mortgage) {
            throw new NotFoundHttpException();
        }

        $payment = new MortgageInstallment();
        $payment->setMortgage($mortgage);
        $paymentForm = $this->createForm(MortgageInstallmentType::class, $payment);

        $paymentForm->handleRequest($request);
        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $em->persist($payment);
            $em->flush();
        }

        return $this->redirectToRoute('app_mortgage_edit', ['id' => $mortgage->getId()]);
    }
}
