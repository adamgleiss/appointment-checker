<?php

namespace App\Contract;

interface AppointmentNotificationInterface
{
    public function notify(string $message): bool;
}