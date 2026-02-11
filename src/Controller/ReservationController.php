<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Service\Agenda;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $error = '';
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentDate = new DateTimeImmutable();

            $reservation->setCreatedAt($currentDate);

            $user = $this->getUser();

            $reservation->setCustomerName($user->getNom() . ' ' . $user->getPrenom());

            // Check date consistency
            $agenda = new Agenda();
            $error = $agenda->checkDateValidity($currentDate, $reservation->getStartAt(), $reservation->getEndAt());

            // Disponibilité du créneau horaire
            if (
                empty($error)
                && !$reservationRepository->isAvailable($reservation->getStartAt(), $reservation->getEndAt())
            ) {
                $error = 'Le créneau horaire n\'est pas disponible';
            }

            if (empty($error)) {
                $entityManager->persist($reservation);
                $entityManager->flush();

                return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
            }

            if (!empty($error)) {
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                    'error' => $error
                ], new Response('', 422));
            }
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'error' => ''
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $error = '';

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentDate = new DateTimeImmutable();

            // Check date consistency
            $agenda = new Agenda();
            $error = $agenda->checkDateValidity($currentDate, $reservation->getStartAt(), $reservation->getEndAt());

            // Disponibilité du créneau horaire
            if (
                empty($error)
                && !$reservationRepository->isAvailable($reservation->getStartAt(), $reservation->getEndAt())
            ) {
                $error = 'Le créneau horaire n\'est pas disponible';
            }

            if (empty($error)) {
                $entityManager->persist($reservation);
                $entityManager->flush();

                return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
            }

            if (!empty($error)) {
                return $this->render('reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                    'error' => $error
                ], new Response('', 422));
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'error' => ''
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
