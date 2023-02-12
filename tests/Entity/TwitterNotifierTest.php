<?php

namespace App\Tests\Entity;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\TwitterNotifier;
use PHPUnit\Framework\TestCase;

class TwitterNotifierTest extends TestCase
{
    public function testSuccessfulTweet()
    {
        $twitterAuthMock = $this->createMock(TwitterOAuth::class);

        $twitterNotifier = new TwitterNotifier($twitterAuthMock);
        $this->assertTrue($twitterNotifier->notify('test'));
    }

    public function testDuplicateTweet()
    {
        $twitterAuthMock = $this->createMock(TwitterOAuth::class);
        $errorResponse = [
            'errors' => [
                ['code' => 187]
            ]
        ];
        $twitterAuthMock->method('post')
            ->willReturn($errorResponse);

        $twitterNotifier = new TwitterNotifier($twitterAuthMock);

        $testMessage = false;
        $twitterNotifier->emitTo(function($message) use (&$testMessage){
            $testMessage = $message;
        });

        $this->assertFalse($twitterNotifier->notify('test'));
        $this->assertEquals('Duplicate message post attempted.', $testMessage);
    }

    public function testErrorDuringPost()
    {
        $twitterAuthMock = $this->createMock(TwitterOAuth::class);
        $errorResponse = [
            'errors' => [
                ['code' => 2, 'message' => 'Server Error']
            ]
        ];
        $twitterAuthMock->method('post')
            ->willReturn($errorResponse);

        $twitterNotifier = new TwitterNotifier($twitterAuthMock);

        $this->expectExceptionMessage('Server Error');
        $twitterNotifier->notify('test');
    }
}