<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionSetResource;
use App\Models\QuestionSet;
use App\Models\Visit;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Visit $visit)
    {   
        $request->validate([
            'answers.*.no' => 'required',
            'answers.*.answer' => 'required'
        ]);

        $answers = $request->input('answers');

        foreach ($answers as $answer) 
        {
           
           $question_set = QuestionSet::where('visit_id',$visit->id)
                ->where("order_no", $answer['order_no'])->first();

            if(!empty($question_set)){
                $choices = $question_set->question->choices;

                $question_set->update([
                    'selected_key' => $answer['answer'],
                    'selected_answer' => $choice[$answer['answer']]
                ]);
            }
        }

        return response()->json([
            "status" => true,
            "message" => "answered set successfully updated",
            "data" => QuestionSetResource::make($questionSet)
        ]); 
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
