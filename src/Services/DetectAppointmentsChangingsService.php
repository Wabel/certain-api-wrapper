<?php

namespace Wabel\CertainAPI\Services;

use Logger\Formatters\DateTimeFormatter;
use Mouf\Utils\Common\Lock;
use Mouf\Utils\Log\Psr\MultiLogger;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Wabel\CertainAPI\Helpers\FileChangesHelper;
use Wabel\CertainAPI\Interfaces\CertainListener;
use Wabel\CertainAPI\Ressources\AppointmentsCertain;

class DetectAppointmentsChangingsService
{

    /**
     * @var AppointmentsCertain
     */
    private $appointmentsCertain;
    /**
     * @var MultiLogger
     */
    private $logger;
    /**
     * @var Lock
     */
    private $lock;
    /**
     * @var string
     */
    private $dirPathHistoryAppointments;
    /**
     * @var CertainListener[]
     */
    private $listeners;

    /**
     * @param CertainListener[] $listeners
     */
    public function __construct(AppointmentsCertain $appointmentsCertain, MultiLogger $logger, Lock $lock, string $dirPathHistoryAppointments, array $listeners = [])
    {
        $this->appointmentsCertain = $appointmentsCertain;
        $this->logger = $logger;
        $this->lock = $lock;
        $this->dirPathHistoryAppointments = $dirPathHistoryAppointments;
        $this->listeners = $listeners;
    }

    /**
     * @param $eventCode
     * @param null|int $start
     * @param null|int $maxResult
     * @return mixed
     */
    public function getCurrentAppoiments($eventCode, $start = null, $maxResult = null)
    {
        if (!$start) {
            $start = 0;
        }
        if (!$maxResult) {
            $maxResult = 999999;
        }
        return $this->certainAppointmentsList = $this->appointmentsCertain->get($eventCode, ['start_index' => $start, 'max_results' => $maxResult])->getResults()->appointments;
    }

    /**
     * @param array $appointmentsOld
     * @param array $appointmentsNew
     * @return bool
     */
    public function hasChanged(array $appointmentsOld, array $appointmentsNew)
    {
        $hasChanged = false;
        $appointmentsOld = self::recursiveArrayObjectToFullArray($appointmentsOld);
        $appointmentsNew = self::recursiveArrayObjectToFullArray($appointmentsNew);
        //Has change by update or delete
        foreach ($appointmentsOld as $appointmentOld) {
            if (!in_array($appointmentOld, $appointmentsNew)) {
                $hasChanged = true;
                break;
            }
        }
        //Has changes by insertion or update
        if (!$hasChanged) {
            foreach ($appointmentsNew as $appointmentNew) {
                if (!in_array($appointmentNew, $appointmentsOld)) {
                    $hasChanged = true;
                    break;
                }
            }
        }
        return $hasChanged;
    }

    /**
     * @param $object
     * @return array
     */
    public static function objectToArray($object)
    {
        if (is_object($object)) {

            return (array)$object;
        }
        return $object;
    }

    /**
     * @param $appointments
     * @return array
     */
    public static function recursiveArrayObjectToFullArray($appointments)
    {
        return json_decode(json_encode($appointments), true);
    }

    /**
     * @param array $arrayOlds
     * @param array $arrayNews
     * @return array
     */
    private function arrayRecursiveDiff(array $arrayOlds, array $arrayNews)
    {
        $difference = [];
        foreach ($arrayOlds as $key => $arrayOld) {
            if (!in_array($arrayOld, $arrayNews)) {
                $difference[$key] = $arrayOld;
            }
        }
        return $difference;
    }

    /**
     * @param array $arrayOlds
     * @param array $arrayNews
     * @return array
     */
    private function arrayRecursiveDiffNew(array $arrayOlds, array $arrayNews)
    {
        $difference = [];
        $appointmentIdOlds = array_map(function($appointment) {
            return $appointment['appointmentId'];
        }, $arrayOlds);
        foreach ($arrayNews as $key => $arrayNew) {
            if (!in_array($arrayNew['appointmentId'], $appointmentIdOlds)) {
                $difference[$key] = $arrayNew;
            }
        }
        return $difference;
    }

