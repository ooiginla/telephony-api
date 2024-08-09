<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agency;
use App\Models\Visit;
use App\Models\Question;
use App\Models\QuestionSet;


class VisitController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'agency_id' => 'required',
            'patient_id' => 'required',
            'user_id' => 'required',
            'visit_date' => 'required',
            'questions' => 'required'
        ]);

        $agency_id = $request->input('agency_id');

        $agency = Agency::find('id', $agency_id);

        if (empty($agency)) {
             // throw a failure
             return response()->json([
                'status' => false,
                'message' => 'Agency not found',
                'data' => null
            ]);
        }

        if(! is_array($request->json('questions'))) {
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
        }

        $questions = $request->json('questions');
        $visits = $request->json('questions');


        $visitObjects = [];

        // Create a visit
        foreach($visits as $visit) {
            $visit_entry = new Visit;
            $visit_entry->agency_id = $agency->id;
            $visit_entry->patient_id = $visit['patient_id'] ?? null;
            $visit_entry->user_id = $visit['user_id'] ?? null;
            $visit_entry->visit_date = $visit['visit_date'] ?? null;
            $visit_entry->save();

            array_push($visitObjects, $visit_entry);
        }
        
        // Fetch Agency Questions List
        $agencyQuestionsHash = Question::where('agency_id','')->pluck('id','hash');
        $currentQuestionSet = [];

        // Create a Question Set
        foreach($questions as $question_item) 
        {
          
            $hash = md5($question->question);

            $question_id = $agencyQuestionsHash[$hash] ?? null;

            // Check if question does not exist in agency set
            if(empty($question_id)) 
            {
                // insert in agencies questions list
                $question_model = new Question;
                $question_model->question = $question_item->question;
                $question_model->type= $question_item->type;
                $question_model->choices = json_encode($question_item->choices);
                $question_model->agency_id = $agency->id;
                $question_model->save();

                // push new question hash into list of agency questions
                $agencyQuestionsHash[$hash] = $question_model->id;

                // set question id
                $question_id = $question_model->id;
            }

            array_push($currentQuestionSet, [
                'question_id' => $question_id,
                'question_type' => $question_item->type,
                'question_no' => $question_item->no,
            ]);
        }

        $this->applyQuestionSetToVisits($currentQuestionSet, $visitObjects);

        return response()->json([
            'status' => true,
            'message' => 'Visits successfully saved',
            'visits' => $visitObjects
        ]);
    }

    public function applyQuestionSetToVisits($currentQuestionSet, $visitObjects)
    {
        // Loop each visits
        foreach($visitObjects as $v) 
        {
            $payload = array_map(function($k) use ($v) { 
                return $k['visit_id'] = $v->id;
            }, $currentQuestionSet);

            QuestionSet::insert($payload);
            $payload = null;
        }
    }
}