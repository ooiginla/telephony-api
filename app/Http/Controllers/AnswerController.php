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
         foreach($visit->questions as $question){
            
            dd($question);
         }
        

        //dd($visits->questions());

        // foreach ($visits as $questionSet) {
        //     //if($questionSet->visit_id === )
        //     $questionSet->update([
        //         'question_order' => $request->input('question_order'),
        //         'selected_key' => $request->input('selected_key'),
        //     ]);
        // }

        // return response()->json([
        //     "status" => true,
        //     "message" => "question set successfully created",
        //     "data" => QuestionSetResource::collection($visits)
        // ]); 
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
