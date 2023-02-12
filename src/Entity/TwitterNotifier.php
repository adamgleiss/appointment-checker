<?php

namespace App\Entity;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Contract\AppointmentNotificationInterface;
use App\Trait\MessageEmitterTrait;
use Exception;

class TwitterNotifier implements AppointmentNotificationInterface
{
    use MessageEmitterTrait;

    private TwitterOAuth $twitterOAuth;

    public function __construct(TwitterOAuth $twitterOAuth)
    {
        $this->twitterOAuth = $twitterOAuth;
    }

    public function notify($message): bool
    {
        $this->twitterOAuth->setDecodeJsonAsArray(true);
        $result = $this->twitterOAuth->post("statuses/update", ["status" => $message]);

        $errorCode = $result['errors'][0]['code'] ?? false;

        if ($errorCode == 187) {
            $this->emitMessage('Duplicate message post attempted.');
            return false;
        }

        if ($errorCode) {
            $message = $result['errors'][0]['message'] ?? 'Unspecified error.';
            throw new Exception($message);
        }

        return true;
    }
}