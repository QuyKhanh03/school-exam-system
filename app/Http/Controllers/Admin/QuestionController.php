<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->search) {
            $questions = Question::with('exam', 'subject')
                ->select('id', 'exam_id', 'subject_id', 'name', 'type', 'is_group', 'parent_id')
                ->where('name', 'like', '%' . $request->search . '%')
                ->get();
        } else {
            $questions = Question::with('exam', 'subject')
                ->select('id', 'exam_id', 'subject_id', 'name', 'type', 'is_group', 'parent_id')
                ->get();
        }

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
                    'is_group' => (string) $question->is_group,
                    'options' => []
                ];

                if ($question->type === 'single') {
                    foreach ($question->options as $option) {
                        $questionData['options'][] = [
                            'option_id' => $option->id,
                            'option_content' => $option->option_text,
                            'is_correct' => (string) $option->is_correct
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
                            'is_group' => (string) $childQuestion->is_group,
                            'options' => []
                        ];

                        if ($childQuestion->type === 'single') {
                            foreach ($childQuestion->options as $childOption) {
                                $childQuestionData['options'][] = [
                                    'option_id' => $childOption->id,
                                    'option_content' => $childOption->option_text,
                                    'is_correct' => (string) $childOption->is_correct
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
        $request->validate([
            'exam_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'name' => 'required|string',
            'type' => 'required|in:single,input,group',
            'is_group' => 'required|boolean',
            'options' => 'required_if:type,single|array',
            'options.*.text' => 'required_if:type,single|string',
            'options.*.is_correct' => 'required_if:type,single|boolean',
            'group_questions' => 'required_if:type,group|array',
            'group_questions.*.name' => 'required_if:type,group|string',
            'group_questions.*.type' => 'required_if:type,group|in:single,input',
            'group_questions.*.options' => 'required_if:group_questions.*.type,single|array',
            'group_questions.*.options.*.text' => 'required_if:group_questions.*.type,single|string',
            'group_questions.*.options.*.is_correct' => 'required_if:group_questions.*.type,single|boolean',
        ]);
        try {
            if ($request->type === 'group' && $request->is_group) {
                return $this->storeGroupQuestion($request);
            }

            return $this->storeSingleOrInputQuestion($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating question: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function storeSingleOrInputQuestion($request)
    {
        $question = Question::create([
            'exam_id' => $request->exam_id,
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'type' => $request->type,
            'is_group' => false,
        ]);
        if ($request->type === 'single') {
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
            'message' => 'Question created successfully!',
            'data' => $question
        ]);
    }

    protected function storeGroupQuestion(Request $request)
    {
        $parentQuestion = Question::create([
            'exam_id' => $request->exam_id,
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'type' => 'group',
            'is_group' => true,
        ]);

        foreach ($request->group_questions as $groupQuestion) {
            $childQuestion = Question::create([
                'exam_id' => $request->exam_id,
                'subject_id' => $request->subject_id,
                'name' => $groupQuestion['name'],
                'type' => $groupQuestion['type'],
                'parent_id' => $parentQuestion->id,
                'is_group' => false,
            ]);

            if ($groupQuestion['type'] === 'single') {
                foreach ($groupQuestion['options'] as $option) {
                    Option::create([
                        'question_id' => $childQuestion->id,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct']
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Group question created successfully!',
            'data' => $parentQuestion
        ]);
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
