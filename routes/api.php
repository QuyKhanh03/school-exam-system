<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SubjectController;

use App\Http\Controllers\Client\ExamController as ExamControllerClient;
use App\Http\Controllers\Client\QuestionController as QuestionControllerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/admin/register', [AuthController::class, 'register']);
Route::post('/admin/login', [AuthController::class, 'login']);

Route::group(['prefix' => 'admin'],function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('exams', ExamController::class);
    Route::resource('questions', QuestionController::class);
    Route::get('subjects', [SubjectController::class, 'index']);
})->middleware('auth:sanctum');

//client routes
Route::group(['prefix' => 'v1'], function () {
    Route::get('/exams', [ExamControllerClient::class, 'index']);
    Route::get('/exam', [ExamControllerClient::class, 'search']);
    Route::get('/exam-start/{exam_id}/subject/{subject_id}', [QuestionControllerClient::class, 'listQuestions']);


    Route::get('/list-questions', [QuestionController::class, 'listQuestions']);
    Route::get('subjects', function () {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Subject::all()
        ]);
    });
});





















Route::get('refresh', function () {
    //call artisan command
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    return "Cache is cleared";
});


