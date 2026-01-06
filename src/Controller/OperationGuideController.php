<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OperationGuideController extends AbstractController
{
    #[Route('/operations/guide', name: 'app_operation_guide')]
    public function index(): Response
    {
        return $this->render('guide/operations.html.twig');
    }
}
