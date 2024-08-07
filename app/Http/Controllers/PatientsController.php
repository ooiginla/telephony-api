<?php

namespace App\Http\Controllers;

use App\Actions\UpsertPatientAction;
use App\DataTransferObjects\PatientData;
use App\Http\Requests\UpsertPatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientsController extends Controller
{

    public function __construct(
        public readonly UpsertPatientAction $upsertPatientAction
    )
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index():AnonymousResourceCollection
    {
        
        return PatientResource::collection(Patient::with('agency')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertPatientRequest $request)
    {
        return PatientResource::make($this->upsert($request, new Patient()));
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return PatientResource::make($patient);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpsertPatientRequest $request, Patient $patient)
    {
        $this->upsert($request, $patient);
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();
        return ["Patient Deleted Successfully"];
    }

    private function upsert(
        UpsertPatientRequest $request,
        Patient $patient
    ):Patient
    {
        $patientData = PatientData::fromRequest($request);
        return $this->upsertPatientAction::execute($patient, $patientData);
    } 
}
