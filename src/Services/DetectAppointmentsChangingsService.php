<?php

namespace Wabel\CertainAPI\Services;

use Wabel\CertainAPI\Ressources\AppointmentsCertain;

class DetectAppointmentsChangingsService
{

    /**
     * @var AppointmentsCertain
     */
    private $appointmentsCertain;

    public function __construct(AppointmentsCertain $appointmentsCertain)
    {
        $this->appointmentsCertain = $appointmentsCertain;
    }

    /**
     * @param $eventCode
     * @param null|int $start
     * @param null|int $maxResult
     * @return mixed
     */
    public function getCurrentAppoiments($eventCode,$start=null,$maxResult=null){
        if(!$start){
            $start = 0;
        }
        if(!$maxResult){
            $maxResult = 999999;
        }
        return $this->certainAppointmentsList = $this->appointmentsCertain->get($eventCode,['start_index'=>$start,'max_results'=>$maxResult])->getResults()->appointments;
    }

    /**
     * @param array $appointmentsOld
     * @param array $appointmentsNew
     * @return bool
     */
    public function hasChanged(array $appointmentsOld,array $appointmentsNew){
        $hasChanged = false;
        $appointmentsOld = self::recursiveArrayObjectToFullArray($appointmentsOld);
        $appointmentsNew = self::recursiveArrayObjectToFullArray($appointmentsNew);
        //Has change by update or delete
        foreach ($appointmentsOld as $appointmentOld){
            if(!in_array($appointmentOld,$appointmentsNew)){
                $hasChanged = true;
                break;
            }
        }
        //Has changes by insertion or update
        if(!$hasChanged){
            foreach ($appointmentsNew as $appointmentNew){
                if(!in_array($appointmentNew,$appointmentsOld)){
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
    public static  function objectToArray($object) {
        if(is_object($object)){

            return (array) $object;
        }
        return $object;
    }

    /**
     * @param $appointments
     * @return array
     */
    public static function recursiveArrayObjectToFullArray($appointments){
        return json_decode(json_encode($appointments), true);
    }

    /**
     * @param array $arrayOlds
     * @param array $arrayNews
     * @return array
     */
    private function arrayRecursiveDiff(array $arrayOlds, array $arrayNews) {
        $difference = [];
        foreach($arrayOlds as $key => $arrayOld){
            if(!in_array($arrayOld,$arrayNews)){
                $difference[$key] = $arrayOld;
            }
        }
        return $difference;
    }

    /**
     * @param array $arrayOlds
     * @param array $arrayNews
     * @param array $existedUpdateOrDelete
     * @return array
     */
    private function arrayRecursiveDiffNew(array $arrayOlds, array $arrayNews, array $existedUpdateOrDelete) {
        $difference = [];
        foreach($arrayNews as $key => $arrayNew){
            if(!in_array($arrayNew,$arrayOlds)
                && !in_array(self::recursiveArrayObjectToFullArray($arrayNew), $existedUpdateOrDelete)){
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
    public function getListChangings(array $appointmentsOld,array $appointmentsNew){
        $appointmentsOld = self::recursiveArrayObjectToFullArray($appointmentsOld);
        $appointmentsNew = self::recursiveArrayObjectToFullArray($appointmentsNew);
        $changesListUpdateOrDelete = [];
        $changesListInsert = [];
        if($this->hasChanged($appointmentsOld,$appointmentsNew)){
            $changesListUpdateOrDelete = self::recursiveArrayObjectToFullArray($this->arrayRecursiveDiff($appointmentsOld,$appointmentsNew));
            $changesListInsert = self::recursiveArrayObjectToFullArray($this->arrayRecursiveDiffNew($appointmentsOld, $appointmentsNew, $changesListUpdateOrDelete));
        }
        $changesList = array_merge($changesListUpdateOrDelete, $changesListInsert);
        return $changesList;
    }

    /**
     *
     * @param array $currentAppointments
     * @param array $changingsDetected
     * @return array ['deleted'=>[],'updated'=>[]]
     */
    public function detectDeleteOrUpdatedOrInserted(array $currentAppointments, array $changingsDetected){
        $delete = [];
        $update = [];
        $insert = [];
        //@Todo: Detect Fields has changed
        $appointmentsNew = self::recursiveArrayObjectToFullArray($currentAppointments);
        $changings = self::recursiveArrayObjectToFullArray($changingsDetected);
        foreach ($changings as $changing){
            $registration = $changing['registration']['regCode'];
            $registrationTarget = $changing['targetRegistration']['regCode'];
            foreach ($appointmentsNew as $currentAppointment){
                $registrationCurrent = $currentAppointment['registration']['regCode'];
                $registrationTargetCurrent = $currentAppointment['targetRegistration']['regCode'];
                if(in_array($registration,[$registrationCurrent,$registrationTargetCurrent])
                    && in_array($registrationTarget,[$registrationCurrent,$registrationTargetCurrent])
                    && !in_array($changing,$update) && !in_array($changing,$delete)) {
                    $update[] = $changing;
                    break;
                }
            }
            if(!in_array($changing,$update) && !in_array($changing,$delete)){
                $delete[] = $changing;
            } else{
                $insert[] = $changing;
            }

        }
        return [
            'deleted' => $delete,
            'updated' => $update,
            'insert' => $insert
        ];
    }

    /**
     * @param array $appointments
     * @param $timestamp
     * @return array
     */
    public static function insertDateTimeChanges(array $appointments,$timestamp){
        foreach ($appointments as $key => $appointment){
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
    public function detectAppointmentsChangings(array $appointmentsOld,array $appointmentsNew,$timestamp){
        $changings = $this->getListChangings($appointmentsOld,$appointmentsNew);
        $changesList = $this->detectDeleteOrUpdatedOrInserted($appointmentsNew,$changings);
        $changesList['inserted'] = self::insertDateTimeChanges($changesList['inserted'],$timestamp);
        $changesList['updated'] = self::insertDateTimeChanges($changesList['updated'],$timestamp);
        $changesList['deleted'] = self::insertDateTimeChanges($changesList['deleted'],$timestamp);
        return $changesList;
    }

}