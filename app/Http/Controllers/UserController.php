<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    // Employee - Caregiver - Clinician : same
    public function getCaregiverByAccessCode(Request $request)
    {
        $request->validate([
            'pin' => 'required',
        ]);

        $profile = $request->input('profile');
        $pin = $request->input('pin');


        $caregiver = User::with('agency')
                        ->where('pin', $pin)
                        ->where('profile_id', $profile->id)
                        ->first();

        return response()->json([
            "data" => $caregiver
        ]);
    }

    public function checkEmployeeExist(Request $request)
    {
        $request->validate([
            'employee_code' => 'required',
        ]);

        $profile = $request->input('profile');
        $employee_id = $request->input('employee_code');

        $employee = User::where('uuid', $employee_id)
                        ->where('profile_id', $profile->id)
                        ->first();

        return response()->json([
            "data" => $employee
        ]);
    }
}