    /**
     * @param array $appointmentsOld
     * @param array $appointmentsNew
     * @return array
     */
    public function getListChangings(array $appointmentsOld, array $appointmentsNew)
    {
        $appointmentsOld = self::recursiveArrayObjectToFullArray($appointmentsOld);
        $appointmentsNew = self::recursiveArrayObjectToFullArray($appointmentsNew);
        $changesListUpdateOrDelete = [];
        $changesListInsert = [];
        if ($this->hasChanged($appointmentsOld, $appointmentsNew)) {
            $changesListUpdateOrDelete = self::recursiveArrayObjectToFullArray($this->arrayRecursiveDiff($appointmentsOld, $appointmentsNew));
            $changesListInsert = self::recursiveArrayObjectToFullArray($this->arrayRecursiveDiffNew($appointmentsOld, $appointmentsNew));
        }
        return [
            'inserted' => $changesListInsert,
            'updated_deleted' => $changesListUpdateOrDelete
        ];
    }

    /**
     *
     * @param array $currentAppointments
     * @param array $changingsDetected
     * @return array ['deleted'=>[],'updated'=>[]]
     */
    public function detectDeleteOrUpdated(array $currentAppointments, array $changingsDetected)
    {
        $delete = [];
        $update = [];
        //@Todo: Detect Fields has changed
        $appointmentsNew = self::recursiveArrayObjectToFullArray($currentAppointments);
        $changings = self::recursiveArrayObjectToFullArray($changingsDetected);
        foreach ($changings as $changing) {
            $registration = $changing['registration']['regCode'];
            $registrationTarget = $changing['targetRegistration']['regCode'];
            $runInNew = 0;
            foreach ($appointmentsNew as $currentAppointment) {
                $registrationCurrent = $currentAppointment['registration']['regCode'];
                $registrationTargetCurrent = $currentAppointment['targetRegistration']['regCode'];
                if (in_array($registration, [$registrationCurrent, $registrationTargetCurrent])
                    && in_array($registrationTarget, [$registrationCurrent, $registrationTargetCurrent])
                    && !in_array($changing, $update) && !in_array($changing, $delete)) {
                    $update[] = $currentAppointment;
                    break;
                }
                $runInNew++;
            }
            if ($runInNew === count($appointmentsNew) && !in_array($changing, $delete)) {
                $delete[] = $changing;
            }

        }
        return [
            'deleted' => $delete,
            'updated' => $update,
        ];
    }

    /**
     * @param array $appointments
     * @param $timestamp
     * @return array
     */
    public static function insertDateTimeChanges(array $appointments, $timestamp)
    {
        foreach ($appointments as $key => $appointment) {
            $appointments[$key]['dateDetectChanges'] = $timestamp;
        }
        return $appointments;
    }

    /**
     * @param array $appointmentsOld
     * @param array $appointmentsNew
     * @param string $timestamp
     * @return array ['deleted'=>[],'updated'=>[]]
     */
    public function detectAppointmentsChangings(array $appointmentsOld, array $appointmentsNew, $timestamp)
    {
        $changings = $this->getListChangings($appointmentsOld, $appointmentsNew);
        $changesList = $this->detectDeleteOrUpdated($appointmentsNew, $changings['updated_deleted']);
        $changesList['inserted'] = self::insertDateTimeChanges($changings['inserted'], $timestamp);
        $changesList['updated'] = self::insertDateTimeChanges($changesList['updated'], $timestamp);
        $changesList['deleted'] = self::insertDateTimeChanges($changesList['deleted'], $timestamp);
        return $changesList;
    }

