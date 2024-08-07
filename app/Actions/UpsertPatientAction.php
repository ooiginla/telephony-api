<?php

namespace App\Actions;

use App\DataTransferObjects\PatientData;
use App\Models\Patient;

class UpsertPatientAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function execute(Patient $patient, PatientData $patientData):Patient
    {
        $patient->phone_number = $patientData->phone_number;
        $patient->agency_id = $patientData->agency->id;
        $patient->save();

        return $patient;
    }
}
