<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\QuestionController;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => 'auth.custom'], function () {
    Route::get('/exams', [ExamController::class, 'index']);
    Route::post('/exams', [ExamController::class, 'store']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::put('/exams/{id}', [ExamController::class, 'update']);
    Route::get('/exams/{id}', [ExamController::class, 'edit']);
    Route::delete('/exams/{id}', [ExamController::class, 'destroy']);


    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::get('/questions/{id}', [QuestionController::class, 'edit']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

    Route::get('subjects', function () {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Subject::all()
        ]);
    });
});

Route::get('/list-questions', [QuestionController::class, 'listQuestions']);


