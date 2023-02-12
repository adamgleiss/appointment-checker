<?php

namespace App\Api;

use App\Entity\Appointment;
use App\Trait\MessageEmitterTrait;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GlobalEntryApiWrapper
{
    use MessageEmitterTrait;

    public const OFFICE_LOCATIONS = [
        5446 => 'SFO',
        5002 => 'Otay Mesa',
        8060 => 'San Ysidro',
        5180 => 'Los Angeles International Global Entry EC',
        12021 => 'St. Louis Enrollment Center'
    ];

    private const SCHEDULER_API_URL = 'https://ttp.cbp.dhs.gov/schedulerapi/locations/{location}/slots?startTimestamp={start}&endTimestamp={end}';
    private const TTP_TIME_FORMAT = 'Y-m-d\TH:i';

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getFirstOpenAppointment(int $daysOut, int $location): ?Appointment
    {
        if (! in_array($location, array_keys(self::OFFICE_LOCATIONS))) {
            throw new Exception("Unsupported office ID: $location");
        }

        if ($daysOut > 360 || $daysOut < 1) {
            throw new Exception("Invalid number of days out.");
        }

        $url = $this->buildUrl($location, $daysOut);

        $this->emitMessage("Checking with url $url");

        $response = $this->httpClient->request('GET', $url);
        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new Exception("Bad status: " . $response->getStatusCode() . " from $url ");
        }

        $rawJson = $response->getContent();

        $appointments = json_decode($rawJson, true);

        foreach ($appointments as $appointment) {
            if ($appointment['active'] > 0) {
                return new Appointment(DateTime::createFromFormat(self::TTP_TIME_FORMAT, $appointment['timestamp']));
            }
        }

        return null;
    }

    private function buildUrl(int $location, int $days): string
    {
        $startDate = new DateTime();
        $endDate = new DateTime();
        $endDate->add(new DateInterval("P{$days}D"));

        $url = str_replace('{start}', $startDate->format(self::TTP_TIME_FORMAT), self::SCHEDULER_API_URL);
        $url = str_replace('{end}', $endDate->format(self::TTP_TIME_FORMAT), $url);

        return str_replace('{location}', $location, $url);
    }

}