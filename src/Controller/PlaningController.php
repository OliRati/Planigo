<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlaningController extends AbstractController
{
    #[Route('/planing', name: 'app_planing')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findByDay(new DateTimeImmutable());

        return $this->render('planing/index.html.twig', [
            'reservations' => $reservations
        ]);
    }
}
