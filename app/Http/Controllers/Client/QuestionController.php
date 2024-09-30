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
        // Lấy section cùng với các subjects và questions
        $section = Section::with([
            'subjects.questions.options'
        ])->find($section_id);

        $questions = [];
        $totalQuestions = 0;

        if ($section) {
            foreach ($section->subjects as $subject) {
                $sortedQuestions = $subject->questions->sortBy('ordering');

                $groupedQuestions = [];
                foreach ($sortedQuestions as $question) {
                    if ($question->is_group) {
                        // Lưu trữ các câu hỏi nhóm vào biến groupedQuestions
                        if (!isset($groupedQuestions[$question->content_question_group])) {
                            $groupedQuestions[$question->content_question_group] = [];
                        }
                        $groupedQuestions[$question->content_question_group][] = $question;
                    } else {
                        $totalQuestions++;
                        $questions[] = $this->formatQuestion($question, $subject->name);
                    }
                }

                foreach ($groupedQuestions as $contentQuestionGroup => $groupQuestions) {
                    $totalQuestions += count($groupQuestions);
                    $questions[] = $this->formatGroupQuestions($contentQuestionGroup, $groupQuestions, $subject->name);
                }
            }
        }

        $sortedQuestions = collect($questions)->sortBy('ordering')->values()->all();

        return response()->json([
            "success" => true,
            "section" => $section ? $section->name : null,
            "time" => $section ? $section->timing : null,
            "total_questions" => $totalQuestions,
            "questions" => $sortedQuestions
        ]);
    }

    private function formatGroupQuestions($contentQuestionGroup, $groupQuestions, $subjectName)
    {
        $labels = collect($groupQuestions)->pluck('label')->implode(' - ');

        $formattedGroupQuestions = collect($groupQuestions)->map(function ($question) use ($subjectName) {
            return $this->formatQuestion($question, $subjectName);
        })->toArray();

        $ordering = $groupQuestions[0]->ordering ?? 0;

        return [
            'subject' => $subjectName,
            'content_question_group' => $contentQuestionGroup,
            'label' => $labels,
            'type' => 'group',
            'ordering' => $ordering, // Đảm bảo sắp xếp câu hỏi nhóm
            'group_questions' => $formattedGroupQuestions,
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
