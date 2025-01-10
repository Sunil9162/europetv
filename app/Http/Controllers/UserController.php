<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MyPlans;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:55',
                'email' => 'email|required|unique:users',
                'password' => 'required|confirmed'
            ]);

            $validatedData['password'] = bcrypt($request->password);

            $user = User::create($validatedData);

            $accessToken = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                "success" => true,
                "message" => "User registered successfully",
                "user" => $user,
                "access_token" => $accessToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
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
            $accessToken = User::where('email', $request->email)->first()->createToken('authToken')->plainTextToken;
            $myplan = MyPlans::where('user_id', auth()->user()->id)->where('status', '1')->get();
            return response()->json([
                "success" => true,
                "message" => "User logged in successfully",
                "user" => User::where('email', $request->email)->first(),
                "my_plans" => $myplan,
                "access_token" => $accessToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();
            return response()->json([
                "success" => true,
                "message" => "User logged out successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found"
                ], 404);
            }
            $plan = MyPlans::where('user_id', auth()->user()->id)->where('status', '1')->get();
            // Get Access Token from Header
            $accessToken = $request->header('Authorization');
            // Remove Bearer from token and space
            $accessToken = str_replace('Bearer ', '', $accessToken);
            return response()->json([
                "success" => true,
                "message" => "User profile fetched successfully",
                "user" => auth()->user(),
                'my_plans' => $plan,
                "access_token" => $accessToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        try {
            $users = User::paginate();
            return response()->json([
                "success" => true,
                "message" => "Users fetched successfully",
                "users" => $users->items(),
                'meta' => [
                    'total' => $users->total(),
                    'currentPage' => $users->currentPage(),
                    'perPage' => $users->perPage(),
                    'lastPage' => $users->lastPage()
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found"
                ], 404);
            }
            return response()->json([
                "success" => true,
                "message" => "User fetched successfully",
                "user" => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found"
                ], 404);
            }
            $user->delete();
            return response()->json([
                "success" => true,
                "message" => "User deleted successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found"
                ], 404);
            }
            $validatedData = $request->validate([
                'name' => 'required|max:55',
                'email' => 'email|required|unique:users',
                'password' => 'required'
            ]);

            $validatedData['password'] = bcrypt($request->password);

            $user->update($validatedData);
            return response()->json([
                "success" => true,
                "message" => "User updated successfully",
                "user" => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
