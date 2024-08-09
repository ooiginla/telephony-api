<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():AnonymousResourceCollection
    {
        return AgencyResource::collection(Agency::select('name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'agency_id' => 'required'
        ]);

        $agency = Agency::create([
            'name'=> $request->input('name'),
            'uuid'=> $request->input('agency_id'),
            'profile_id'=> $request->input('profile')->id,
        ]);

        return response()->json([
            "status" => true,
            "message" => "agency successfully created",
            "data" => AgencyResource::make($agency)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agency $agency)
    {
        return AgencyResource::make($agency);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AgencyRequest $request, Agency $agency)
    {
        $agency->update($request->validated());
        return AgencyResource::make($agency);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agency $agency)
    {
        $agency->delete();
        return ['Agency deleted successfully'];
    }
}
