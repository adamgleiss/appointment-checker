<?php

namespace App\Entity;

use DateTime;

class Appointment
{
    private DateTime $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getFormattedDate(): string
    {
        return $this->dateTime->format("n/j/Y g:i a");
    }


}