<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\Section;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::with('subject', 'options')
            ->select('id', 'subject_id', 'name', 'type', 'is_group', 'parent_id');

        // Tìm kiếm theo tên câu hỏi
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Lọc theo subject_id
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        // Lọc theo loại câu hỏi (type)
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Kiểm tra và sử dụng limit từ request, mặc định là 10
        $limit = $request->has('limit') ? (int) $request->limit : 10;

        $questions = $query->orderBy('id', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }




    public function listQuestions()
    {
        try {
            $questions = Question::with('options')
                ->orderBy('is_group', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $result = [];
            $groupedQuestions = [];

            foreach ($questions as $question) {
                $questionData = [
                    'question_id' => $question->id,
                    'question_content' => $question->content,
                    'question_type' => $question->type,
                    'is_group' => (string)$question->is_group,
                    'options' => []
                ];

                if ($question->type === 'single') {
                    foreach ($question->options as $option) {
                        $questionData['options'][] = [
                            'option_id' => $option->id,
                            'option_content' => $option->option_text,
                            'is_correct' => (string)$option->is_correct
                        ];
                    }
                }
                if ($question->is_group) {
                    $childQuestions = Question::with('options')->where('parent_id', $question->id)->get();
                    $childData = [];

                    foreach ($childQuestions as $childQuestion) {
                        $childQuestionData = [
                            'question_id' => $childQuestion->id,
                            'question_content' => $childQuestion->content,
                            'question_type' => $childQuestion->type,
                            'is_group' => (string)$childQuestion->is_group,
                            'options' => []
                        ];

                        if ($childQuestion->type === 'single') {
                            foreach ($childQuestion->options as $childOption) {
                                $childQuestionData['options'][] = [
                                    'option_id' => $childOption->id,
                                    'option_content' => $childOption->option_text,
                                    'is_correct' => (string)$childOption->is_correct
                                ];
                            }
                        }

                        $childData[] = $childQuestionData;
                    }

                    $questionData['child_questions'] = $childData;
                    $groupedQuestions[] = $questionData;
                } else {
                    $result[] = $questionData;
                }
            }

            $result = array_merge($result, $groupedQuestions);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching questions: ' . $e->getMessage()
            ], 500);
        }
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
        $rules = [
            'subject_id' => 'required|integer',
            'exam_id' => 'required|integer',
            'is_group' => 'required|boolean',
            'ordering' => 'required|integer',
            'label' => 'required|integer'
        ];

        if ($request->is_group) {
            $rules['content_question_group'] = 'required|string';
            $rules['group_questions'] = 'required|array';
            $rules['group_questions.*.name'] = 'required|string';
            $rules['group_questions.*.type'] = 'required|in:single,input';
            $rules['group_questions.*.ordering'] = 'required|integer';
            $rules['group_questions.*.label'] = 'required';

            $rules['group_questions.*.options'] = 'required_if:group_questions.*.type,single|array';
            $rules['group_questions.*.options.*.text'] = 'required_if:group_questions.*.type,single|string';
            $rules['group_questions.*.options.*.is_correct'] = 'required_if:group_questions.*.type,single|boolean';
        } else {
            $rules['name'] = 'required|string';
            $rules['type'] = 'required|in:single,input';

            $rules['options'] = 'required_if:type,single|array';
            $rules['options.*.text'] = 'required_if:type,single|string';
            $rules['options.*.is_correct'] = 'required_if:type,single|boolean';
        }
        if (!in_array($request->subject_id, [2, 3, 12])) {
            $totalQuestions = Question::where('subject_id', $request->subject_id)->count();

            if ($totalQuestions >= 18) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot create more than 18 questions for this subject.'
                ], 400);
            }
        }

        $request->validate($rules);
        if ($request->is_group) {
            return $this->storeGroupQuestion($request);
        }

        return $this->storeSingleOrInputQuestion($request);
    }

    protected function storeSingleOrInputQuestion($request)
    {
        $question = Question::create([
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'type' => $request->type,
            'is_group' => false,
            'correct_answer' => $request->correct_answer ?? null,
            "exam_id" => $request->exam_id,
        ]);

        if ($request->type === 'single') {
            $options = [];
            foreach ($request->options as $option) {
                $createdOption = Option::create([
                    'question_id' => $question->id,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct']
                ]);
                $options[] = [
                    'option_id' => $createdOption->id,
                    'option_text' => $createdOption->option_text,
                    'is_correct' => $createdOption->is_correct
                ];
            }
            // Trả về câu hỏi với options nếu loại là "single"
            return response()->json([
                'success' => true,
                'message' => 'Question created successfully!',
                'data' => [
                    'question_id' => $question->id,
                    'question_type' => $question->type,
                    'name' => $question->name,
                    'options' => $options,
                    'exam_id' => $question->exam_id
                ]
            ]);
        }

        if ($request->type === 'input') {
            return response()->json([
                'success' => true,
                'message' => 'Question created successfully!',
                'data' => [
                    'question_id' => $question->id,
                    'question_type' => $question->type,
                    'name' => $question->name,
                    'correct_answer' => $question->correct_answer,
                    'exam_id' => $question->exam_id
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully!',
            'data' => $question
        ]);
    }
    protected function storeGroupQuestion(Request $request)
    {
        if($request->is_group) {
            foreach ($request->group_questions as $value){
                $question = Question::create([
                    'subject_id' => $request->subject_id,
                    'exam_id' => $request->exam_id,
                    'name' => $value['name'],
                    "content_question_group" => $request->content_question_group,
                    'type' => $value['type'],
                    'is_group' => true,
                    "correct_answer" => $request->correct_answer,
                    "ordering" => $value['ordering'],
                    "label" => $value['label']
                ]);
                if($value['type'] === 'single'){
                    foreach ($value['options'] as $option) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => $option['text'],
                            'is_correct' => $option['is_correct']
                        ]);
                    }
                }
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Group question created successfully!',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = Question::with('options')->find($id);

        return response()->json([
            'success' => true,
            'data' => $model
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $question = Question::with('options')->findOrFail($id);

        if ($question->is_group) {
            $groupQuestions = Question::with('options')
                ->where('content_question_group', $question->content_question_group)
                ->where('is_group', false)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'question' => $question,
                    'group_questions' => $groupQuestions
                ]
            ]);
        }

        // Trả về câu hỏi đơn
        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Xác định câu hỏi cần cập nhật
        $question = Question::findOrFail($id);

        // Thiết lập quy tắc validate dựa trên giá trị is_group
        $rules = [
            'subject_id' => 'required|integer',
            'exam_id' => 'required|integer',
            'is_group' => 'required|boolean'
        ];

        // Kiểm tra nếu là câu hỏi nhóm (is_group = true)
        if ($request->is_group) {
            $rules['content_question_group'] = 'required|string'; // Câu hỏi chính của nhóm
            $rules['group_questions'] = 'required|array'; // Các câu hỏi con
            $rules['group_questions.*.name'] = 'required|string'; // Tên câu hỏi con
            $rules['group_questions.*.type'] = 'required|in:single,input'; // Loại câu hỏi con
            $rules['group_questions.*.ordering'] = 'required|integer'; // Thứ tự
            $rules['group_questions.*.label'] = 'required'; // Nhãn (label)

            // Nếu câu hỏi con là "single", yêu cầu các options
            $rules['group_questions.*.options'] = 'required_if:group_questions.*.type,single|array';
            $rules['group_questions.*.options.*.text'] = 'required_if:group_questions.*.type,single|string';
            $rules['group_questions.*.options.*.is_correct'] = 'required_if:group_questions.*.type,single|boolean';
        } else {
            // Nếu là câu hỏi đơn lẻ (không phải nhóm)
            $rules['name'] = 'required|string';
            $rules['type'] = 'required|in:single,input';

            // Nếu là "single", yêu cầu các options
            $rules['options'] = 'required_if:type,single|array';
            $rules['options.*.text'] = 'required_if:type,single|string';
            $rules['options.*.is_correct'] = 'required_if:type,single|boolean';
        }

        // Validate request
        $request->validate($rules);

        // Nếu là câu hỏi nhóm, xử lý việc cập nhật câu hỏi nhóm
        if ($request->is_group) {
            $this->updateGroupQuestion($request, $question);
        } else {
            // Nếu không phải câu hỏi nhóm, cập nhật câu hỏi đơn
            $this->updateSingleOrInputQuestion($request, $question);
        }

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully!',
        ]);
    }

    protected function updateGroupQuestion(Request $request, $question)
    {
        // Cập nhật câu hỏi chính (content_question_group)
        $question->update([
            'subject_id' => $request->subject_id,
            'exam_id' => $request->exam_id,
            'content_question_group' => $request->content_question_group
        ]);

        // Xóa các câu hỏi con cũ nếu có
        Question::where('content_question_group', $question->content_question_group)
            ->where('is_group', false)
            ->delete();

        // Tạo lại các câu hỏi con từ dữ liệu mới
        foreach ($request->group_questions as $value) {
            $subQuestion = Question::create([
                'subject_id' => $request->subject_id,
                'exam_id' => $request->exam_id,
                'name' => $value['name'],
                'content_question_group' => $request->content_question_group,
                'type' => $value['type'],
                'is_group' => false,
                "correct_answer" => $value['correct_answer'] ?? null,
                "ordering" => $value['ordering'],
                "label" => $value['label']
            ]);

            // Xử lý options nếu là câu hỏi "single"
            if ($value['type'] === 'single') {
                foreach ($value['options'] as $option) {
                    Option::create([
                        'question_id' => $subQuestion->id,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct']
                    ]);
                }
            }
        }
    }

    protected function updateSingleOrInputQuestion(Request $request, $question)
    {
        // Cập nhật câu hỏi đơn
        $question->update([
            'subject_id' => $request->subject_id,
            'exam_id' => $request->exam_id,
            'name' => $request->name,
            'type' => $request->type,
            'correct_answer' => $request->correct_answer ?? null,
        ]);

        if ($request->type === 'single') {
            Option::where('question_id', $question->id)->delete();

            // Thêm lại các option mới
            foreach ($request->options as $option) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct']
                ]);
            }
        }
    }

    public function listQuestionByExamAndSection($exam_id, $section_id)
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
            'content_question_group' => $contentQuestionGroup,
            'label' => $labels,
            'type' => 'group',
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $question = Question::findOrFail($id);

        if ($question->is_group) {
            Question::where('content_question_group', $question->content_question_group)->delete();
        }
        Option::where('question_id', $question->id)->delete();

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully!',
        ]);
    }



}
