<?php

namespace App\Trait;

trait MessageEmitterTrait
{
    /** @var callable[] */
    private array $listeners = [];

    /**
     * @param callable(string):void $function
     */
    public function emitTo(callable $function): void
    {
        $this->listeners[] = $function;
    }

    public function emitMessage(string $message): void
    {
        foreach ($this->listeners as $function) {
            $function($message);
        }
    }
}