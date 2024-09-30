<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{

    public function index(Request $request)
    {
        $sections = Section::with('subjects')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách section thành công!',
            'data' => $sections
        ], 200);
    }

    public function edit($id)
    {
        $section = Section::with('subjects')->find($id);
        if (!$section) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy section!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy thông tin section thành công!',
            'data' => $section
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy section!',
            ], 404);
        }

        $section->update([
            'exam_id' => $request->exam_id,
            'name' => $request->name,
            'is_mandatory' => $request->is_mandatory,
            'timing' => $request->timing
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật section thành công!',
            'data' => $section
        ], 200);
    }

    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string',
            'is_mandatory' => 'required|boolean',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        $section = Section::create([
            'exam_id' => $request->exam_id,
            'name' => $request->name,
            'is_mandatory' => $request->is_mandatory,
        ]);

        $section->questions()->attach($request->question_ids);

        return response()->json([
            'status' => 'success',
            'message' => 'Section đã được tạo và câu hỏi đã được gán vào section thành công!',
            'data' => $section
        ], 201);
    }
}
