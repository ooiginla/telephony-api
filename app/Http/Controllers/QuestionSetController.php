<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\QuestionSet;

class QuestionSetController extends Controller
{
    public function getVisitTasks(Request $request)
    {
        $visit_id = $request->input('visit_id');

        $tasks = QuestionSet::with('question:id,name,filename,has_sound')
                    ->where('visit_id', $visit_id)
                    ->select('question_sets.id as task_id','question_sets.question_id','question_sets.selected_key')
                    // ->whereNull('selected_key')
                    ->get();

        return response([
            'data' => $tasks
        ]);
    }

    public function postVisitTasks(Request $request)
    {
        $options = [
            1 => "yes",
            2 => "no",
            3 => "refused"
        ];


        $set_id = $request->input('set_id');
        $option = $request->input('option');

        $task = QuestionSet::find($set_id);

        if(empty($task)){
            return  response(['data' => []]);
        }

        $task->selected_key = $option;
        $task->selected_answer = $options[$option];
        $task->answered_date = date("Y-m-d H:i:s");
        $task->save();

        return response([
                'status' => true, 
                'data' => ['message' => "option for task id:".$set_id ." saved"]
        ]);
    }

    public function postRefusalReason(Request $request)
    {
        $set_id = $request->input('set_id');
        $refusal_reason_code = $request->input('option');

        $task = QuestionSet::find($set_id);

        if(empty($task)){
            return  response(['data' => []]);
        }

        $task->reason = $refusal_reason_code;
        $task->save();

        return response([
                'status' => true, 
                'data' => ['message' => "Refusal reason for task id:".$set_id ." saved"]
        ]);
    }

    


    
}
