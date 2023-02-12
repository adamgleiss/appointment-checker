<?php

namespace App\Entity;

use App\Contract\AppointmentNotificationInterface;
use App\Trait\MessageEmitterTrait;
use Exception;

class AppointmentNotifier
{
    /** @var AppointmentNotificationInterface[] */
    private array $notifiers;

    public function __construct(array $notifiers)
    {
        foreach ($notifiers as $notifier) {
            if (! $notifier instanceof AppointmentNotificationInterface) {
                throw new Exception("Only objects that honor the AppointmentNotificationInterface allowed.");
            }
        }

        $this->notifiers = $notifiers;
    }

    public function setEmitTo(callable $emitToFunction): void
    {
        foreach ($this->notifiers as $notifier) {
           if ($this->notifierCanEmit($notifier)) {
               $notifier->emitTo($emitToFunction);
           }
        }
    }

    public function notifyAll(string $message): void
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->notify($message);
        }
    }

    private function notifierCanEmit(AppointmentNotificationInterface $notifier): bool
    {
        return in_array(MessageEmitterTrait::class, class_uses($notifier), true);
    }
}