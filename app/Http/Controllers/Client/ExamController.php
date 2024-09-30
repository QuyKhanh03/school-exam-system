<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\ExamSectionScore;
use App\Models\Question;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Exam::select('id', 'name', 'code')
            ->whereHas('questions'); // Kiểm tra nếu exam có ít nhất 1 câu hỏi

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $exams = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $exams
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

    public function search(Request $request)
    {
        $exam = Exam::where('code', $request->code)->first();
        if ($exam) {
            return response()->json([
                'success' => true,
                'data' => $exam
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Exam not found'
        ], 404);
    }


    public function submitQuestionsByExamAndSection(Request $request, $exam_id, $section_id)
    {
        $section = Section::with(['subjects.questions.options'])->find($section_id);

        $userAnswers = $request->input('answers');
        $totalQuestions = 0;
        $totalCorrect = 0;

        foreach ($section->subjects as $subject) {
            foreach ($subject->questions as $question) {
                $totalQuestions++;

                $userAnswer = collect($userAnswers)->firstWhere('question_id', $question->id);

                if ($question->type === 'input') {
                    if ($userAnswer && $question->correct_answer == $userAnswer['answer_text']) {
                        $totalCorrect++;
                    }
                } elseif ($question->type === 'single') {
                    $correctOption = $question->options->firstWhere('is_correct', 1);
                    if ($userAnswer && $correctOption && $correctOption->id == $userAnswer['option_id']) {
                        $totalCorrect++;
                    }
                } elseif ($question->type === 'group') {
                    foreach ($userAnswer['group_questions'] as $groupAnswer) {
                        $groupQuestion = $question->subQuestions->find($groupAnswer['question_id']);
                        $correctOption = $groupQuestion->options->firstWhere('is_correct', 1);

                        if ($groupQuestion && $correctOption && $correctOption->id == $groupAnswer['option_id']) {
                            $totalCorrect++;
                        }
                    }
                }
            }
        }

        $score = ($totalCorrect / $totalQuestions) * 100;

        return response()->json([
            'success' => true,
            'section' => $section->name,
            'total_questions' => $totalQuestions,
            'total_correct' => $totalCorrect,
            'total_false' => $totalQuestions - $totalCorrect,
            'score' => $score
        ]);
    }



}
