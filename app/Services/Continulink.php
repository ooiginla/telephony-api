<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

use App\Models\Agency;
use App\Models\Profile;
use App\Models\Patient;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Visit;
use App\Models\User;
use App\Models\Careplan;
use App\Models\Telelog;
use Carbon\Carbon;

class Continulink
{
    /**
     * Create a new class instance.
     */

    public  $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile->retrieve('continulink');

        $this->processed = [
            'Employees' => [],
            'Clients' => [],
            'Tasks' => [],
            'Schedules' => [],
            'Careplans' => []
        ];
    }

    public function process($payload)
    {
        try{
            foreach($payload as $item) 
            {
                $this->processEmployee($item);            
                $this->processClient($item);
                $this->processTasks($item);
                $this->processCareplan($item);
                $this->processVisit($item);
            }

            return ['status' => true, 'message' => 'successful', 'data' => $this->processed];

        }catch(\Exception $e){
            $message = "Continulink: Error Occured". $e->getMessage();
            Log::error($message);

            return ['status' => false, 'message' => $message, 'data' => $this->processed];
        }
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

        return $agency->id;
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

    public function getEmployeeSpecialties($specialties)
    {
        return implode(",", ($pecialties['empSpecialties'] ?? []));
    }

    public function processEmployee($item) 
    {
       $employees =  $item['Worker'] ?? [];
       
       foreach($employees as $employeeObj)
       {
            $employee = $employeeObj['employee'] ?? null;
            $empGroup = $employeeObj['empGroup'] ?? null;
            $empPath = $employeeObj['empPath'] ?? null;
            $empSpecialty = $employeeObj['empSpecialty'] ?? null;

            if(empty($employee)){
                return;
            }

            $this->logPath('employee', $employee['external_id'], $employee);

            $user = User::where('profile_id', $this->profile->id)->where('uuid', $employee['external_id'])->first();

            if (empty($user)) {
                $user = new User;
            }
            
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
            $user->specialties = $this->getEmployeeSpecialties($empSpecialty);
            $user->profile_id = $this->profile->id;
            $user->agency_id = $this->setOrCreateAgency($employee['agency_id']);
            $user->timezone = $employee['time_zone'] ?? NULL;
            $user->save();   

            array_push($this->processed['Employees'], $employee['external_id']);
       }
    }

    public function processClient($item) 
    {
       $clients =  $item['Episode'] ?? [];
       
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

            $this->logPath('client', $client['external_id'], $client);

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
                $patient->agency_id = $this->setOrCreateAgency($client['agency_id']);
                $patient->save();
            }

            array_push($this->processed['Clients'], $client['external_id']);
        }
    }

    public function setOrCreateModel($model, $agency_id, $uuid_value)
    {
        $object = $model->where('uuid', $uuid_value)->where('profile_id', $this->profile->id)->first();

        if(empty($object)) {
            $object = $model;
            $object->uuid = $uuid_value;
            $object->agency_id = $agency_id;
            $object->profile_id = $this->profile->id;
            $object->save();
        }

        return $object->id;
    }

    public function convertVisitDate($date)
    {
        // sample - "4/29/2019 1:00:00 PM
        $pattern = "/[-\s:\/]/";
        $comp = preg_split($pattern, $date);

        if($comp[6] == "PM" && $comp[3] < 12){
            $comp[3] = $comp[3] + 12;
        }
        
        $timestamp = mktime($comp[3], $comp[4], $comp[5], $comp[0], $comp[1], $comp[2]);

        return date("Y-m-d H:i:s", $timestamp);
    }

    public function processVisit($item) 
    {
       $visits =  $item['ScheduleService'] ?? [];
       $visit = null;
       
       // create schedule
       foreach($visits as $visitObj)
       {
            $schedule = $visitObj['schedule'] ?? null;
            $question_set = [];

            if(empty($schedule)){
                return;
            }

            $visit = Visit::where('profile_id',$this->profile->id)->where('uuid',$schedule['id'])->first();

            if (empty($visit)) {
                $visit = new Visit;
            }

            $this->logPath('schedule', $schedule['id'], $schedule);

            $visit->agency_id = $this->setOrCreateAgency($schedule['agency_id']);
            $visit->uuid = $schedule['id'] ?? '';
            $visit->episode_id = $schedule['episode_id'] ?? '';
            $visit->patient_id = $this->setOrCreateModel(new Patient, $visit->agency_id, $schedule['external_id']);
            $visit->user_id = $this->setOrCreateModel(new User, $visit->agency_id, $schedule['employee_id']);
            $visit->visit_start = $this->convertVisitDate($schedule['start']) ?? null;
            $visit->visit_end = $this->convertVisitDate($schedule['end']) ?? null;
            $visit->visit_type = $schedule['type'] ?? '';
            $visit->schedule_type = $schedule['schedule_type'] ?? '';
            $visit->status = ($schedule['active']) ? true : false;
            $visit->profile_id = $this->profile->id;
            $visit->save();

            array_push($this->processed['Schedules'], $schedule['id']); 
            
            $this->loadQuestionSet($visit);
       }
    }
    
    public function processCareplan($item) 
    {
        $careplans =  $item['CarePlan'] ?? [];

       foreach($careplans as $careplanObj)
       {
            $agency_id = $this->setOrCreateAgency($careplanObj['agency_id']);
            $patient_id = $this->setOrCreateModel(new Patient, $agency_id, $careplanObj['external_id']);

            $this->logPath('careplan', $careplanObj['id'], $careplanObj);

            $has_tasks = false;
            
            if(isset($careplanObj['codes']) && !empty($careplanObj['codes'])) 
            {
                foreach($careplanObj['codes'] as $entry) 
                {
                    $question = Question::where('code', $entry['code'])
                                    ->where('agency_id', $agency_id)
                                    ->first();

                    // We don't even have the task in the first place
                    if(empty($question)){
                        continue;
                    }

                    $careplan = Careplan::where('uuid', $careplanObj['id'])
                                    ->where('episode_id', $careplanObj['episode_id'])
                                    ->where('patient_id', $patient_id)
                                    ->where('agency_id', $agency_id)
                                    ->where('question_id', $question->id)
                                    ->first();

                    if(empty($careplan)){
                        $careplan = new Careplan;
                        $careplan->uuid = $careplanObj['id'];
                        $careplan->agency_id = $agency_id;
                        $careplan->patient_id = $patient_id;
                        $careplan->episode_id = $careplanObj['episode_id'] ?? '';
                        $careplan->question_id = $question->id;
                        
                    }
                    
                    $careplan->discipline = $entry['discipline'] ?? '';
                    $careplan->status = ($careplanObj['active']) ? true : false;
                    $careplan->save();
                    
                    $has_tasks = true;
                }
            }

            array_push($this->processed['Careplans'], $careplanObj['id']);

             // if careplan has tasks...push them to the visit
             if($has_tasks)
             {
                // Try to load Question Set...if not previously loaded on visit.
                $visits = Visit::where('episode_id', $careplanObj['episode_id'])
                    ->where('patient_id', $patient_id)
                    ->where('agency_id', $agency_id)
                    ->where('visit_start','>', now())
                    ->get();
                
                // reload careplan for future visits
                foreach($visits as $visit) {
                    $this->loadQuestionSet($visit);
                }
             }
        }
    }

    public function processTasks($item) 
    {
       $tasks =  $item['TaskCode'] ?? [];
       
       foreach($tasks as $taskObj)
       {
            $taskcode = $taskObj['taskcode'] ?? null;

            // check if exist in Questions
            if(empty($taskcode)){
                return;
            }

            $this->logPath('tasks', $taskcode['id'], $taskObj);

            $question = Question::where('profile_id',$this->profile->id)->where('uuid',$taskcode['id'])->first();

            if (empty($question)) 
            {
                // Check if question has an existing hash
                $hash = md5($taskcode['name']);
                $sound = Question::where('hash', $hash)->where('has_sound', true)->first();
                $filename = (!empty($sound)) ? $sound->filename : '';
                $has_sound = (!empty($sound)) ? 1 : 0;

                $question = new Question;
                $question->uuid = $taskcode['id'];
                $question->code = $taskcode['code'];
                $question->name = $taskcode['name'];
                $question->question = $taskcode['name'];
                $question->agency_id = $this->setOrCreateAgency($taskcode['agency_id']);
                $question->profile_id = $this->profile->id;
                $question->type = $taskcode['visit_type'];
                $question->choices = json_encode(["1"=> "yes", "2" => "no", "3" => "refused"]);
                $question->hash = $hash;
                $question->filename = $filename;
                $question->has_sound = $has_sound;
                $question->save();
            }

            array_push($this->processed['Tasks'], $taskcode['code']);
       }
    }

    public function loadQuestionSet($visit)
    {
        $visit_questions = Careplan::where('patient_id', $visit->patient_id)
                                ->where('episode_id', $visit->episode_id)
                                ->where('status',true)
                                ->get();

        $counter = 1;
        $loaded_questions = [];

        foreach($visit_questions as $question_entry)
        {
            if(in_array($question_entry->question_id, $loaded_questions)){
                continue;
            }

            $question_set = QuestionSet::where('visit_id', $visit->id)->where('question_id', $question_entry->question_id)->first();

            if(empty($question_set)) {
                $question_set = new QuestionSet;
                $question_set->visit_id = $visit->id;
                $question_set->question_id = $question_entry->question_id;
                $question_set->question_type = 'MCQ';
                $question_set->question_no = $counter;
                $question_set->save();
            }
            
            array_push($loaded_questions, $question_entry->question_id);

            $counter++;
        }
    }

    public function retrieve($agency, $uuid =null)
    {
        $visits = Visit::with('questionset.question','patient','user','agency','profile')
                    ->where('agency_id', $agency->id);


        if(!empty($uuid)) {
            $visits = $visits->where('uuid', $uuid);
        }else{
            $visits = $visits->whereBetween('visit_start', [Carbon::yesterday(), date("Y-m-d 23:59:59")]);
        }

        $visits = $visits->get();

        $transformed = [];
        $calls = [];

        foreach($visits as $visit)
        {
            // Clocked In and Out?
            if(!empty($visit->clock_out))
            {
                $statusVal = 2;  
                
                array_push($calls, [
                    "VisitID" => $visit->uuid,
                    "call_type_name" => "Start",
                    "Phone" => $visit->patient->phone
                ]);

                array_push($calls, [
                    "VisitID" => $visit->uuid,
                    "call_type_name" => "End",
                    "Phone" => $visit->patient->phone
                ]);

            }elseif(!empty($visit->clock_in))
            {
                // Clocked In, Not out yet
                $statusVal = 1;

                array_push($calls, [
                    "VisitID" => $visit->uuid,
                    "CallTypeName" => "Start",
                    "Phone" => $visit->patient->phone
                ]);
            }else{
                // Not clocked in
                $statusVal = 0;
            };

            
            array_push($transformed, [
                "VisitId" => $visit->id,
                "ScheduleId" => $visit->uuid,
                "VisitStart" => empty($visit->clock_in) ? $visit->visit_start : $this->convertToPatientTimezone($visit->clock_in, $visit->user_id),
                "VisitEnd" => empty($visit->clock_out) ? $visit->visit_end : $this->convertToPatientTimezone($visit->clock_out, $visit->user_id),
                "MileageQty" => 0,
                "TravelEndDateTime" => "",
                "TravelTimeInMinutes" => 0,
                "OdometerStart" => 0,
                "OdometerEnd" => 0,
                "OdometerCalc" => 0,
                "PersonnelSys" => $visit->user->uuid,
                "EpisodeSys" => $visit->episode_id,
                "ResidentSys" => $visit->patient->uuid,
                "AgencyId" => $visit->agency->uuid,
                "VType" => $visit->visit_type,
                "Discipline" => "",
                "VisitStatus" => (int) $statusVal,
                "coordinates" => (object) [],
                "Mobile" => 0,
                "UTCStart" => $visit->clock_in,
                "UTCEnd" => $visit->clock_out,
                "Documentation" => $this->transformQuestionSet($visit->questionset, $visit->uuid),
                "Calls" => $calls
                // 'Profile'=> $visit->profile->auth_user,         
                // "VisitType" => $visit->visit_type,
                // "ScheduleType" => $visit->schedule_type,
                // "IsComplete" => ($visit->is_complete) ? 'completed':'pending',
                // "CreatedAt" => $visit->created_at,
                // "UpdatedAt" => $visit->updated_at,
                // 'QuestionSet' => $this->transformQuestionSet($visit->questionset, $visit->uuid, )
            ]);
        }
        return ["Visits" => $transformed];
    }

    public function transformQuestionSet($questionset, $schedule_id)
    {
        $data = [];
        $answers = [1 => 'yes', 2 => 'no'];

        // Add schedule documentation
        array_push($data, [
            "VisitID" => $schedule_id,
            "DocumentID" => $schedule_id,
            "Value" => "",
            "ValueLength" => 1,
            "Type" => "Schedule",
            "Reason" => null
        ]);

        // Add tasks documentation
        foreach($questionset as $entry)
        {
            array_push($data, [
                "VisitID" => $schedule_id,
                "DocumentID" => $entry->question->code ?? '',
                "Value" => base64_encode((string) $entry->selected_key),
                "ValueLength" => 1,
                "Type" => "Task",
                "Reason" => $entry->reason
            ]);

            /*array_push($data, [
                "id" => $entry->id,
                "schedule_id" => $schedule_id,
                "code" => $entry->question->uuid,
                "question" => $entry->question->name,
                "question_type" => $entry->question_type,
                "question_no" => (int) $entry->question_no,
                "selected_key" => (int) $entry->selected_key,
                "selected_answer" => $answers[$entry->selected_key] ?? 'Unknown',
                "answered_date" => $entry->answered_date,
                "created_at" => $entry->created_at,
                "updated_at" => $entry->updated_at,
            ]);
            */
        }

        return $data;
    }

    public function logPath($type, $uuid, $object)
    {
        try{
            $telelog = new Telelog;
            $telelog->type = $type;
            $telelog->uuid = $uuid;
            $telelog->payload = json_encode($object);
            $telelog->save();
        }catch(\Exception $e){
            Log::error("Error Logging to log table: ".$e->getMessage());
        }
    }

    public function convertToPatientTimezone($datestr, $user_id)
    {
        // clinician timezone
        $user = User::find($user_id);
        $timezone = $user->timezone;
        $format = "Y-m-d H:i:s";
    
        $timezone = (int) $timezone;
        $offset= abs($timezone) * 60 * 60;
    
        $datestr = empty($datestr) ? time() : strtotime($datestr);
    
        $timestamp = ($timezone < 0) ? $datestr - $offset : $datestr + $offset;
        $final_date = gmdate($format, $timestamp);
        
        return $final_date;
    }
}
