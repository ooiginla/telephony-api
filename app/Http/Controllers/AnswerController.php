<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionSetResource;
use App\Models\QuestionSet;
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
    public function store(Request $request, QuestionSet $questionSet)
    {
         
        //$questionsSet = QuestionSet::where('id', $questionSet)->first();
        QuestionSet::updateOrCreate(
            [ 'id' => $questionSet],
            [
            'question_order'=> $request->input('question_order'),
            'selected_key'=> $request->input('selected_key'),
            ]
            );
        // if(empty($questionsSet)){
        //     $$questionsSet->create([
        //     'question_order'=> $request->input('question_order'),
        //     'selected_key'=> $request->input('selected_key'),
        //    ]);
        // }

        // $questionsSet->update([
        //     'question_order'=> $request->input('question_order'),
        //     'selected_key'=> $request->input('selected_key'),
        // ]);

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
