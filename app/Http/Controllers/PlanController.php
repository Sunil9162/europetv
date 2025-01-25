<?php

namespace App\Http\Controllers;

use App\Models\MyPlans;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        try {
            $plans = Plan::all();
            return response()->json([
                "success" => true,
                "message" => "Plans fetched successfully",
                "plans" => $plans
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }

    public function storePlan(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:55',
                'price' => 'required',
                'duration' => 'required',
                'description' => 'required'
            ]);

            $plan = Plan::create($validatedData);

            return response()->json([
                "success" => true,
                "message" => "Plan created successfully",
                "plan" => $plan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }

    public function updatePlan(Request $request, $id)
    {
        try {
            $plan = Plan::find($id);
            if (!$plan) {
                return response()->json([
                    "success" => false,
                    "message" => "Plan not found"
                ], 404);
            }

            $validatedData = $request->validate([
                'name' => 'required|max:55',
                'price' => 'required',
                'duration' => 'required',
                'description' => 'required'
            ]);

            $plan->update($validatedData);

            return response()->json([
                "success" => true,
                "message" => "Plan updated successfully",
                "plan" => $plan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }

    public function deletePlan($id)
    {
        try {
            $plan = Plan::find($id);
            if (!$plan) {
                return response()->json([
                    "success" => false,
                    "message" => "Plan not found"
                ], 404);
            }

            $plan->delete();

            return response()->json([
                "success" => true,
                "message" => "Plan deleted successfully"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }

    public function subscribe(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now());
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found"
                ], 404);
            }
            $plan = Plan::find($request->plan_id);
            if (!$plan) {
                return response()->json([
                    "success" => false,
                    "message" => "Plan not found"
                ], 404);
            }
            $planExist = MyPlans::where('user_id', $user->id)->where('plan_id', $plan->id)->first();
            if ($planExist) {
                // Extend the plan validity
                $planExist->update([
                    'end_date' => ($planExist->end_date > now()) ? $planExist->end_date->addDays($plan->duration) : now()->addDays($plan->duration)
                ]);
                return response()->json([
                    "success" => true,
                    "message" => "Plan extended successfully",
                    'subscription' => $planExist,
                    'days_left' => now()->diffInDays($planExist->end_date)
                ], 200);
            }
            $myPlan = new MyPlans();
            $myPlan->user_id = $user->id;
            $myPlan->plan_id = $plan->id;
            $myPlan->start_date = $startDate;
            $myPlan->name = $plan->name;
            $myPlan->end_date = now()->addDays($plan->duration);
            $myPlan->price = $plan->price;
            $myPlan->save();
            return response()->json([
                "success" => true,
                "message" => "Subscribed successfully",
                "plan" => $plan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" =>  $th->getMessage()
            ], 500);
        }
    }
}
