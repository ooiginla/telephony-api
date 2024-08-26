<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Profile;
use App\Models\Patient;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Visit;
use App\Models\User;

class Continulink
{
    /**
     * Create a new class instance.
     */

    public  $profile;

    public function __construct()
    {
        $this->profile = Profile::retrieve('continulink');
    }

    public function process($payload)
    {
        foreach($payload as $item) {
            $this->processEmployee($item);
            $this->processClient($item);
            $this->processVisit($item);
            $this->processTasks($item);
        }

        // dd('done');
    }

    public function setOrCreateAgency($agency_id, $agency_name="")
    {
        $agency = Agency::where('uuid', $agency_id)->where('profile_id', $this->profile->id)->first();

        if(empty($agency)) {
            $agency = new Agency;
            $agency->uuid = $agency_id;
            $agency->uuid = $this->profile->id;
            $agency->name = $agency_name;
            $agency->save();
        }

        return $agency;
    }

    public function processEmployee($item) 
    {
       $employees =  $item['Worker'] ?? null;
       
       foreach($employees as $employeeObj)
       {
            $employee = $employeeObj['employee'] ?? null;
            $empGroup = $employeeObj['empGroup'] ?? null;
            $empPath = $employeeObj['empPath'] ?? null;
            $empSpecialty = $employeeObj['empSpecialty'] ?? null;

            if(empty($employee)){
                return;
            }

            $user = User::where('profile_id',$this->profile->id)->where('uuid')->first();

            if (empty($user)) {
                $user = new User;
                $user->first_name = $employee['first_name'] ?? '';
                $user->last_name = $employee['last_name'] ?? '';
                $user->middle_name = $employee['middle_name'] ?? '';
                $user->address = $employee['address'] ?? '';
                $user->city = $employee['city'] ?? '';
                $user->state = $employee['state'] ?? '';
                $user->zipcode = $employee['zipcode'] ?? '';
                $user->phone = $employee['phone'] ?? '';
                $user->pin = $employee['access_code'] ?? '';
                $user->status = $employee['active'] ?? false;

                $user->profile_id = $this->profile->id;
                $user->agency_id = $this->setOrCreateAgency($employee['agency_id']);
                $user->save();   
            }
       }
    }

    public function processClient($item) 
    {
       $clients =  $item['Episode'] ?? null;
       
       foreach($clients as $clientObj)
       {
            $client = $clientObj['client'] ?? null;
            $clientGroup = $clientObj['clientGroup'] ?? null;
            $clientPhones = $clientObj['clientPhones'] ?? null;
            $clientAddress = $clientObj['clientAddress'] ?? null;
            $gpsCoords = $clientObj['gpsCoords'] ?? null;
            $episode = $clientObj['episode'] ?? null;
       }
    }

    public function processVisit($item) 
    {
       $visits =  $item['ScheduleService'] ?? null;
       
       foreach($visits as $visitObj)
       {
            $schedule = $visitObj['schedule'] ?? null;
       }
    }

    public function processTasks($item) 
    {
       $tasks =  $item['TaskCode'] ?? null;
       
       foreach($tasks as $taskObj)
       {
            $taskcode = $taskObj['taskcode'] ?? null;
       }
    }
}