    public function runCommandForEvent(string $eventCode, OutputInterface $output = null): void
    {
        if ($output) {
            $this->logger->addLogger(new DateTimeFormatter(new ConsoleLogger($output)));
        }
        $codeCheckDirectory = FileChangesHelper::checkDirectory($this->dirPathHistoryAppointments);
        if ($codeCheckDirectory === 'no_directory') {
            $this->logger->error('Path ' . $this->dirPathHistoryAppointments . ' doesn\'t exists.');
            return;
        }
        if ($codeCheckDirectory === 'not_readable') {
            $this->logger->error('Path ' . $this->dirPathHistoryAppointments . ' is not readable.');
            return;
        }
        if ($codeCheckDirectory === 'not_writable') {
            $this->logger->error('Path ' . $this->dirPathHistoryAppointments . ' is not writable.');
            return;
        }
        $this->lock->acquireLock();
        $this->logger->info('Detect changes - Run.');
        //That permits to stop the followings instructions when we are makings changes on Certain.
        //Get the online appointments.
        $appointmentsNewCertain = $this->getCurrentAppoiments($eventCode);
        $appointmentsNew = self::recursiveArrayObjectToFullArray($appointmentsNewCertain);
        //Get the last saved appointments to get old data.
        $appointmentsOldHistoryFilePath = FileChangesHelper::getTheLastAppointmentsSaved($eventCode, $this->dirPathHistoryAppointments);
        if (!$appointmentsOldHistoryFilePath) {
            //No files so it's the first time we attempt to synchronize.
            $appointmentsOld = [];
        } else {
            //Get the last old appointments data.
            $appointmentsOldHistory = FileChangesHelper::getJsonContentFromFile($appointmentsOldHistoryFilePath);
            $appointmentsOld = self::recursiveArrayObjectToFullArray($appointmentsOldHistory);
        }
        //Check if they are changes.
        $timestamp = time();
//        $listChangings = $this->detectAppointmentsChangings($appointmentsOld, $appointmentsNew, $timestamp);
        $listChangings = $this->detectAppointmentsChanges($appointmentsOld, $appointmentsNew, $timestamp);
        if (!$appointmentsOld || ((isset($listChangings['updated']) && !empty($listChangings['updated']))
                || (isset($listChangings['deleted']) && !empty($listChangings['deleted']))  || (isset($listChangings['inserted']) && !empty($listChangings['inserted'])))) {
            //Changes? So we save the new online appointments
            FileChangesHelper::saveAppointmentsFileByHistory($this->dirPathHistoryAppointments . '/appointments_' . $eventCode . '.json', json_encode($appointmentsNew));
            $this->logger->info('Detect changes - Save Changes');
        } else {
            $this->logger->info('Detect changes - No Changes');
        }
        FileChangesHelper::saveAppointmentsFileByHistory($this->dirPathHistoryAppointments . '/changes_' . $eventCode . '.json', json_encode($listChangings));
        foreach ($this->listeners as $listener) {
            //Run Listener. For instance,Here we can use ChangingsToFileListeners to save the changes in file.
            $listener->run($eventCode, $listChangings);
        }
        $this->logger->info('Detect changes - Stop.');
        $this->lock->releaseLock();
    }

    private function detectAppointmentsChanges(array $appointmentsOld, array $appointmentsNew, int $timestamp)
    {
        $oldAppointments = [];
        $newAppointments = [];
        $oldAppointmentIds = [];
        $newAppointmentIds = [];

        foreach ($appointmentsOld as $item) {
            $oldAppointments[$item['appointmentId']] = $item;
            $oldAppointmentIds[] = $item['appointmentId'];
        }
        foreach ($appointmentsNew as $item) {
            $newAppointments[$item['appointmentId']] = $item;
            $newAppointmentIds[] = $item['appointmentId'];
        }

        $data = [
            'inserted' => [],
            'updated' => [],
            'deleted' => [],
        ];

        $data['inserted'] = array_map(static function($item) use ($newAppointments) {
            return $newAppointments[$item];
        }, array_diff($newAppointmentIds, $oldAppointmentIds));

        $data['deleted'] = array_map(static function($item) use ($oldAppointments) {
            return $oldAppointments[$item];
        }, array_diff($oldAppointmentIds, $newAppointmentIds));

        $updatedIds = array_keys(array_intersect_key($newAppointments, $oldAppointments));
        foreach ($updatedIds as $updatedId) {
            // Important we use simple != and not strict !== to avoid issue if positions in array are different
            if ($newAppointments[$updatedId] != $oldAppointments[$updatedId]) {
                $data['updated'][] = $newAppointments[$updatedId];
            }
        }

        $data['inserted'] = self::insertDateTimeChanges($data['inserted'], $timestamp);
        $data['updated'] = self::insertDateTimeChanges($data['updated'], $timestamp);
        $data['deleted'] = self::insertDateTimeChanges($data['deleted'], $timestamp);

        return $data;
    }

}
