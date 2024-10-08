<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Section;
use http\Client;
use Illuminate\Http\Request;
use function Termwind\render;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //render html
    }

    public function listQuestions($exam_id, $section_id)
    {
        $directoryPath = public_path("questions");
        $filePath = $directoryPath . "/{$exam_id}_section_{$section_id}.json";

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0777, true); // Tạo thư mục với quyền truy cập đầy đủ
        }

        // Nếu file JSON đã tồn tại, đọc trực tiếp từ file và trả về dữ liệu
        if (file_exists($filePath)) {
            $questionsData = json_decode(file_get_contents($filePath), true);
            return response()->json($questionsData);
        }

        $index = 101; // Khởi tạo index mặc định
        if ($section_id == 3) {
            $index = 1; // Section 3: từ 1
        } elseif ($section_id == 4) {
            $index = 51; // Section 4: từ 51
        }

        // Lấy dữ liệu từ database
        $section = Section::with([
            'subjects.questions' => function ($query) use ($exam_id) {
                $query->where('exam_id', $exam_id);
            },
            'subjects.questions.options',
            'subjects.questions.questionImages'
        ])->find($section_id);

        $questions = [];
        $totalQuestions = 0;

        if ($section) {
            foreach ($section->subjects as $subject) {
                $sortedQuestions = $subject->questions->sortBy('ordering');

                $groupedQuestions = [];
                foreach ($sortedQuestions as $question) {
                    if ($question->is_group) {
                        if (!isset($groupedQuestions[$question->content_question_group])) {
                            $groupedQuestions[$question->content_question_group] = [];
                        }
                        $groupedQuestions[$question->content_question_group][] = $question;
                    } else {
                        $totalQuestions++;
                        $questions[] = $this->formatQuestion($question, $subject->name, $index);
                        $index++;
                    }
                }

                foreach ($groupedQuestions as $contentQuestionGroup => $groupQuestions) {
                    $totalQuestions += count($groupQuestions);
                    $questions[] = $this->formatGroupQuestions($contentQuestionGroup, $groupQuestions, $subject->name, $index);
                    $index++;
                }
            }
        }

        $responseData = [
            "success" => true,
            "section" => $section ? $section->name : null,
            "time" => $section ? $section->timing : null,
            "total_questions" => $totalQuestions,
            "questions" => $questions
        ];

        // Lưu dữ liệu vào file JSON
        file_put_contents($filePath, json_encode($responseData));

        return response()->json($responseData);
    }


    public function formatGroupQuestions($contentQuestionGroup, $groupQuestions, $subjectName, &$index)
    {
        $groupIndexStart = $index; // Ghi lại chỉ số bắt đầu của câu hỏi nhóm

        $formattedGroupQuestions = collect($groupQuestions)->map(function ($question) use ($subjectName, &$index) {
            $formattedQuestion = $this->formatQuestion($question, $subjectName, $index);
            $index++; // Tăng index cho mỗi câu hỏi trong nhóm
            return $formattedQuestion;
        })->toArray();

        // Sau khi xử lý nhóm câu hỏi, chỉ tăng index một lần cho nhóm
        return [
            'subject' => $subjectName,
            'content_question_group' => $contentQuestionGroup,
            'type' => 'group',
            'group_questions' => $formattedGroupQuestions,
            'label' => $groupIndexStart // Gán label cho nhóm dựa trên index của câu hỏi đầu tiên trong nhóm
        ];
    }

    public function formatQuestion($question, $subjectName, $index)
    {
        return [
            'subject' => $subjectName,
            'question_id' => $question->id,
            'question' => $question->name,
            'type' => $question->type,
            'options' => $question->type === 'single' ? $this->formatOptions($question->options) : [],
            'correct_answer' => $question->type === 'input' ? $question->correct_answer : null,
            'ordering' => $question->ordering,
            'label' => $index, // Gán index làm label
            'images' => $question->questionImages->pluck('url')
        ];
    }

    public function formatOptions($options)
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
