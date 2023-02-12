<?php

namespace App\Tests\Entity;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Contract\AppointmentNotificationInterface;
use App\Entity\AppointmentNotifier;
use App\Entity\TwitterNotifier;
use PHPUnit\Framework\TestCase;

class AppointmentNotifierTest extends TestCase
{
    public function testNotifyAll()
    {
        $message = 'test message';

        $emailMock = $this->createMock(AppointmentNotificationInterface::class);
        $emailMock->expects($this->once())
            ->method('notify')
            ->with($message);

        $twitterMock = $this->createMock(TwitterNotifier::class);
        $twitterMock->expects($this->once())
            ->method('notify')
            ->with($message);

        $notifier = new AppointmentNotifier([$emailMock, $twitterMock]);
        $notifier->notifyAll($message);
    }

    public function testWithWrongTypeInConstructor()
    {
        $emailMock = $this->createMock(AppointmentNotificationInterface::class);
        $emailMock->expects($this->never())
            ->method('notify');

        $this->expectExceptionMessage('Only objects that honor the AppointmentNotificationInterface allowed.');
        $notifier = new AppointmentNotifier([$emailMock, 'sdsfdsf']);
    }
}