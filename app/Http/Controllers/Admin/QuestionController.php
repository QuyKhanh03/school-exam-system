<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionImage;
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
        try {
            $rules = [
                'subject_id' => 'required|integer',
                'exam_id' => 'required|integer',
                'is_group' => 'required|boolean',
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
            if (in_array($request->subject_id, [2, 3])) {
                $totalSubjectQuestions = Question::where('exam_id', $request->exam_id)
                    ->where('subject_id', $request->subject_id)
                    ->count();

                if ($totalSubjectQuestions >= 50) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot create more than 50 questions for this subject in the same exam.'
                    ], 400);
                }
            }

            if (!in_array($request->subject_id, [2, 3, 12])) {
                $totalQuestions = Question::where('subject_id', $request->subject_id)
                    ->where('exam_id', $request->exam_id)
                    ->count();

                if ($totalQuestions >= 17) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot create more than 18 questions for this subject.'
                    ], 400);
                }
            }

            $request->validate($rules);

            if ($request->is_group) {
                $this->storeGroupQuestion($request);
            } else {
                $this->storeSingleOrInputQuestion($request);
            }
            $section = Section::whereHas('subjects', function ($query) use ($request) {
                $query->where('subjects.id', $request->subject_id);
            })->first();
            if ($section) {
                $this->updateQuestionsFile($request->exam_id, $section->id);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No section found for the provided exam ID.'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Question created successfully!'
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating question: ' . $e->getMessage()
            ], 500);
        }
    }
    private function updateQuestionsFile($exam_id, $section_id)
    {
        $directoryPath = public_path("questions");
        $filePath = $directoryPath . "/{$exam_id}_section_{$section_id}.json";

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0777, true); // Tạo thư mục nếu chưa tồn tại
        }

        $index = 101;
        if ($section_id == 3) {
            $index = 1;
        } elseif ($section_id == 4) {
            $index = 51;
        }

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

        file_put_contents($filePath, json_encode($responseData));
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
            "ordering" => $request->ordering,
//            "label" => $request->label
        ]);

        if ($request->has('images')) {
            foreach ($request->images as $imageURL) {
                QuestionImage::create([
                    'question_id' => $question->id,
                    'url' => $imageURL
                ]);
            }
        }

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
                    'exam_id' => $question->exam_id,
                    'ordering' => $question->ordering,
//                    'label' => $question->label
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
                    'exam_id' => $question->exam_id,
                    'ordering' => $question->ordering,
//                    'label' => $question->label
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
                    "correct_answer" => $value['correct_answer'] ?? null,
                    "ordering" => $value['ordering'],
//                    "label" => $value['label']
                ]);

                if ($request->has('images')) {
                    foreach ($request->images as $imageURL) {
                        QuestionImage::create([
                            'question_id' => $question->id,
                            'url' => $imageURL
                        ]);
                    }
                }

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
    public function edit($id)
    {
        try {
            // Retrieve the question by its ID
            $question = Question::with(['options', 'questionImages'])->find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found.'
                ], 404);
            }

            // Check if it's a group question and retrieve related group questions
            if ($question->is_group) {
                $groupQuestions = Question::where('content_question_group', $question->content_question_group)
                    ->with('options')
                    ->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Group question retrieved successfully!',
                    'data' => [
                        'question' => $question,
                        'group_questions' => $groupQuestions
                    ]
                ]);
            }

            // If it's a single/input type question, return the details
            return response()->json([
                'success' => true,
                'message' => 'Question retrieved successfully!',
                'data' => [
                    'question' => $question
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving question: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'subject_id' => 'required|integer',
                'exam_id' => 'required|integer',
                'is_group' => 'required|boolean',
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

            // Validate the request
            $request->validate($rules);

            // Check if the question exists
            $question = Question::find($id);
            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found.'
                ], 404);
            }

            // Handle group or single/input question update
            if ($request->is_group) {
                return $this->updateGroupQuestion($request, $id);
            }

            return $this->updateSingleOrInputQuestion($request, $id);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating question: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function updateSingleOrInputQuestion(Request $request, $id)
    {
        // Find the question
        $question = Question::findOrFail($id);

        // Update the question details
        $question->update([
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'type' => $request->type,
            'correct_answer' => $request->correct_answer ?? null,
            'exam_id' => $request->exam_id,
            'ordering' => $request->ordering,
//            'label' => $request->label
        ]);

        // Handle images if provided
        if ($request->has('images')) {
            // Remove old images if any
            QuestionImage::where('question_id', $id)->delete();

            // Insert new images
            foreach ($request->images as $imageURL) {
                QuestionImage::create([
                    'question_id' => $question->id,
                    'url' => $imageURL
                ]);
            }
        }

        // Handle options for single type questions
        if ($request->type === 'single') {
            Option::where('question_id', $id)->delete(); // Remove old options

            foreach ($request->options as $option) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct']
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully!',
            'data' => $question
        ]);
    }

    protected function updateGroupQuestion(Request $request, $id)
    {
        // Delete existing group questions and options
        Question::where('content_question_group', $request->content_question_group)->delete();

        foreach ($request->group_questions as $value) {
            $question = Question::create([
                'subject_id' => $request->subject_id,
                'exam_id' => $request->exam_id,
                'name' => $value['name'],
                'content_question_group' => $request->content_question_group,
                'type' => $value['type'],
                'is_group' => true,
                'correct_answer' => $value['correct_answer'] ?? null,
                'ordering' => $value['ordering'],
//                'label' => $value['label']
            ]);

            if ($request->has('images')) {
                foreach ($request->images as $imageURL) {
                    QuestionImage::create([
                        'question_id' => $question->id,
                        'url' => $imageURL
                    ]);
                }
            }

            // Handle options for each group question if single type
            if ($value['type'] === 'single') {
                foreach ($value['options'] as $option) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct']
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Group question updated successfully!'
        ]);
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
                $groupedQuestions = [];
                foreach ($subject->questions as $question) {
                    if ($question->is_group) {
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
        $labels = collect($groupQuestions)->pluck('label')->implode(' - ');

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
//            'label' => $question->label
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
