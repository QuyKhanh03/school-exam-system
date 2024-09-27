<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Section;
use http\Client;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    //list questions by exam and subject
    public function listQuestions($exam_id, $subject_id)
    {
        $questions = Question::where('exam_id', $exam_id)
            ->where('subject_id', $subject_id)
            ->get();

        if ($questions->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'data' => $questions
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No questions found for the given exam and subject'
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function getExamQuestionsBySection($examId, $sectionId)
    {
        // Lấy các sections của bài thi với `exam_id` và `section_id` cụ thể
        $section = Section::with(['subjects.questions.options'])
            ->where('exam_id', $examId)
            ->where('id', $sectionId)
            ->first(); // Chỉ lấy một section cụ thể

        if (!$section) {
            return response()->json([
                'status' => 'error',
                'message' => 'Section not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'section' => $section
        ]);
    }

}
