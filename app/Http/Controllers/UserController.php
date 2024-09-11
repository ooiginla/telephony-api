<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    
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
}