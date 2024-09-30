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

    //list questions by exam and subject
//    public function listQuestions($exam_id, $section_id)
//    {
//        $section = Section::with(['subjects.questions.options', 'subjects.questions.subQuestions.options'])->find($section_id);
//        $questions = [];
//        if ($section) {
//            foreach ($section->subjects as $subject) {
//                foreach ($subject->questions as $question) {
//                    if ($question->type === 'single') {
//                        $questions[] = [
//                            'subject' => $subject->name,
//                            'question_id' => $question->id,
//                            'question' => $question->name,
//                            'type' => $question->type,
//                            'options' => $question->options ? $question->options->map(function ($option) {
//                                return [
//                                    'option_id' => $option->id,
//                                    'option_text' => $option->option_text,
//                                    'is_correct' => $option->is_correct,
//                                ];
//                            })->toArray() : [],
//                            'correct_answer' => null
//                        ];
//                    } elseif ($question->type === 'input') {
//                        $questions[] = [
//                            'subject' => $subject->name,
//                            'question_id' => $question->id,
//                            'question' => $question->name,
//                            'type' => $question->type,
//                            'options' => [], // Ẩn options
//                            'correct_answer' => $question->correct_answer
//                        ];
//                    } elseif ($question->type === 'group') {
//                        $groupQuestions = [];
//                        foreach ($question->subQuestions as $subQuestion) {
//                            if ($subQuestion->type === 'single') {
//                                $groupQuestions[] = [
//                                    'sub_question_id' => $subQuestion->id,
//                                    'sub_question_text' => $subQuestion->name,
//                                    'type' => $subQuestion->type,
//                                    'options' => $subQuestion->options ? $subQuestion->options->map(function ($option) {
//                                        return [
//                                            'option_id' => $option->id,
//                                            'option_text' => $option->option_text,
//                                            'is_correct' => $option->is_correct,
//                                        ];
//                                    })->toArray() : [],
//                                    'correct_answer' => null
//                                ];
//                            } elseif ($subQuestion->type === 'input') {
//                                $groupQuestions[] = [
//                                    'sub_question_id' => $subQuestion->id,
//                                    'sub_question_text' => $subQuestion->name,
//                                    'type' => $subQuestion->type,
//                                    'options' => [],
//                                    'correct_answer' => $subQuestion->correct_answer
//                                ];
//                            }
//                        }
//
//                        $questions[] = [
//                            'subject' => $subject->name,
//                            'question_id' => $question->id,
//                            'question' => $question->name,
//                            'type' => $question->type,
//                            'group_questions' => $groupQuestions,
//                            'correct_answer' => null,
//                            'options' => []
//                        ];
//                    }
//                }
//            }
//        }
//
//        return response()->json([
//            "success" => true,
//            "section" => $section ? $section->name : null,
//            "time" => $section ? $section->timing : null,
//            "questions" => $questions
//        ]);
//    }

    public function listQuestions($exam_id, $section_id)
    {
        $section = Section::with([
            'subjects.questions' => function ($query) use ($exam_id) {
                $query->where('questions.exam_id', $exam_id);
            },
            'subjects.questions.options',
            'subjects.questions.subQuestions.options'
        ])->find($section_id);

        $questions = [];
        $totalQuestions = 0; // Biến lưu tổng số câu hỏi

        if ($section) {
            foreach ($section->subjects as $subject) {
                foreach ($subject->questions as $question) {
                    // Nếu là câu hỏi group, chỉ đếm các sub-questions
                    if ($question->type === 'group') {
                        $totalQuestions += count($question->subQuestions); // Đếm số sub-questions
                        $questions[] = $this->formatQuestion($question, $subject->name); // Chỉ thêm câu hỏi group vào danh sách
                    } else {
                        // Nếu không phải câu hỏi group, đếm và thêm vào danh sách câu hỏi
                        $totalQuestions++;
                        $questions[] = $this->formatQuestion($question, $subject->name);
                    }
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

    private function formatQuestion($question, $subjectName)
    {
        if ($question->type === 'group') {
            $groupQuestions = $question->subQuestions->map(function ($subQuestion) {
                return $this->formatSubQuestion($subQuestion);
            })->toArray();

            return [
                'subject' => $subjectName,
                'question_id' => $question->id,
                'question' => $question->name,
                'type' => $question->type,
                'group_questions' => $groupQuestions, // Chỉ hiển thị sub-questions ở đây
                'ordering' => $question->ordering,
                'label' => $question->label,
                'correct_answer' => null,
                'options' => [] // Không hiển thị options cho câu hỏi group
            ];
        }

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

    private function formatSubQuestion($subQuestion)
    {
        return [
            'sub_question_id' => $subQuestion->id,
            'sub_question_text' => $subQuestion->name,
            'type' => $subQuestion->type,
            'options' => $subQuestion->type === 'single' ? $this->formatOptions($subQuestion->options) : [],
            'correct_answer' => $subQuestion->type === 'input' ? $subQuestion->correct_answer : null,
            'ordering' => $subQuestion->ordering,
            'label' => $subQuestion->label
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
