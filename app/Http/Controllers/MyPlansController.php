<?php

namespace App\Http\Controllers;

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
            return response()->json([
                "success" => true,
                "message" => "My plans fetched successfully",
                "my_plans" => $myPlans->items(),
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
}
