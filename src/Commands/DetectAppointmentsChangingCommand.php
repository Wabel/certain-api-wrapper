<?php

namespace Wabel\CertainAPI\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wabel\CertainAPI\Helpers\FileChangesHelper;
use Wabel\CertainAPI\Interfaces\CertainListener;
use Wabel\CertainAPI\Services\DetectAppointmentsChangingsService;

class DetectAppointmentsChangingCommand extends Command
{

    /**
     * @var DetectAppointmentsChangingsService
     */
    private $detectAppointmentsChangingsService;

    /**
     * @var CertainListener[]
     */
    private $listeners;

    /**
     * @var string
     */
    private $dirPathHistoryAppointments;

    /**
     * DetectAppointmentsChangingCommand constructor.
     * @param DetectAppointmentsChangingsService $detectAppointmentsChangingsService
     * @param string $dirPathHistoryAppointments
     * @param CertainListener[] $listeners
     * @param null $name
     */
    public function __construct(DetectAppointmentsChangingsService $detectAppointmentsChangingsService, $dirPathHistoryAppointments, array $listeners = [], $name = null)
    {
        parent::__construct($name);
        $this->detectAppointmentsChangingsService = $detectAppointmentsChangingsService;
        $this->listeners = $listeners;
        $this->dirPathHistoryAppointments = $dirPathHistoryAppointments;
    }

    protected function configure()
    {
        $this
            ->setName('certain:detect-changings')
            ->setDescription('Detect changings of appointments from  Certain Event.')
            ->setHelp(<<<EOT
Request Certain to get appointments and detect changes between to request
EOT
            );
        $this->addArgument('eventCode', InputArgument::REQUIRED, 'Specify the eventCode from Certain');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $codeCheckDirectory = FileChangesHelper::checkDirectory($this->dirPathHistoryAppointments);
        if ($codeCheckDirectory === 'no_directory') {
            $output->writeln('ERROR: Path ' . $this->dirPathHistoryAppointments . ' doesn\'t exists.');
            return;
        }
        if ($codeCheckDirectory === 'not_readable') {
            $output->writeln('ERROR: Path ' . $this->dirPathHistoryAppointments . ' is not readable.');
            return;
        }
        if ($codeCheckDirectory === 'not_writable') {
            $output->writeln('ERROR: Path ' . $this->dirPathHistoryAppointments . ' is not writable.');
            return;
        }
        if (FileChangesHelper::commandIsLocked($this->dirPathHistoryAppointments)) {
            $output->writeln('Lock is active, cannot run command.');
            return;
        }
        FileChangesHelper::lockCommand($this->dirPathHistoryAppointments);
        $eventCode = $input->getArgument('eventCode');
        //That permits to stop the followings instructions when we are makings changes on Certain.
        if ($eventCode) {
            $output->writeln('Detect changes - Run.');
            //Get the online appointments.
            $appointmentsNewCertain = $this->detectAppointmentsChangingsService->getCurrentAppoiments($eventCode);
            $appointmentsNew = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($appointmentsNewCertain);
            //Get the last saved appointments to get old data.
            $appointmentsOldHistoryFilePath = FileChangesHelper::getTheLastAppointmentsSaved($eventCode, $this->dirPathHistoryAppointments);
            if (!$appointmentsOldHistoryFilePath) {
                //No files so it's the first time we attempt to synchronize.
                $appointmentsOld = [];
            } else {
                //Get the last old appointments data.
                $appointmentsOldHistory = FileChangesHelper::getJsonContentFromFile($appointmentsOldHistoryFilePath);
                $appointmentsOld = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($appointmentsOldHistory);
            }
            //Check if they are changes.
            $timestamp = time();
            $listChangings = $this->detectAppointmentsChangingsService->detectAppointmentsChangings($appointmentsOld, $appointmentsNew, $timestamp);
            if (!$appointmentsOld || ((isset($listChangings['updated']) && !empty($listChangings['updated']))
                    || (isset($listChangings['deleted']) && !empty($listChangings['deleted'])))) {
                //Changes? So we save the new online appointments
                FileChangesHelper::saveAppointmentsFileByHistory($this->dirPathHistoryAppointments . '/appointments_' . $eventCode . '.json', json_encode($appointmentsNew));
                $output->writeln('Detect changes - Save Changes');
            } else {
                $output->writeln('Detect changes - No Changes');
            }
            foreach ($this->listeners as $listener) {
                //Run Listener. For instance,Here we can use ChangingsToFileListeners to save the changes in file.
                $listener->run($eventCode, $listChangings);
            }
        } else {
            $output->writeln('Detect changes - Stop.');
        }
        FileChangesHelper::unlockCommand($this->dirPathHistoryAppointments);
    }

}
