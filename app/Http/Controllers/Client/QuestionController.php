<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Exam;
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

    public function listQuestions($exam_id, $section_id)
    {
        $section = Section::with([
            'subjects.questions' => function ($query) use ($exam_id) {
                $query->where('questions.exam_id', $exam_id);
            },
            'subjects.questions.options'
        ])->find($section_id);

        $questions = [];
        $totalQuestions = 0; // Biến lưu tổng số câu hỏi

        if ($section) {
            foreach ($section->subjects as $subject) {
                // Nhóm các câu hỏi lại dựa trên content_question_group
                $groupedQuestions = [];
                foreach ($subject->questions as $question) {
                    if ($question->is_group) {
                        // Lấy tất cả các câu hỏi con có cùng content_question_group
                        if (!isset($groupedQuestions[$question->content_question_group])) {
                            $groupedQuestions[$question->content_question_group] = [];
                        }
                        $groupedQuestions[$question->content_question_group][] = $question;
                    } else {
                        $totalQuestions++;
                        $questions[] = $this->formatQuestion($question, $subject->name);
                    }
                }

                // Xử lý các câu hỏi nhóm
                foreach ($groupedQuestions as $contentQuestionGroup => $groupQuestions) {
                    $totalQuestions += count($groupQuestions); // Đếm số câu hỏi trong nhóm
                    $questions[] = $this->formatGroupQuestions($contentQuestionGroup, $groupQuestions, $subject->name);
                }
            }
        }

        return response()->json([
            "success" => true,
            "section" => $section ? $section->name : null,
            "time" => $section ? $section->timing : null,
            "total_questions" => $totalQuestions, // Trả về tổng số câu hỏi đã đếm
            "questions" => $questions
        ]);
    }

    private function formatGroupQuestions($contentQuestionGroup, $groupQuestions, $subjectName)
    {
        // Lấy các giá trị label của các câu hỏi con và nối lại thành 1 chuỗi
        $labels = collect($groupQuestions)->pluck('label')->implode(' - ');

        // Định dạng từng câu hỏi con
        $formattedGroupQuestions = collect($groupQuestions)->map(function ($question) use ($subjectName) {
            return $this->formatQuestion($question, $subjectName); // Định dạng từng sub-question
        })->toArray();

        return [
            'subject' => $subjectName,
            'content_question_group' => $contentQuestionGroup, // Hiển thị câu hỏi chính của nhóm
            'group_questions' => $formattedGroupQuestions, // Hiển thị các câu hỏi con trong group_questions
            'label' => $labels // Nối các giá trị label của câu hỏi con lại
        ];
    }

    private function formatQuestion($question, $subjectName)
    {
        return [
            'subject' => $subjectName,
            'question_id' => $question->id,
            'question' => $question->name,
            'type' => $question->type,
            'options' => $question->type === 'single' ? $this->formatOptions($question->options) : [],
            'correct_answer' => $question->type === 'input' ? $question->correct_answer : null,
            'ordering' => $question->ordering,
            'label' => $question->label
        ];
    }

    private function formatOptions($options)
    {
        return $options->map(function ($option) {
            return [
                'option_id' => $option->id,
                'option_text' => $option->option_text,
                'is_correct' => $option->is_correct
            ];
        })->toArray();
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



}
