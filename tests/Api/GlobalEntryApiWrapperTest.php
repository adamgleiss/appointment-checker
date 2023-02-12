<?php

namespace App\Tests\Api;

use App\Api\GlobalEntryApiWrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GlobalEntryApiWrapperTest extends TestCase
{
    public function testOpenAppointments()
    {
        $mockResponse = new MockResponse($this->getJsonResponse());
        $mockClient = new MockHttpClient($mockResponse);
        $globalEntryWrapper = new GlobalEntryApiWrapper($mockClient);

        $appointment = $globalEntryWrapper->getFirstOpenAppointment(3, 5002);

        $this->assertNotEmpty($appointment);
        $this->assertEquals('2/5/2023 8:30 am', $appointment->getFormattedDate());
    }

    public function testNoAppointments()
    {
        $mockResponse = new MockResponse($this->getJsonResponse(0));
        $mockClient = new MockHttpClient($mockResponse);
        $globalEntryWrapper = new GlobalEntryApiWrapper($mockClient);

        $appointment = $globalEntryWrapper->getFirstOpenAppointment(3, 5002);

        $this->assertNull($appointment);
    }

    public function testErrorWithHttpClient()
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $mockClient = new MockHttpClient($mockResponse);

        $globalEntryWrapper = new GlobalEntryApiWrapper($mockClient);

        $this->expectExceptionMessage('Bad status: 500');
        $globalEntryWrapper->getFirstOpenAppointment(3, 5002);
    }

    public function testBadLocationID()
    {
        $mockClient = new MockHttpClient();

        $globalEntryWrapper = new GlobalEntryApiWrapper($mockClient);

        $this->expectExceptionMessage('Unsupported office ID: 1');
        $globalEntryWrapper->getFirstOpenAppointment(3, 1);
    }

    public function testBadDaysOut()
    {
        $mockClient = new MockHttpClient();

        $globalEntryWrapper = new GlobalEntryApiWrapper($mockClient);

        $this->expectExceptionMessage('Invalid number of days out.');
        $globalEntryWrapper->getFirstOpenAppointment(1000, 5002);
    }

    private function getJsonResponse(int $activeAppointments = 2): string
    {
        return  <<<JSON
[ {
  "active" : $activeAppointments,
  "total" : 2,
  "pending" : 0,
  "conflicts" : 0,
  "duration" : 15,
  "timestamp" : "2023-02-05T08:30",
  "remote" : false
}, {
  "active" : 0,
  "total" : 2,
  "pending" : 0,
  "conflicts" : 0,
  "duration" : 15,
  "timestamp" : "2023-02-05T08:45",
  "remote" : false
}]
JSON;
    }
}