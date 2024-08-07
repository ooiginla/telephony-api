<?php

namespace App\Http\Controllers;

use App\Actions\UpsertQuestionAction;
use App\DataTransferObjects\QuestionData;
use App\Http\Requests\UpsertQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionsController extends Controller
{

    public function __construct(
        public readonly UpsertQuestionAction $upsertQuestionAction
    )
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index():AnonymousResourceCollection
    {
        return QuestionResource::collection(Question::with('agency')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertQuestionRequest $request)
    {
        return QuestionResource::make($this->upsert($request, new Question()));

    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        return QuestionResource::make($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpsertQuestionRequest $request, Question $question)
    {
        $this->upsert($request, $question);
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return ["Question Deleted Successfully"];
    }

    private function upsert(
        UpsertQuestionRequest $request,
        Question $question
    ):Question
    {
        $questionData = QuestionData::fromRequest($request);
        return $this->upsertQuestionAction::execute($question, $questionData);
    } 
}
