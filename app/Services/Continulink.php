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

    public function __construct(Profile $profile)
    {
        $this->profile = $profile->retrieve('continulink');
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
            $agency->profile_id = $this->profile->id;
            $agency->name = $agency_name;
            $agency->save();
        }

        return $agency;
    }

    public function grabClientPhone($clientPhones)
    {
        $phone_list = $clientPhones['clientPhones'];
        $phone = "";
        $prefix = "";

        foreach($phone_list as $phone_entry) 
        {
           $prefix =  (!empty($phone)) ? "|":"";

           $phone .= $prefix.$phone_entry['phone'];
        }

        return $phone;
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

            $user = User::where('profile_id', $this->profile->id)->where('uuid', $employee['external_id'])->first();

            if (empty($user)) {
                $user = new User;
                $user->uuid = $employee['external_id'];
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
                $user->agency_id = $this->setOrCreateAgency($employee['agency_id'])->id;
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

            if(empty($client)){
                return;
            }

            $patient = Patient::where('profile_id',$this->profile->id)->where('uuid',$client['external_id'])->first();

            if (empty($patient)) {
                $patient = new Patient;
                $patient->uuid = $client['external_id'] ?? '';
                $patient->first_name = $client['first_name'] ?? '';
                $patient->last_name = $client['last_name'] ?? '';
                $patient->middle_name = $client['middle_name'] ?? '';
                $patient->address = $client['address'] ?? '';
                $patient->city = $client['city'] ?? '';
                $patient->state = $client['state'] ?? '';
                $patient->zipcode = $client['zipcode'] ?? '';
                $patient->phone = $this->grabClientPhone($clientPhones);
                $patient->profile_id = $this->profile->id;
                $patient->agency_id = $this->setOrCreateAgency($client['agency_id'])->id;
                $patient->save();
            }
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

            // check if exist in Questions
            if(empty($taskcode)){
                return;
            }

            $question = Question::where('profile_id',$this->profile->id)->where('uuid',$taskcode['code'])->first();

            if (empty($question)) {
                $question = new Question;
                $question->uuid = $taskcode['code'];
                $question->name = $taskcode['name'];
                $question->question = $taskcode['description'];
                $question->agency_id = $this->setOrCreateAgency($taskcode['agency_id'])->id;
                $question->profile_id = $this->profile->id;
                $question->type = 'MCQ';
                $question->choices = json_encode([]);
                $question->hash = md5($taskcode['description']);
                $question->save();
            }
       }
    }
}
