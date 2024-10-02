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

Route::group(['prefix' => 'admin'], function () {
    Route::get('test',function (){
        return response()->json([
            'success' => true,
            'message' => 'Hello World'
        ]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('exams', ExamController::class)->middleware('auth:sanctum'); // Route này yêu cầu xác thực
    Route::get('questions', [QuestionController::class, 'index']);
    Route::post('questions', [QuestionController::class, 'store']);
    Route::get('subjects', [SubjectController::class, 'index']); // Route này không yêu cầu xác thực
    Route::post('/sections', [ExamController::class, 'createSection'])->middleware('auth:sanctum');
    Route::get('/sections', [\App\Http\Controllers\Admin\SectionController::class, 'index']);
    Route::get('list-questions-by-exam-and-section/{exam_id}/{section_id}', [QuestionController::class, 'listQuestionByExamAndSection'])->middleware('auth:sanctum');
    Route::get('/sections/{id}', [\App\Http\Controllers\Admin\SectionController::class, 'edit'])->middleware('auth:sanctum');
    Route::put('/sections/{id}', [\App\Http\Controllers\Admin\SectionController::class, 'update'])->middleware('auth:sanctum');
});


//client routes
Route::group(['prefix' => 'v1'], function () {
    Route::get('/exams', [ExamControllerClient::class, 'index']);
    Route::get('/exam', [ExamControllerClient::class, 'search']);
    Route::get('list-sections', [ExamController::class, 'listSections']);
    Route::get('list-questions/{exam_id}/{section_id}', [QuestionControllerClient::class, 'listQuestions']);
    Route::get('subjects', function () {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Subject::all()
        ]);
    });
    Route::post('save-exam', [ExamControllerClient::class, 'submitQuestionsByExamAndSections']);
    Route::get('show-user-answer', [ExamControllerClient::class, 'showUserAnswers']);

});
Route::post('v1/save-user-info', [ExamControllerClient::class, 'saveUserInfo']);



















Route::get('refresh', function () {
    //call artisan command
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    return "Cache is cleared";
});


