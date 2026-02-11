<?php

namespace App\Service;

use DateTimeImmutable;

class Agenda
{
    // Durées en minutes
    private static string $heureOuverture = '8:00';
    private static string $heureFermeture = '19:00';
    private static string $reservationMaximale = '4:00';
    private static string $reservationMinimale = '0:30';

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
        $maxTimestampEnd = $timestampStart + (int)$h*60*60 + (int)$m*60;

        [$h, $m] = explode(':', Agenda::$reservationMinimale);
        $minTimestampEnd = $timestampStart + (int)$h*60*60 + (int)$m*60;

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
}
