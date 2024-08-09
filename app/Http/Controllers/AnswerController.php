<?php

namespace App\Http\Controllers;

use App\Actions\UpsertAnswerAction;
use App\DataTransferObjects\AnswerData;
use App\Http\Requests\UpsertAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnswerController extends Controller
{

    public function __construct(
        public readonly UpsertAnswerAction $upsertAnswerAction
    )
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index():AnonymousResourceCollection
    {
        return AnswerResource::collection(Answer::with('agency')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertAnswerRequest $request)
    {
        return AnswerResource::make($this->upsert($request, new Answer()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Answer $question)
    {
        return AnswerResource::make($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpsertAnswerRequest $request, Answer $question)
    {
        $this->upsert($request, $question);
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Answer $question)
    {
        $question->delete();
        return ["Answer Deleted Successfully"];
    }

    private function upsert(
        UpsertAnswerRequest $request,
        Answer $question
    ):Answer
    {
        $questionData = AnswerData::fromRequest($request);
        return $this->upsertAnswerAction::execute($question, $questionData);
    } 
}
