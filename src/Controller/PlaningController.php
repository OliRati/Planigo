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
    #[Route('/planing', name: 'app_planing_all')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findByDay(new DateTimeImmutable());

        return $this->render('planing/index.html.twig', [
            'service' => 'de tous les services',
            'reservations' => $reservations
        ]);
    }

    #[Route(path: "/planing/{service}", name: 'app_planing')]
    public function plannings(ReservationRepository $reservationRepository): Response
    {
        $service = "du Spa";

        $reservations = $reservationRepository->findByDay(new DateTimeImmutable());

        return $this->render('planing/index.html.twig', [
            "service" => $service,
            'reservations' => $reservations
        ]);
    }
}
