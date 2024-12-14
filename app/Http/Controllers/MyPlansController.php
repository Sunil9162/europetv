<?php

namespace App\Http\Controllers;

use App\Models\MyPlans;
use Illuminate\Http\Request;

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
                "message" =>  $th->getMessage()
            ], 500);
        }
    }
}
