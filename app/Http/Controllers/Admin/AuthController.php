<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            $credentials = $request->only('email', 'password');
            if(!auth()->attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            //remove old token
//            auth()->user()->tokens()->delete();

            $token = auth()->user()->createToken('authToken')->plainTextToken;
            return response()->json([
                "success" => true,
                "access_token" => $token,
                "token_type" => "Bearer"
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
            ]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_name' => 'admin'
            ]);
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                "success" => true,
                "access_token" => $token,
                "token_type" => "Bearer"
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            auth()->user()->tokens()->delete();
            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $data = User::where('role_name', 'admin')->get();
        return response([
            'success' => true,
            'data' => $data
        ]);
    }




    //send mail system SonHaDuAn

    public function sendMail(Request $request) {
        try {
            //send mail
            $name = $request->name;
            $email = $request->email;
            $phone = $request->phone;
            $content = $request->content;
            $data = array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'content' => $content
            );
            Mail::to($email)->send(new SendMail($data));
            return back();
        }catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
