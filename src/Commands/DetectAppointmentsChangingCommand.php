<?php

namespace Wabel\CertainAPI\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wabel\CertainAPI\Helpers\FileChangesHelper;
use Wabel\CertainAPI\Interfaces\CertainListener;
use Wabel\CertainAPI\Services\DetectAppointmentsChangingsService;

class DetectAppointmentsChangingCommand extends Command
{

    /**
     * @var bool
     */
    private $fileLockAuthorizeRun;

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
     * @var string
     */
    private $filePathEventToCheck;

    /**
     * DetectAppointmentsChangingCommand constructor.
     * @param DetectAppointmentsChangingsService $detectAppointmentsChangingsService
     * @param string $filePathEventToCheck
     * @param string $dirPathHistoryAppointments
     * @param string $fileLockAuthorizeRun
     * @param CertainListener[] $listeners
     * @param null $name
     */
    public function __construct(DetectAppointmentsChangingsService $detectAppointmentsChangingsService,$filePathEventToCheck,$dirPathHistoryAppointments,$fileLockAuthorizeRun, array $listeners=[],$name=null)
    {
        parent::__construct($name);
        $this->detectAppointmentsChangingsService = $detectAppointmentsChangingsService;
        $this->listeners = $listeners;
        $this->dirPathHistoryAppointments = $dirPathHistoryAppointments;
        $this->fileLockAuthorizeRun = $fileLockAuthorizeRun;
        $this->filePathEventToCheck = $filePathEventToCheck;
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
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $eventCode = null;

        //Get the EventCode we need to check.
        if($this->filePathEventToCheck && file_exists($this->filePathEventToCheck)){
            $configurationEventFile = parse_ini_file($this->filePathEventToCheck);
            if(isset($configurationEventFile['eventCode'])){
                $eventCode = $configurationEventFile['eventCode'];
            }
        }
        //That permits to stop the followings instructions when we are makings changes on Certain.
        if(!file_exists($this->fileLockAuthorizeRun.'/detect_appointments_changes.lock') && $eventCode){
            $output->writeln('Detect changes - Run.');
            //Get the online appointments.
            $appointmentsNewCertain = $this->detectAppointmentsChangingsService->getCurrentAppoiments($eventCode);
            $appointmentsNew = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($appointmentsNewCertain);
            //Get the last saved appointments to get old data.
            $appointmentsOldHistoryFilePath = FileChangesHelper::getTheLastAppointmentsSaved($eventCode,$this->dirPathHistoryAppointments);
            if(!$appointmentsOldHistoryFilePath){
                //No files so it's the first time we attempt to synchronize.
                $appointmentsOld = [];
            }else{
                //Get the last old appointments data.
                $appointmentsOldHistory = FileChangesHelper::getJsonContentFromFile($appointmentsOldHistoryFilePath);
                $appointmentsOld = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($appointmentsOldHistory);
            }
            //Check if they are changes.
            $timestamp = time();
            $listChangings = $this->detectAppointmentsChangingsService->detectAppointmentsChangings($appointmentsOld,$appointmentsNew,$timestamp);
            if(!$appointmentsOld || ((isset($listChangings['updated']) && !empty($listChangings['updated']))
                || (isset($listChangings['deleted']) && !empty($listChangings['deleted'])))){
                //Changes? So we save the new online appointments
                FileChangesHelper::saveAppointmentsFileByHistory($this->dirPathHistoryAppointments.'/appointments_'.$eventCode.'.json',json_encode($appointmentsNew));
                $output->writeln('Detect changes - Save Changes');
            }else{
                $output->writeln('Detect changes - No Changes');
            }
            foreach ($this->listeners as $listener){
                //Run Listener. For instance,Here we can use ChangingsToFileListeners to save the changes in file.
                $listener->run($eventCode,$listChangings);
            }
        }else{
            $output->writeln('Detect changes - Stop.');
        }
    }

}