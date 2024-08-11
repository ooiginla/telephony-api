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
         
        //This is getting all the questions that is related to this visit Id
        foreach ($visit->questions as $question) {
            $data = json_decode($question->choices, true);
            if ($data && isset($data['answer'])) {
                $answer = $data['answer'];
            } else {
                $answer = null; // or some default value
            }
        
            $questionSet = QuestionSet::where('question_id', $question->id)->first();
            if ($questionSet) {
                $questionSet->update([
                    'question_order' => $request->input('question_order'),
                    'selected_key' => $request->input('selected_key'),
                    'selected_answer' => $answer,
                ]);
                $questionSet->save();
                $questionSet->refresh();
            }
        }
        return response()->json([
            "status" => true,
            "message" => "question set successfully created",
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
