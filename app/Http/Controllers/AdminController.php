<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Movie;
use App\Models\MyPlans;
use App\Models\User;
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
                "message" => $th->getMessage()
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
                "message" => $th->getMessage()
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

    public function dashboard()
    {
        try {
            $request = request();
            $validator = \Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "success" => false,
                    "message" => $validator->errors()->first()
                ], 400);
            }
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $overView = [
                "total_users" => User::where("created_at", "<=", $endDate)->where("created_at", ">=", $startDate)->count(),
                "users_this_month" => User::whereMonth('created_at', date('m'))->count(),
                "total_movies" => Movie::where("created_at", "<=", $endDate)->where("created_at", ">=", $startDate)->count(),
                "movies_this_month" => Movie::whereMonth('created_at', date('m'))->count(),
                "activs_users" => MyPlans::where('status', 1)->where("created_at", "<=", $endDate)->where("created_at", ">=", $startDate)->count(),
                "active_users_this_month" => MyPlans::where('status', 1)->whereMonth('created_at', date('m'))->count(),
            ];
            $revenueReport = [];
            // Graph data
            $revenueReport['labels'] = [];
            $revenueReport['data'] = [];
            $plans = MyPlans::whereBetween('created_at', [now()->subDays(7), now()])->get();
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i)->format('l');
                $revenueReport['labels'][] = $date;
                $planForDate = $plans->firstWhere('created_at', '>=', now()->subDays($i)->startOfDay())
                    ->firstWhere('created_at', '<', now()->subDays($i)->endOfDay());
                $revenueReport['data'][] = $planForDate ? $planForDate->price : 0;
            }


            return response()->json([
                "success" => true,
                "message" => "Dashboard data fetched successfully",
                "overview" => $overView,
                "revenue_report" => $revenueReport
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
