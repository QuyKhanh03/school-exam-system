<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\ExamSectionScore;
use App\Models\Question;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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


    public function submitQuestionsByExamAndSections(Request $request)
    {
        $sectionsResult = [];
        $userAnswersData = [];

        foreach ($request->exam as $sectionData) {
            $section = Section::with(['subjects.questions.options'])->find($sectionData['section_id']);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section with ID ' . $sectionData['section_id'] . ' not found.'
                ], 404);
            }

            $userAnswers = $sectionData['answers'];
            $totalQuestions = 0;
            $totalCorrect = 0;
            $sectionUserAnswers = [];

            foreach ($section->subjects as $subject) {
                foreach ($subject->questions as $question) {
                    $totalQuestions++;

                    // Lấy câu trả lời của người dùng cho câu hỏi hiện tại
                    $userAnswer = collect($userAnswers)->firstWhere('question_id', $question->id);

                    if ($question->type === 'input') {
                        // Xử lý câu hỏi dạng input
                        $sectionUserAnswers[] = [
                            'question_id' => $question->id,
                            'type' => 'input',
                            'answer_text' => $userAnswer['answer_text'] ?? null
                        ];

                        if ($userAnswer && $question->correct_answer == $userAnswer['answer_text']) {
                            $totalCorrect++;
                        }
                    } elseif ($question->type === 'single') {
                        // Kiểm tra xem có key 'option_id' hay không
                        if (isset($userAnswer) && isset($userAnswer['option_id'])) {
                            $sectionUserAnswers[] = [
                                'question_id' => $question->id,
                                'type' => 'single',
                                'option_id' => $userAnswer['option_id']
                            ];

                            $correctOption = $question->options->firstWhere('is_correct', 1);
                            if ($correctOption && $correctOption->id == $userAnswer['option_id']) {
                                $totalCorrect++;
                            }
                        } else {
                            // Không có 'option_id', người dùng chưa chọn đáp án
                            $sectionUserAnswers[] = [
                                'question_id' => $question->id,
                                'type' => 'single',
                                'option_id' => null // Người dùng chưa chọn đáp án
                            ];
                        }
                    }
                }
            }

            $score = ($totalCorrect / $totalQuestions) * 100;

            $sectionsResult[] = [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'total_questions' => $totalQuestions,
                'total_correct' => $totalCorrect,
                'total_false' => $totalQuestions - $totalCorrect,
                'score' => $score
            ];

            $userAnswersData[] = [
                'section_id' => $section->id,
                'answers' => $sectionUserAnswers
            ];
        }

        // Lưu dữ liệu trong cookie
        $cookieResults = cookie('exam_results', json_encode($sectionsResult), 60); // Lưu kết quả trong 60 phút
        $cookieAnswers = cookie('exam_answers', json_encode($userAnswersData), 60); // Lưu đáp án trong 60 phút

        return response()->json([
            'success' => true,
            'sections' => $sectionsResult
        ])->cookie($cookieResults)->cookie($cookieAnswers);
    }



    public function showUserAnswers(Request $request)
    {
        // Lấy dữ liệu từ cookie hoặc từ DB
        $examResults = json_decode($request->cookie('exam_results'), true);
        $examAnswers = json_decode($request->cookie('exam_answers'), true);

        if (!$examResults || !$examAnswers) {
            return response()->json([
                'success' => false,
                'message' => 'No exam results or answers found.'
            ], 400);
        }

        $sectionsWithAnswers = [];

        foreach ($examAnswers as $sectionAnswers) {
            $sectionId = $sectionAnswers['section_id'];
            $sectionResult = collect($examResults)->firstWhere('section_id', $sectionId);

            $questionsWithAnswers = [];

            foreach ($sectionAnswers['answers'] as $answer) {
                $question = Question::with('options')->find($answer['question_id']);

                if ($question) {
                    if ($question->type === 'input') {
                        // Câu hỏi dạng input, hiển thị câu trả lời của người dùng
                        $questionsWithAnswers[] = [
                            'question_id' => $question->id,
                            'question_text' => $question->name,
                            'type' => 'input',
                            'user_answer' => $answer['answer_text'] ?? null,
                            'correct_answer' => $question->correct_answer
                        ];
                    } elseif ($question->type === 'single') {
                        // Xử lý câu hỏi dạng single, hiển thị tùy chọn người dùng đã chọn
                        $correctOption = $question->options->firstWhere('is_correct', 1);
                        $userOption = isset($answer['option_id']) ? $question->options->find($answer['option_id']) : null;

                        $options = $question->options->map(function ($option) use ($userOption, $correctOption) {
                            return [
                                'option_id' => $option->id,
                                'option_text' => $option->option_text,
                                'is_correct' => $option->is_correct,
                                'is_user_selected' => $userOption && $userOption->id == $option->id
                            ];
                        });

                        $questionsWithAnswers[] = [
                            'question_id' => $question->id,
                            'question_text' => $question->name,
                            'type' => 'single',
                            'options' => $options
                        ];
                    }
                }
            }

            $sectionsWithAnswers[] = [
                'section_id' => $sectionId,
                'section_name' => $sectionResult['section_name'],
                'questions' => $questionsWithAnswers
            ];
        }

        return response()->json([
            'success' => true,
            'sections' => $sectionsWithAnswers
        ]);
    }






    public function saveUserInfo(Request $request)
    {
        // Kiểm tra cookie để lấy kết quả thi và câu trả lời đã lưu
        $examResults = json_decode($request->cookie('exam_results'), true);
        $examAnswers = json_decode($request->cookie('exam_answers'), true);

        if (!$examResults || !$examAnswers) {
            return response()->json([
                'success' => false,
                'message' => 'No exam results or answers found in cookie.'
            ], 400);
        }

        // Tạo hoặc cập nhật tài khoản cho người dùng với status = 0
        $user = User::updateOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->username,
                'password' => bcrypt($request->email),
                'status' => 0 ,
                'role_name' => 'user',
                'facebookurl' => $request->facebookurl,
                'phone_number' => $request->phone_number,
                'workplace' => $request->workplace,
                'dob' => $request->dob,
                'address' => $request->address,
                'gender' => $request->gender,
                'user_code' => $request->user_code,
                'school_id' => $request->school_id,
                'province_id' => $request->province_id,
                'username' => $request->username
            ]
        );







        // Xóa cookie sau khi đã lưu dữ liệu
        Cookie::queue(Cookie::forget('exam_results'));
        Cookie::queue(Cookie::forget('exam_answers'));

        return response()->json([
            'success' => true,
            'message' => 'User account created and exam results and answers saved successfully',
            'user_id' => $user->id
        ]);
    }







}
