<?php

namespace App\Service;

use DateTimeImmutable;

class Agenda
{
    private static float $heureOuverture = 8;
    private static float $heureFermeture = 19;
    private static float $reservationMaximal = 4.00;
    private static float $reservationMinimale = 0.50;

    public function getDisponibilitéForDay($date)
    {
        return [];
    }

    public function checkDateValidity(DateTimeImmutable $current, DateTimeImmutable $start, DateTimeImmutable $end)
    {
        $timestampOuverture = (clone $start)->setTime(Agenda::$heureOuverture, 0, 0)->getTimestamp();
        $timestampFermeture = (clone $start)->setTime(Agenda::$heureFermeture, 0, 0)->getTimestamp();
        $timestampCurrent = $current->getTimestamp();
        $timestampStart = $start->getTimestamp();
        $timestampEnd = $end->getTimestamp();

        // Date de début dans le futur
        if ($timestampStart - $timestampCurrent < 0)
            return 'La date de début doit être dans le futur';

        // Date de fin après la date de Début
        if ($timestampEnd - $timestampStart < 0)
            return 'La date de fin doit succeder à la date de début';

        // Durée du créneau ne peut dépasser 4 heures soit 14400 secondes
        if ($timestampEnd - $timestampStart > 14400)
            return 'La durée du créneau ne peut dépasser 4 heures';

        if ($timestampStart < $timestampOuverture)
            return 'Les reservations sont ouvertes a partir de ' . Agenda::$heureOuverture . ' heures';

        if ($timestampEnd > $timestampFermeture)
            return 'Les réservations ne peuvent dépasser la fermeture à ' . Agenda::$heureFermeture . ' heures';

        return false;
    }
}
