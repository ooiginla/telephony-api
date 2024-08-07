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
    public function store(AgencyRequest $request)
    {
       
        $agency = Agency::create($request->validated());
        return AgencyResource::make($agency);
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
