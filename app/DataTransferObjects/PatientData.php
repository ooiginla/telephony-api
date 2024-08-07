<?php

namespace App\DataTransferObjects;

use App\Http\Requests\UpsertPatientRequest;
use App\Models\Agency;

class PatientData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $phone_number,
        public readonly Agency $agency
    )
    {}

    public static function fromRequest(UpsertPatientRequest $request):self
    {
        return new self(
            $request->phone_number,
            $request->getAgency()
        );
    }
}
