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

    public function submitSection(Request $request, $attempt_id, $section_id)
    {
        $attempt = Attempt::findOrFail($attempt_id);
        $section = Section::findOrFail($section_id);

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer_text' => 'required|string',
        ]);

        $totalQuestions = count($request->answers);
        $correctAnswers = 0;

        DB::transaction(function () use ($totalQuestions, $request, $attempt, $section, &$correctAnswers) {
            foreach ($request->answers as $answerData) {
                $question = Question::findOrFail($answerData['question_id']);

                $isCorrect = $this->checkAnswer($question, $answerData['answer_text']);
                if ($isCorrect) {
                    $correctAnswers++;
                }
                Answer::create([
                    'question_id' => $question->id,
                    'attempt_id' => $attempt->id,
                    'answer_text' => $answerData['answer_text']
                ]);
            }

            $score = $correctAnswers;
            $percentage = ($correctAnswers / $totalQuestions) * 100;

            ExamSectionScore::create([
                'attempt_id' => $attempt->id,
                'section_id' => $section->id,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'score' => $score,
                'percentage' => $percentage
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Section submitted successfully!',
            'data' => [
                'section_id' => $section->id,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'percentage' => ($correctAnswers / $totalQuestions) * 100,
            ]
        ]);
    }

    private function checkAnswer(Question $question, $answerText)
    {
        if ($question->type === 'single') {
            $correctOption = $question->options()->where('is_correct', 1)->first();
            return $correctOption && $correctOption->option_text === $answerText;
        }

        if ($question->type === 'input') {
            return $question->correct_answer === $answerText;
        }

        return false;
    }
}
