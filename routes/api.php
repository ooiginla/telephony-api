<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\VisitController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
 

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(AuthMiddleware::class)->group(function(){

    Route::apiResource('agency', AgencyController::class);
    Route::apiResource('patient', PatientsController::class);
    Route::apiResource('question', QuestionsController::class);
    Route::apiResource('answers', AnswerController::class);
    Route::apiResource('visits', VisitController::class);

});

