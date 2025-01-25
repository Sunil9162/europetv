<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MyPlans;


class MyPlansController extends Controller
{
    public function index()
    {
        try {
            $myPlans = MyPlans::where('user_id', auth()->user()->id)->where('status', '1')->get();
            return response()->json([
                "success" => true,
                "message" => "My plans fetched successfully",
                "my_plans" => $myPlans
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function allPlans()
    {
        try {
            $myPlans = MyPlans::paginate(10);
            $items = $myPlans->items();
            foreach ($items as $item) {
                $item->user = User::find($item->user_id);
                $startDate = $item->start_date;
                $endDate = $item->end_date;
                // Calculate the number of days remaining
                $now = time();
                $end = strtotime($endDate);
                $datediff = $end - $now;
                $daysRemaining = round($datediff / (60 * 60 * 24));
                $item->status = $daysRemaining > 0 ? 1 : 0;
            }
            return response()->json([
                "success" => true,
                "message" => "My plans fetched successfully",
                "my_plans" => $items,
                'meta' => [
                    'total' => $myPlans->total(),
                    'currentPage' => $myPlans->currentPage(),
                    'perPage' => $myPlans->perPage(),
                    'lastPage' => $myPlans->lastPage()
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
            $myPlan = MyPlans::find($id);
            if (!$myPlan) {
                return response()->json([
                    "success" => false,
                    "message" => "My plan not found"
                ], status: 400);
            }
            return response()->json([
                "success" => true,
                "message" => "My plan fetched successfully",
                "my_plan" => $myPlan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
