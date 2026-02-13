<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\Agenda;
use App\Repository\ReservationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PlanningController extends AbstractController
{
    #[isGranted('ROLE_USER')]
    #[Route('/planning', name: 'app_planning')]
    public function userIndex(ReservationRepository $reservationRepository): Response
    {
        $today = new DateTimeImmutable();
        $reservations = $reservationRepository->findByUserByDay($this->getUser(), $today);
        
        return $this->render('planning/user.html.twig', [
            'date' => $today,
            'reservations' => $reservations
        ]);
    }

    #[Route(path: '/planning/{service}', name: 'app_planning_service')]
    public function planningsByService(ReservationRepository $reservationRepository, Service $service): Response
    {
        $today = new DateTimeImmutable();
        $reservations = $reservationRepository->findByServiceByDay( $service->getId(), $today);

        $agenda = new Agenda();
        $freetabs = $agenda->freeSpace($reservations);

        return $this->render('planning/service.html.twig', [
            'date' => $today,
            "service" => $service,
            'reservations' => $reservations,
            'freetabs' => $freetabs
        ]);
    }

    #[isGranted('ROLE_USER')]
    #[Route('/admin/planning', name: 'app_planning_all')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $today = new DateTimeImmutable();
        $reservations = $reservationRepository->findByDay($today);

        return $this->render('planning/index.html.twig', [
            'date'=> $today,
            'service' => 'de tous les services',
            'reservations' => $reservations
        ]);
    }

    #[Route(path: '/admin/setup', name: 'app_planning_setup')]
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
