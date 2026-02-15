<?php

namespace App\Service;

use App\Entity\Reservation;
use DateTimeImmutable;

class Agenda
{
    private static string $heureOuverture = '8:00';
    private static string $heureFermeture = '19:00';
    private static string $reservationMaximale = '4:00';
    private static string $reservationMinimale = '0:30';
    private static string $reservationPas = '0:30';
    private static string $workingDays = "lundi,mardi,mercredi,jeudi,vendredi,samedi";

    private function convertTimeToMinutes($time)
    {
        [$h, $m] = explode(':', $time);
        return (int) $h * 60 + (int) $m;
    }

    private function convertMinutesToTime($ticks)
    {
        return intdiv($ticks, 60) . ':' . ($ticks % 60);
    }

    private function isTimeEqual($time1, $time2)
    {
        $minutes1 = $this->convertTimeToMinutes($time1);
        $minutes2 = $this->convertTimeToMinutes($time2);

        return $minutes1 === $minutes2;
    }

    public function getDisponibilitéForDay($date)
    {
        return [];
    }

    public function checkDateValidity(DateTimeImmutable $current, DateTimeImmutable $start, DateTimeImmutable $end)
    {
        // Conversion en timestamp
        $timestampCurrent = $current->getTimestamp();
        $timestampStart = $start->getTimestamp();
        $timestampEnd = $end->getTimestamp();

        [$h, $m] = explode(':', Agenda::$heureOuverture);
        $timestampOuverture = (clone $start)->setTime((int) $h, (int) $m)->getTimestamp();

        [$h, $m] = explode(':', Agenda::$heureFermeture);
        $timestampFermeture = (clone $start)->setTime((int) $h, (int) $m, 0)->getTimestamp();

        [$h, $m] = explode(':', Agenda::$reservationMaximale);
        $maxTimestampEnd = $timestampStart + (int) $h * 60 * 60 + (int) $m * 60;

        [$h, $m] = explode(':', Agenda::$reservationMinimale);
        $minTimestampEnd = $timestampStart + (int) $h * 60 * 60 + (int) $m * 60;

        // Date de début dans le futur
        if ($timestampStart - $timestampCurrent < 0)
            return 'La date de début doit être dans le futur';

        // Date de fin après la date de Début
        if ($timestampEnd - $timestampStart < 0)
            return 'La date de fin doit succeder à la date de début';

        // Durée du créneau ne peut dépasser $reservationMaximale
        if ($timestampEnd > $maxTimestampEnd)
            return 'La durée du créneau ne peut dépasser ' . Agenda::$reservationMaximale . ' heures';

        // Durée du créneau ne peut être inférieure a $reservationMinimale
        if ($timestampEnd < $minTimestampEnd)
            return 'La durée du crèneau doit être au moins ' . Agenda::$reservationMinimale . ' heures';

        // Date de réservation ne peut commencer avant $heureOuverture
        if ($timestampStart < $timestampOuverture)
            return 'Les reservations sont ouvertes a partir de ' . Agenda::$heureOuverture . ' heures';

        // Date de réservation ne peut dépasser $heureFermeture
        if ($timestampEnd > $timestampFermeture)
            return 'Les réservations ne peuvent dépasser la fermeture à ' . Agenda::$heureFermeture . ' heures';

        return false;
    }

    public function checkUserDisponibility($userReservations, $timeStart, $timeEnd)
    {
        $minutesStart = $this->convertTimeToMinutes($timeStart);
        $minutesEnd = $this->convertTimeToMinutes($timeEnd);

        if ($minutesStart > $minutesEnd)
            return "L'heure de début de réservation est inférieure à l'heure de fin";


        foreach ($userReservations as $reservation) {
            $reservedStart = $this->convertTimeToMinutes($reservation->getStartAt()->format('h:m'));
            $reservedEnd = $this->convertTimeToMinutes($reservation->getEndAt()->format('h:m'));

            if (
                (($minutesEnd > $reservedStart) && ($minutesEnd < $reservedEnd))
                || (($minutesStart > $reservedStart) && ($minutesStart < $reservedEnd))
            ) {
                return "Vous avez déjà une reservation dans ce créneau avec " . $reservation->getService()->getNom() ;
            }

        }

        return false;
    }

    public function freeSpace(array $reservations)
    {
        $startTime = $this::$heureOuverture;
        $endTime = $this::$heureFermeture;

        $spaces = [];

        foreach ($reservations as $reservation) {
            if (!$this->isTimeEqual($startTime, $reservation->getStartAt()->format('H:i'))) {
                $spaces[] = [
                    'start' => $startTime,
                    'end' => $reservation->getStartAt()->format('H:i'),
                ];
            }
            $startTime = $reservation->getEndAt()->format('H:i');
        }

        if (!$this->isTimeEqual($startTime, $this::$heureFermeture)) {
            $spaces[] = [
                'start' => $startTime,
                'end' => $this::$heureFermeture
            ];
        }

        return $spaces;
    }
}
