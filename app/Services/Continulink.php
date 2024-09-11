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
            dd($e);
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
                $user->specialties = $this->getEmployeeSpecialties($empSpecialty);
                $user->profile_id = $this->profile->id;
                $user->agency_id = $this->setOrCreateAgency($employee['agency_id']);
                $user->save();   
            }

            array_push($this->processed['Employees'], $employee['external_id']);
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

        if($comp[6] == "PM"){
            $comp[3] = $comp[3] + 12;
        }
        
        $timestamp = mktime($comp[3], $comp[4], $comp[5], $comp[0], $comp[1], $comp[2]);

        return date("Y-m-d H:i:s", $timestamp);
    }

    public function processVisit($item) 
    {
       $visits =  $item['ScheduleService'] ?? null;
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

            $visit->agency_id = $this->setOrCreateAgency($schedule['agency_id']);
            $visit->uuid = $schedule['id'] ?? '';
            $visit->patient_id = $this->setOrCreateModel(new Patient, $visit->agency_id, $schedule['external_id']);
            $visit->user_id = $this->setOrCreateModel(new User, $visit->agency_id, $schedule['employee_id']);
            $visit->visit_start = $this->convertVisitDate($schedule['start']) ?? null;
            $visit->visit_end = $this->convertVisitDate($schedule['end']) ?? null;
            $visit->visit_type = $schedule['type'] ?? '';
            $visit->schedule_type = $schedule['schedule_type'] ?? '';
            $visit->status = (boolean) $schedule['active'] ?? false;
            $visit->profile_id = $this->profile->id;
            $visit->save();

            array_push($this->processed['Schedules'], $schedule['id']); 
            
            $this->loadQuestionSet($visit);
       }
    }
    
    public function processCareplan($item) 
    {
        $careplans =  $item['CarePlan'] ?? null;

       foreach($careplans as $careplanObj)
       {
            $agency_id = $this->setOrCreateAgency($careplanObj['agency_id']);
            $patient_id = $this->setOrCreateModel(new Patient, $agency_id, $careplanObj['external_id']);
            
            if(isset($careplanObj['codes']) && !empty($careplanObj['codes'])) 
            {
                foreach($careplanObj['codes'] as $entry) 
                {
                    $question = Question::where('uuid', $entry['code'])->first();

                    $careplan = new Careplan;
                    $careplan->uuid = $careplanObj['id'];
                    $careplan->agency_id = $agency_id;
                    $careplan->patient_id = $patient_id;
                    $careplan->question_id = ($question) ? $question->id : null;
                    $careplan->save();    
                }
            }

            array_push($this->processed['Careplans'], $careplanObj['id']);
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
                $question->agency_id = $this->setOrCreateAgency($taskcode['agency_id']);
                $question->profile_id = $this->profile->id;
                $question->type = 'MCQ';
                $question->choices = json_encode(["1"=> "yes", "2" => "no"]);
                $question->hash = md5($taskcode['description']);
                $question->save();
            }

            array_push($this->processed['Tasks'], $taskcode['code']);
       }
    }

    public function loadQuestionSet($visit)
    {
        $todays_questions = Careplan::where('patient_id', $visit->patient_id)->whereDate('created_at', Carbon::today())->get();

        $counter = 1;
        $loaded_questions = [];

        foreach($todays_questions as $question_entry)
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

    public function retrieve($agency)
    {
        $visits = Visit::with('questionset.question','patient','user','agency','profile')
                    ->whereDate('created_at', Carbon::today())
                    ->where('agency_id', $agency->id)
                    ->get();

        $transformed = [];

        foreach($visits as $visit)
        {
            array_push($transformed, [
                'visit_id' => $visit->id,
                'schedule_id' => $visit->uuid,
                'agency_id' => $visit->agency->uuid,
                'client_id' => $visit->patient->uuid,
                'employee_id' => $visit->user->uuid,
                'profile'=> $visit->profile->auth_user,
                "visit_start" => $visit->visit_start,
                "visit_end" => $visit->visit_end,
                "clock_in" => $visit->clock_in,
                "clock_out" => $visit->clock_out,
                "visit_type" => $visit->visit_type,
                "schedule_type" => $visit->schedule_type,
                "status" => ($visit->schedule_type) ? 'active':'inactive',
                "is_complete" => ($visit->is_complete) ? 'completed':'pending',
                "created_at" => $visit->created_at,
                "updated_at" => $visit->updated_at,
                'question_set' => $this->transformQuestionSet($visit->questionset, $visit->uuid)
            ]);
        }
        return ["Visits" => $transformed];
    }

    public function transformQuestionSet($questionset, $schedule_id)
    {
        $data = [];
        $answers = [1 => 'yes', 2 => 'no'];

        foreach($questionset as $entry)
        {
            array_push($data, [
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
        }

        return $data;
    }
}
