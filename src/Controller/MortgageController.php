<?php

namespace App\Controller;

use App\Entity\Mortgage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MortgageController extends AbstractController
{
    const CONTROLLER_NAME = "MortgageController";

    #[Route('/mortgage', name: 'app_mortgage_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $mortgages = $em->getRepository(Mortgage::class)->findAll();

        return $this->render('mortgage/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'mortgages' => $mortgages,
        ]);
    }
}
