<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agency;
use App\Models\User;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\Question;
use App\Models\QuestionSet;


class VisitController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'agency_id' => 'required',
            'visits' => 'required|array',
            'questions' => 'required|array',
            'visits.*.visit_date' => 'required',
            'visits.*.clinician_id' => 'required',
            'visits.*.patient_id' => 'required',
        ]);

        $agency_id = $request->input('agency_id');
        $profile = $request->input('profile');

        $agency = Agency::where('profile_id',$profile->id)->where('uuid', $agency_id)->first();

        // Agency doesn't exist?
        if (empty($agency)) {
            // Create Agency
            $agency = Agency::create([
                'name'=> $request->input('agency_name',''),
                'uuid'=> $request->input('agency_id'),
                'profile_id'=> $profile->id
            ]);
        }

       /* if(! is_array($request->json('questions'))) {
             // throw a failure
             return response()->json([
                'status' => false,
                'message' => 'Expects questions parameter in the request payload must be a collection',
                'data' => null
            ]);
        }

        if(! is_array($request->json('visits'))) {
            // throw a failure
            return response()->json([
                'status' => false,
                'message' => 'Expects visits parameter in the request payload must be a collection',
                'data' => null
            ]);
        }*/

        $questions = $request->json('questions');
        $visits = $request->json('visits');
       
        $visitObjects = [];

        // Create a visit
        foreach($visits as $visit) {
            // check patient exist
            $patient = Patient::where('uuid',$visit['patient_id'])->first();

            if(empty($patient)){
                // create patient
                $patient = Patient::create([
                    'uuid' => $visit['patient_id'],
                    'agency_id' => $agency->id,
                    'profile_id' => $profile->id
                ]);
            }

            // check clinician exists
            $clinician = User::where('uuid', $visit['clinician_id'])->first();

            if(empty($clinician)){
                // create clinician
                $clinician = User::create([
                    'uuid' => $visit['clinician_id'],
                    'agency_id' => $agency->id,
                    'profile_id' => $profile->id
                ]);
            }

            $visit_entry = new Visit;
            $visit_entry->agency_id = $agency->id;
            $visit_entry->patient_id = $patient->id;
            $visit_entry->user_id = $clinician->id;
            $visit_entry->visit_date = $visit['visit_date'];
            $visit_entry->profile_id = $profile->id;
            $visit_entry->save();

            array_push($visitObjects, $visit_entry);
        }
        
        // Fetch Agency Questions List
        $agencyQuestionsHash = Question::where('agency_id',$agency->id)->pluck('id','hash');
        $currentQuestionSet = [];

        // Create a Question Set
        foreach($questions as $question_item) 
        {
            $hash = md5($question_item['question']);

            $question_id = $agencyQuestionsHash[$hash] ?? null;

            // Check if question does not exist in agency set
            if(empty($question_id)) 
            {
                // insert in agencies questions list
                $question_model = new Question;
                $question_model->question = $question_item['question'];
                $question_model->type = $question_item['type'];
                $question_model->choices = $question_item['choices'];
                $question_model->agency_id = $agency->id;
                $question_model->profile_id = $profile->id;
                $question_model->hash = $hash;
                $question_model->save();

                // push new question hash into list of agency questions
                $agencyQuestionsHash[$hash] = $question_model->id;

                // set question id
                $question_id = $question_model->id;
            }

            array_push($currentQuestionSet, [
                'question_id' => $question_id,
                'question_type' => $question_item['type'],
                'question_no' => $question_item['no'],
                'uuid' => $this->v4_UUID()
            ]);
        }

        $response_data = $this->applyQuestionSetToVisits($currentQuestionSet, $visitObjects, $profile);

        return response()->json([
            'status' => true,
            'message' => 'Visits successfully saved',
            'visits' => $response_data
        ]);
    }

    public function applyQuestionSetToVisits($currentQuestionSet, $visitObjects, $profile)
    {
        // Loop each visits
        $response_data = [];

        foreach($visitObjects as $v) 
        {
            $payload = array_map(function($k) use ($v) { 
                $k['visit_id'] = $v->id;
                return $k;
            }, $currentQuestionSet);
             
            QuestionSet::insert($payload);

            $payload = null;

            array_push($response_data,[
                'profile' => $profile->name,
                'visit_id' => $v->id,
                'visit_date' => $v->visit_date,
                'is_completed' => (bool) $v->is_completed,
                'created_at' => $v->created_at,
                'updated_at' => $v->updated_at
            ]);
        }

        return $response_data;
    }

    public function v4_UUID() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for the time_low
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for the time_mid
            mt_rand(0, 0xffff),
            // 16 bits for the time_hi,
            mt_rand(0, 0x0fff) | 0x4000,

            // 8 bits and 16 bits for the clk_seq_hi_res,
            // 8 bits for the clk_seq_low,
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for the node
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}