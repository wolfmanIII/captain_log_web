<?php

namespace App\Controller;

use App\Entity\Ship;
use App\Entity\ShipAmendment;
use App\Form\ShipAmendmentType;
use App\Repository\ShipAmendmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ShipAmendmentController extends BaseController
{
    const CONTROLLER_NAME = 'ShipAmendmentController';

    #[Route('/ship/{id}/amendments/new', name: 'app_ship_amendment_new', methods: ['GET', 'POST'])]
    public function new(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ShipAmendmentRepository $repository
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException();
        }

        $ship = $em->getRepository(Ship::class)->findOneForUser($id, $user);
        if (!$ship) {
            throw new NotFoundHttpException();
        }

        if (!$ship->hasMortgageSigned()) {
            $this->addFlash('error', 'Amendments are available only after a mortgage is signed.');
            return $this->redirectToRoute('app_ship_edit', ['id' => $ship->getId()]);
        }

        $amendment = (new ShipAmendment())
            ->setShip($ship)
            ->setUser($user);

        $form = $this->createForm(ShipAmendmentType::class, $amendment, [
            'ship' => $ship,
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($amendment);
            $em->flush();

            $this->addFlash('success', 'Amendment recorded.');
            return $this->redirectToRoute('app_ship_edit', ['id' => $ship->getId()]);
        }

        $existing = $repository->findForShip($user, $ship);

        return $this->renderTurbo('ship/amendment_new.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'ship' => $ship,
            'amendments' => $existing,
            'form' => $form,
        ]);
    }
}
