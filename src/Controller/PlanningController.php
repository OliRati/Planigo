<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Repository\ReservationRepository;
use App\Service\Agenda;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $reservations = $reservationRepository->findByServiceByDay($service->getId(), $today);

        $agenda = new Agenda();
        $freetabs = $agenda->freeSpace($reservations);

        return $this->render('planning/service.html.twig', [
            'date' => $today,
            "service" => $service,
            'reservations' => $reservations,
            'freetabs' => $freetabs
        ]);
    }

    #[Route(path: '/planning/{service}/reserver', name: 'app_planning_reserver', methods: ['GET'])]
    public function reserverCreneau(Request $request, EntityManagerInterface $em, ReservationRepository $reservationRepository, Service $service): Response
    {
        $today = new DateTimeImmutable();

        $startHour = $request->query->get('start_hour');
        $endHour = $request->query->get('end_hour');
        $confirm = $request->query->get('confirm');

        if (($endHour === null) && ($startHour !== null)) {
            $start = $startHour;
            $end = $request->query->get('endTime');

            [$hDebut, $mDebut] = explode(':', $start);
            $minutesDebut = $hDebut * 60 + $mDebut;

            [$hFin, $mFin] = explode(':', $end);
            $minutesFin = $hFin * 60 + $mFin;

            $tabEndTime = [];

            $curMinutes = $minutesDebut;
            while ($curMinutes < min($minutesFin, $minutesDebut + 4 * 60)) {
                $curMinutes += 30;
                $tabEndTime[] = str_pad((int) ($curMinutes / 60), 2, '0', STR_PAD_LEFT)
                    . ':' .
                    str_pad($curMinutes % 60, 2, '0', STR_PAD_LEFT);
            }

            return $this->render('planning/reserver2.html.twig', [
                'date' => $today,
                "service" => $service,
                "startHour" => $startHour,
                'hours' => $tabEndTime,
            ]);
        }

        if (($endHour !== null) && ($startHour !== null) && ($confirm !== null)) {
            $reservation = new Reservation();
            $reservation->setCreatedAt($today);

            $user = $this->getUser();
            $reservation->setCustomerName($user->getNom() . ' ' . $user->getPrenom());

            $reservation->setUtilisateur($user);

            [$h, $m] = explode(':', $startHour);
            $reservation->setStartAt((clone $today)->setTime((int)$h, (int)$m));

            [$h, $m] = explode(':', $endHour);
            $reservation->setEndAt((clone $today)->setTime((int)$h, (int)$m));

            $reservation->setService($service);

            // Check date consistency
            $agenda = new Agenda();
            $error = $agenda->checkDateValidity($today, $reservation->getStartAt(), $reservation->getEndAt());

            // Disponibilité du créneau horaire
            if (
                empty($error)
                && !$reservationRepository->isAvailable($reservation->getService(), $reservation->getStartAt(), $reservation->getEndAt())
            ) {
                $error = 'Le créneau horaire n\'est pas disponible';
            }

            if (empty($error)) {
                $em->persist($reservation);
                $em->flush();

                if ($this->isGranted("ROLE_ADMIN"))
                    return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);

                return $this->redirectToRoute('app_planning', [], Response::HTTP_SEE_OTHER);
            }
            
            return $this->render('planning/reserver4.html.twig', [
                'date' => $today,
                "service" => $service,
                "startHour" => $startHour,
                'endHour' => $endHour,
                'error' => $error
            ]);
        }

        if (($endHour !== null) && ($startHour !== null)) {
            return $this->render('planning/reserver3.html.twig', [
                'date' => $today,
                "service" => $service,
                "startHour" => $startHour,
                'endHour' => $endHour
            ]);
        }

        $start = $request->query->get('start');
        $end = $request->query->get('end');

        [$hDebut, $mDebut] = explode(':', $start);
        $minutesDebut = $hDebut * 60 + $mDebut;

        [$hFin, $mFin] = explode(':', $end);
        $minutesFin = $hFin * 60 + $mFin;

        $tabStartTime = [];

        while ($minutesDebut < $minutesFin) {
            $tabStartTime[] = str_pad((int) ($minutesDebut / 60), 2, '0', STR_PAD_LEFT)
                . ':' .
                str_pad($minutesDebut % 60, 2, '0', STR_PAD_LEFT);
            $minutesDebut += 30;
        }

        return $this->render('planning/reserver.html.twig', [
            'date' => $today,
            'service' => $service,
            'hours' => $tabStartTime,
            'startTime' => $start,
            'endTime' => $end
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/planning', name: 'app_planning_all')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $today = new DateTimeImmutable();
        $reservations = $reservationRepository->findByDay($today);

        return $this->render('planning/index.html.twig', [
            'date' => $today,
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
