<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Section;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Exam::orderBy('id', 'desc')->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data
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
        try {
            $request->validate([
                'name' => 'required',
                'code' => 'required|unique:exams,code',
            ]);
            $exam = Exam::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Exam created successfully',
                'data' => $exam
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = Exam::query()->findOrFail($id);
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
        $model = Exam::query()->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $model
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $request->validate([
                'name' => 'required',
                'code' => 'required|unique:exams,code,'.$id,
            ]);

            $model = Exam::query()->findOrFail($id);
            $model->fill($request->all());
            $model->update();


            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully',
                'data' => $model
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Exam::query()->findOrFail($id);
        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully'
        ]);
    }

    public function createSection(Request $request)
    {
        $section = Section::create([
            'exam_id' => $request->input('exam_id'),
            'name' => $request->input('name'),
            'is_mandatory' => $request->input('is_mandatory'),
        ]);

        if ($request->has('subject_ids')) {
            $section->subjects()->attach($request->subject_ids);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Section created successfully',
            'data' => $section
        ], 201);
    }
    public function listSections()
    {
        $data = Section::with('subjects')->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
