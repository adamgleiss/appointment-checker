<?php

namespace App\Command;

use App\Api\GlobalEntryApiWrapper;
use App\Entity\Appointment;
use App\Entity\AppointmentNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:check-appt')]
class AppointmentChecker extends Command
{
    const DELTA_IN_DAYS = 60;
    const DEFAULT_OFFICE_LOCATION = 5002;

    private AppointmentNotifier $appointmentNotifier;
    private GlobalEntryApiWrapper $globalEntryApiWrapper;

    public function __construct(GlobalEntryApiWrapper $globalEntryApiWrapper, AppointmentNotifier $appointmentNotifier)
    {
        $this->appointmentNotifier = $appointmentNotifier;
        $this->globalEntryApiWrapper = $globalEntryApiWrapper;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption(
            'location',
            'l',
            InputArgument::OPTIONAL,
            'The ID of the location to check.',
            self::DEFAULT_OFFICE_LOCATION
        );

        $this->addOption(
            'days',
            'd',
            InputArgument::OPTIONAL,
            'How many days from today to scan for appointments.',
            self::DELTA_IN_DAYS
        );

        $this->setDescription('This command checks for Global Entry appointments.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $numberOfDays = (int)$input->getOption('days');
        $location = (int)$input->getOption('location');

        $outputFunction = function (string $message) use ($output){
             $output->writeln($message);
        };

        $this->appointmentNotifier->setEmitTo($outputFunction);
        $this->globalEntryApiWrapper->emitTo($outputFunction);

        $appointment = $this->globalEntryApiWrapper->getFirstOpenAppointment($numberOfDays, $location);

        if (empty($appointment)) {
            $output->writeln("No open appointments.");

            return Command::SUCCESS;
        }

        $message = sprintf(
            'Found available slot on %s at %s.',
            $appointment->getFormattedDate(),
            GlobalEntryApiWrapper::OFFICE_LOCATIONS[$location]
        );

        $output->writeln($message);

        $this->appointmentNotifier->notifyAll($message);

        return Command::SUCCESS;
    }
}