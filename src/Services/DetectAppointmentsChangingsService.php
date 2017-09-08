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
            $maxResult = $this->appointmentsCertain->get($eventCode,['start_index'=>0,'max_results'=>99999])->getMaxResults();
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
        foreach ($appointmentsOld as $appointmentOld){
            if(!in_array($appointmentOld,$appointmentsNew)){
                $hasChanged = true;
                break;
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
     * @param array $appointmentsOld
     * @param array $appointmentsNew
     * @return array
     */
    public function getListChangings(array $appointmentsOld,array $appointmentsNew){
        $appointmentsOld = self::recursiveArrayObjectToFullArray($appointmentsOld);
        $appointmentsNew = self::recursiveArrayObjectToFullArray($appointmentsNew);
        $changesList = [];
        if($this->hasChanged($appointmentsOld,$appointmentsNew)){
            $changesList = self::recursiveArrayObjectToFullArray($this->arrayRecursiveDiff($appointmentsOld,$appointmentsNew));
        }
        return $changesList;
    }

    /**
     *
     * @param array $currentAppointments
     * @param array $changingsDetected
     * @return array ['deleted'=>[],'updated'=>[]]
     */
    public function detectDeleteOrUpdated(array $currentAppointments,array $changingsDetected){
        $delete = [];
        $update = [];
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
            }
        }
        return [
            'deleted' => $delete,
            'updated' => $update
        ];
    }

    /**
     * @param array $appointments
     * @param $timestamp
     * @return array
     */
    private function insertDateTimeChanges(array $appointments,$timestamp){
        foreach ($appointments as $key => $appointment){
            $appointment[$key]['dateDetectChanges'] = $timestamp;
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
        $changesList = $this->detectDeleteOrUpdated($appointmentsNew,$changings);
        $insertDateTimeChanges = function ($appointment)use($timestamp){
            if($appointment){
                $appointment['dateDetectChanges'] = $timestamp;
            }
            return $appointment;
        };
        $changesList['updated'] = $this->insertDateTimeChanges($changesList['updated'],$timestamp);
        $changesList['deleted'] = $this->insertDateTimeChanges($changesList['deleted'],$timestamp);
        return $changesList;
    }

}