<?php

use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\AssessorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [AssessorController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('assessments', [AssessmentController::class, 'index']);
    Route::get('assessment/questions/{student_id}', [AssessmentController::class, 'assessmentQuestions']);
    Route::get('assessment/{student_id}/detail', [AssessmentController::class, 'assessmentDetail']);
    Route::get('assessment/{student_id}/detail/question', [AssessmentController::class, 'assessmentDetailQuestion']);
    Route::get('profile', [AssessorController::class, 'profile']);
    Route::post('assessment', [AssessmentController::class, 'assessmentPostDetails']);
    Route::post('activity', [AssessmentController::class, 'activityLog']);
    Route::post('change/password', [AssessorController::class, 'changePassword']);
    Route::post('logout', [AssessorController::class, 'logout']);
});

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//
//    return $request->user();
//});
