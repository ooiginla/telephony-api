<?php

namespace App\DataTransferObjects;

use App\Http\Requests\UpsertQuestionRequest;
use App\Models\Agency;

class QuestionData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $question,
        public readonly string $type,
        public readonly array $choices,
        public readonly Agency $agency 
    )
    {}

    public static function fromRequest(UpsertQuestionRequest $request):self
    {
        return new self(
            $request->question,
            $request->type,
            $request->choices,
            $request->getAgency()
        );
    }
}
