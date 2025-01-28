<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\ContinulinkController;
use App\Http\Controllers\UserController;
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
    
    Route::put('/visit/{visit}/answer', [AnswerController::class, 'store']);

    // Get Customer API by Phone number
    Route::any('/fetch/patient/byphone', [PatientsController::class, 'getPatientByPhone']);
    Route::any('/fetch/caregiver/bycode', [UserController::class, 'getCaregiverByAccessCode']);
    Route::any('/fetch/visit/tasks', [QuestionSetController::class, 'getVisitTasks']);
    Route::any('/post/visit/tasks', [QuestionSetController::class, 'postVisitTasks']);
    Route::any('/post/visit/tasks/refusal', [QuestionSetController::class, 'postRefusalReason']);

    
    Route::any('/trigger/visit/start', [VisitController::class, 'startVisit']);
    Route::any('/trigger/visit/end', [VisitController::class, 'endVisit']);
    Route::any('/check/employee/exists', [UserController::class, 'checkEmployeeExist']);
});

Route::any("/generate-sound",[QuestionsController::class,'getPendingSound']);
Route::any("/update-sound",[QuestionsController::class,'postSoundGenerated']);

Route::any('/ExecuteDNDSend', [ContinulinkController::class, 'send']);
Route::any('/ExecuteDNDReceive', [ContinulinkController::class, 'receive']);
Route::any('/ExecuteDNDAcknowledge', [ContinulinkController::class, 'acknowledge']);