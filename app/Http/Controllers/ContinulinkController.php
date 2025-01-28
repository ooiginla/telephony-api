<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use App\Services\Continulink;

class ContinulinkController extends Controller
{

    public function send(Request $request, Continulink $continulink) 
    {
        $payload = $request->all();
        $response = $continulink->process($payload);

        return response()->json($response);
    }

    public function receive(Request $request, Continulink $continulink) 
    {
        $request->validate([
            'agency_id' => 'required'
        ]);

        $agency_id = $request->input('agency_id');

        $agency = Agency::where('uuid', $agency_id)->first();

        $uuid = $request->input('uuid');

        if(empty($agency)){
            return response()->json(['status'=>false,'message'=>'agency not found', 'data' => []]);
        }

        $response = $continulink->retrieve($agency, $uuid);

        return response()->json($response);
    }

    public function acknowledge(Request $request, Continulink $continulink) 
    {
        $request->validate([
            'Visits' => 'required'
        ]);

        $visits = $request->input('Visits');

        Visit::whereIn('uuid',$visits)->update(['is_acknowledged' => true]);

        return response()->json(['status'=>true, 'message'=>'visits successfully acknowledged', 'data' => []]);
    }
}
