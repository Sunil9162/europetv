<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:55',
                'email' => 'email|required|unique:admins',
                'password' => 'required|confirmed'
            ]);

            $validatedData['password'] = bcrypt($request->password);

            $admin = Admin::create($validatedData);

            $accessToken = $admin->createToken('admin')->plainTextToken;
            return response()->json([
                "success" => true,
                "message" => "Admin registered successfully",
                "admin" => $admin,
                "access_token" => $accessToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $loginData = $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            if (!auth()->attempt($loginData)) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid credentials"
                ], 401);
            }
            $accessToken = Admin::where('email', $request->email)->first()->createToken('admin')->plainTextToken;

            return response()->json([
                "success" => true,
                "message" => "Admin logged in successfully",
                "admin" => Admin::where('email', $request->email)->first(),
                "access_token" => $accessToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                "success" => true,
                "message" => "Logged out successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }


    public function profile(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Admin profile",
            "data" => $request->user()
        ]);
    }
}
