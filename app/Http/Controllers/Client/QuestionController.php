<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Question;
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
    public function listQuestions($exam_id, $subject_id)
    {
        $questions = Question::where('exam_id', $exam_id)
            ->where('subject_id', $subject_id)
            ->get();

        if ($questions->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'data' => $questions
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No questions found for the given exam and subject'
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
//    public function postContactUs(Request $request) {
//        // Kiểm tra xem tất cả các trường bắt buộc đã được nhập chưa
//        if ($request->name && $request->email && $request->phone && $request->content) {
//
//            // Lưu thông tin liên hệ vào cơ sở dữ liệu
//            Contact::create([
//                'name'    => $request->name,
//                'email'   => $request->email,
//                'phone'   => $request->phone,
//                'content' => $request->content,
//            ]);
//
//            // Tạo URL với các tham số để gọi GET request
//            $url = 'https://chatpion.id.vn/send';
//            $queryParams = [
//                'email'   => $request->email,
//                'name'    => $request->name,
//                'phone'   => $request->phone,
//                'content' => $request->content,
//            ];
//
//            // Sử dụng Guzzle để gửi GET request
//            try {
//                // Tạo một client Guzzle
//                $client = new Client();
//
//                // Gửi yêu cầu GET với các tham số
//                $response = $client->request('GET', $url, [
//                    'query' => $queryParams
//                ]);
//
//                // Kiểm tra mã phản hồi HTTP
//                if ($response->getStatusCode() == 200) {
//                    $request->session()->flash('alert-success', 'Cảm ơn bạn đã gửi liên hệ đến chúng tôi và thông tin đã được gửi thành công!');
//                } else {
//                    $request->session()->flash('alert-warning', 'Liên hệ đã được gửi nhưng không thể kết nối tới URL chatpion.id.vn.');
//                }
//            } catch (\Exception $e) {
//                // Xử lý ngoại lệ khi không thể gửi HTTP request
//                $request->session()->flash('alert-danger', 'Đã xảy ra lỗi khi kết nối đến URL: ' . $e->getMessage());
//            }
//
//            return redirect()->back();
//
//        } else {
//            // Nếu các trường bắt buộc không được nhập
//            $request->session()->flash('alert-danger', 'Vui lòng điền đủ những trường có dấu (*)!');
//            return redirect()->back();
//        }
//    }
}
