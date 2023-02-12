<?php

namespace App\Entity;

use App\Contract\AppointmentNotificationInterface;
use App\Trait\MessageEmitterTrait;
use SendGrid;
use SendGrid\Mail\Mail;

class EmailNotifier implements AppointmentNotificationInterface
{
    use MessageEmitterTrait;

    private SendGrid $sendGrid;
    private string $fromEmail;
    private string $fromName;

    /** @var string[] */
    private array $toEmails;

    public function __construct(SendGrid $sendGrid, string $fromEmail, string $fromName, array $toEmails)
    {
        $this->sendGrid = $sendGrid;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->toEmails = $toEmails;
    }


    public function notify(string $message): bool
    {
       $email = new Mail();
       $email->setFrom('info@adamgleiss.com', 'AppointmentNotifier');
       $email->setSubject('New Appointment Available');
       $email->addTo('adamgleiss@gmail.com');
       $email->addContent('text/plain', $message);
       $email->addContent('text/html', $message);

       $response = $this->sendGrid->send($email);

       $this->emitMessage(sprintf("Response code: %d with message %s", $response->statusCode(), $response->body()));
       return $response->statusCode() == 200;
    }


}