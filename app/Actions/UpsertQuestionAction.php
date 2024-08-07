<?php

namespace App\Actions;

use App\DataTransferObjects\PatientData;
use App\DataTransferObjects\QuestionData;
use App\Models\Patient;
use App\Models\Question;

class UpsertQuestionAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function execute(Question $question, QuestionData $questionData):Question
    {
        $question->question = $questionData->question;
        $question->type = $questionData->type;
        $question->choices = $questionData->choices;
        $question->agency_id = $questionData->agency->id;
        $question->save();

        return $question;
    }
}
