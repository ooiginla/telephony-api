<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Http\Resources\QuestionSetResource;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionSetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():AnonymousResourceCollection
    {
        return QuestionSetResource::collection(QuestionSet::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'visit_id' => 'required|integer|exists:visits,id',
            'question_id' => 'required|integer|exists:questions,id',
            'question_type' => 'required|string',
            'question_order' => 'required',
            'selected_key' => 'required',
            'selected_answer' => 'required',
            'answered_date' => 'nullable|sometimes|date',
            'profile_id' => 'required|integer|exists:profiles,id'
        ]);

        $questionSet = QuestionSet::create($validatedData);

        return response()->json([
            "status" => true,
            "message" => "Question set Created successfully",
            "data" => QuestionSetResource::make($questionSet)
        ]);




    }

    /**
     * Display the specified resource.
     */
    public function show(QuestionSet $question_set)
    {
        return response()->json([
            "status" => true,
            "message" => "Question Set fetched successfully",
            "data" => QuestionSetResource::make($question_set)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionSet $question_set)
    {
        $validatedData = $request->validate([
            'visit_id' => 'required|integer|exists:visits,id',
            'question_id' => 'required|integer|exists:questions,id',
            'question_type' => 'required|string',
            'question_order' => 'required',
            'selected_key' => 'required',
            'selected_answer' => 'required',
            'answered_date' => 'nullable|sometimes|date',
            'profile_id' => 'required|integer|exists:profiles,id'
        ]);

        $question_set->update($validatedData);

        return response()->json([
            "status" => true,
            "message" => "Question set Updated successfully",
            "data" => QuestionSetResource::make($question_set)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionSet $question_set)
    {
        $question_set->delete();

        return response()->json([
            "status" => true,
            "message" => "question set deleted successfully",
            "data" => null
        ]);
    }
}
