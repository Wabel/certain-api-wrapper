<?php

namespace Wabel\CertainAPI\Services;


use PHPUnit\Framework\TestCase;
use Wabel\CertainAPI\CertainApiClient;
use Wabel\CertainAPI\CertainApiService;
use Wabel\CertainAPI\Ressources\AppointmentsCertain;

class DetectAppointmentsChangingsServiceTest extends TestCase
{
    /**
     * @var CertainApiService
     */
    private $certainApiService;

    /**
     * @var array[]
     */
    private $certainAppointmentsList;

    /**
     * @var AppointmentsCertain
     */
    private $appointmentsCertain;

    /**
     * Personal Object to test
     * @return array
     */
    private function personalAppointment(){
        return [
                    "appointmentId"=> 52,
                    "startDate"=> "2015-16-07 11:00:00",
                    "endDate"=> "2015-16-07 11:30:00",
                    "eventCode"=> "eventCodeXYZ",
                    "pkEventId"=> "pkEventIdXYZ",
                    "location"=> "locationXYZ",
                    "status"=> "statusXYZ",
                    "appointmentType"=> "appointmentTypeXYZ",
                    "appointmentRating"=> [
                        2,
                        1
                    ],
                    "appointmentSource"=> "AME",
                    "registration"=> [
                        "regCode"=> "regCodeXYZ",
                        "name"=> "nameXYZ",
                        "jobTitle"=> "jobTitleXYZ",
                        "organization"=> "organizationXYZ",
                        "attendeeType"=> "attendeeTypeXYZ",
                        "city"=> "cityXYZ",
                        "state"=> "stateXYZ"
                    ],
                    "targetRegistration"=> [
                        "regCode"=> "regCodeXYZ",
                        "name"=> "nameXYZ",
                        "jobTitle"=> "jobTitleXYZ",
                        "organization"=> "organizationXYZ",
                        "attendeeType"=> "attendeeTypeXYZ",
                        "city"=> "cityXYZ",
                        "state"=> "stateXYZ"
                    ],
                    "calendar"=> null
                ];
    }

    protected function setUp()
    {
        $certainApiClient =  new CertainApiClient(null,$_ENV['username'],$_ENV['password'],$_ENV['accountCode']);
        $this->certainApiService  = new CertainApiService($certainApiClient);
        $this->appointmentsCertain = new AppointmentsCertain($this->certainApiService);
        $this->certainAppointmentsList = $this->appointmentsCertain->get($_ENV['eventCode'],['start_index'=>0,'max_results'=>10])->getResults()->appointments;
    }

    public function testDetectChangings(){
        $detectService = new DetectAppointmentsChangingsService($this->appointmentsCertain);
        $oldAppointments = $this->certainAppointmentsList;
        $currentAppoiments = $oldAppointments;
        unset($currentAppoiments[0]);
        unset($currentAppoiments[1]);
        $hasChanged = $detectService->hasChanged($oldAppointments,$currentAppoiments);
        $this->assertTrue($hasChanged);
    }

    public function testGetListChangings(){
        $detectService = new DetectAppointmentsChangingsService($this->appointmentsCertain);
        $oldAppointments = $this->certainAppointmentsList;
        $currentAppoiments = $oldAppointments;
        $delete1 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[0]);
        $delete2 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[1]);
        $delete3 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[4]);
        unset($currentAppoiments[0]);
        unset($currentAppoiments[1]);
        unset($currentAppoiments[4]);
        $changings = $detectService->getListChangings($oldAppointments,$currentAppoiments);
        $this->assertEquals(3,count($changings));
        $this->assertContains($delete1,$changings);
        $this->assertContains($delete2,$changings);
        $this->assertContains($delete3,$changings);
    }

    public function testDetectDeleteOrUpdated(){
        $detectService = new DetectAppointmentsChangingsService($this->appointmentsCertain);
        $oldAppointments = $this->certainAppointmentsList;
        $oldAppointments[10] = $this->personalAppointment();
        $currentAppoiments = $oldAppointments;
        $delete1 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[1]);
        $delete2 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[4]);
        unset($currentAppoiments[1]);
        unset($currentAppoiments[4]);
        $update1 = $currentAppoiments[10];
        $currentAppoiments[10]["startDate"] = "2015-17-07 12:00:00";
        $currentAppoiments[10]["endDate"] = "2015-17-07 12:30:00";
        $changings = $detectService->getListChangings($oldAppointments,$currentAppoiments);
        $listDetected = $detectService->detectDeleteOrUpdated($currentAppoiments,$changings);
        $this->assertEquals(3,count($changings));
        $this->assertArrayHasKey('deleted',$listDetected);
        $this->assertArrayHasKey('updated',$listDetected);
        $this->assertEquals(2,count($listDetected['deleted']));
        $this->assertEquals(1,count($listDetected['updated']));
        $this->assertContains($delete1,$listDetected['deleted']);
        $this->assertContains($delete2,$listDetected['deleted']);
        $this->assertContains($update1,$listDetected['updated']);
    }

    public function testDetectAppointmentsChangings(){
        $detectService = new DetectAppointmentsChangingsService($this->appointmentsCertain);
        $oldAppointments = $this->certainAppointmentsList;
        $oldAppointments[10] = $this->personalAppointment();
        $currentAppoiments = $oldAppointments;
        $time = time();
        $delete1 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[1]);
        $delete1['dateDetectChanges'] = $time;
        $delete2 = DetectAppointmentsChangingsService::recursiveArrayObjectToFullArray($currentAppoiments[4]);
        $delete2['dateDetectChanges'] = $time;
        unset($currentAppoiments[1]);
        unset($currentAppoiments[4]);
        $update1 = $currentAppoiments[10];
        $update1['dateDetectChanges'] = $time;
        $currentAppoiments[10]["startDate"] = "2015-17-07 12:00:00";
        $currentAppoiments[10]["endDate"] = "2015-17-07 12:30:00";
        $listDetected = $detectService->detectAppointmentsChangings($oldAppointments,$currentAppoiments,$time);
        $this->assertContains($delete1,$listDetected['deleted']);
        $this->assertContains($delete2,$listDetected['deleted']);
        $this->assertContains($update1,$listDetected['updated']);
    }

}