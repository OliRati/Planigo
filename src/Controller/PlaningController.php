<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlaningController extends AbstractController
{
    #[Route('/planing', name: 'app_planing')]
    public function index(): Response
    {
        return $this->render('planing/index.html.twig', [
            'controller_name' => 'PlaningController',
        ]);
    }
}
