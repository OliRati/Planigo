<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Repository\ReservationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PlaningController extends AbstractController
{
    #[isGranted('ROLE_USER')]
    #[Route('/planing', name: 'app_planing')]
    public function userIndex(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findByUserByDay($this->getUser(), new DateTimeImmutable());

        return $this->render('planing/index.html.twig', [
            'service' => 'de mes reservations',
            'reservations' => $reservations
        ]);
    }

    #[Route(path: '/planing/{service}', name: 'app_planing_service')]
    public function planingsByService(ReservationRepository $reservationRepository, Service $service): Response
    {
        $reservations = $reservationRepository->findByServiceByDay( $service->getId(), new DateTimeImmutable());

        return $this->render('planing/service.html.twig', [
            "service" => $service,
            'reservations' => $reservations
        ]);
    }


    #[Route('/admin/planing', name: 'app_planing_all')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findByDay(new DateTimeImmutable());

        return $this->render('planing/index.html.twig', [
            'service' => 'de tous les services',
            'reservations' => $reservations
        ]);
    }

    #[Route(path: '/admin/setup', name: 'app_planing_setup')]
    public function setup(EntityManagerInterface $em): Response
    {
        $defaults = [
            [
                'nom' => 'Spa',
                'description' => 'Profitez d’un moment de détente absolue avec notre service SPA exclusif, conçu pour votre bien-être et relaxation totale.'
            ],
            [
                'nom' => 'Massage',
                'description' => 'Offrez-vous un massage relaxant aux huiles essentielles, idéal pour relâcher les tensions et revitaliser profondément votre corps.'
            ],
            [
                'nom' => 'Salle de sport',
                'description' => 'Accédez à notre salle de sport moderne et entièrement équipée pour entretenir votre forme et booster votre énergie quotidienne.'
            ],
            [
                'nom' => 'Espace détente',
                'description' => 'Profitez d’un espace de détente privé et raffiné, idéal pour vous ressourcer en toute tranquillité et intimité absolue.'
            ]
        ];

        foreach ($defaults as $default) {
            $service = new Service();
            $service->setNom('' . $default['nom']);
            $service->setDescription($default['description']);

            $em->persist($service);
        }

        $em->flush();

        return $this->redirectToRoute('app_home');
    }
}
