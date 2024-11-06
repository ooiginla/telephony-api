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

    public function getVisitTasks(Request $request)
    {
        $visit_id = $request->input('visit_id');

        
    }

    public function getPendingSound(Request $request)
    {
       $questions =  Question::select('id','name','question')->where('has_sound', false)->get();

       return response()->json([
            'data' => $questions
       ]);
    }

    public function postSoundGenerated(Request $request)
    {
       $task_id = $request->input('id');
       $name = $request->input("name");

       $status = Question::where('id', $task_id)->update(['has_sound' => true]);

       if($status){
            $msg = "Sound successfully pushed for id: ". $task_id . " - ". $name;
       }else{
        $msg = "Error updating sound updated event for question id: ". $task_id;
       }

       return response()->json([
            "message" => $msg
       ]);
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
