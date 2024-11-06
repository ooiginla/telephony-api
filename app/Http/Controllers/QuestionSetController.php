<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\QuestionSet;

class QuestionSetController extends Controller
{
    public function getVisitTasks(Request $request)
    {
        $visit_id = $request->input('visit_id');

        $tasks = QuestionSet::with('question:id,name,has_sound')
                    ->where('visit_id', $visit_id)
                    ->select('question_sets.id as task_id','question_sets.question_id','question_sets.selected_key')
                    // ->whereNull('selected_key')
                    ->get();

        return response([
            'data' => $tasks
        ]);
    }
    
}
