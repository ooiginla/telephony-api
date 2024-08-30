<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
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
    }

    public function receive(Request $request) 
    {
        $payload = $request->all();
        dd($payload);
    }
}
