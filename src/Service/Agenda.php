<?php

namespace App\Service;

class Agenda
{
    private static float $heureMinimale = 8.00;
    private static float $heureMaximale = 19.00;
    private static float $tempsCrenaux = 0.50;
    private static float $reservationMaximal = 4.00;
    private static float $reservationMinimale = 0.50;

    public function getDisponibilitéForDay($date)
    {
        $nombreCrenaux = (Agenda::$heureMaximale - Agenda::$heureMinimale) / Agenda::$tempsCrenaux;
        return [];
    }
}
