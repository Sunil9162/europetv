<?php

namespace App\Http\Controllers;

use App\Models\Channels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChannelsController extends Controller
{

    public function index()
    {
        $channels = Channels::all();
        return response()->json([
            "success" => true,
            "message" => "Channels List",
            "data" => $channels
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:channels',
                'image' => 'required',
                'url' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "success" => false,
                    "message" =>   $validator->errors()->first()
                ], 400);
            }
            $channel = new Channels();
            $channel->name = $request->name;
            $channel->image = $request->image;
            $channel->url = $request->url;
            $channel->save();
            return response()->json([
                "success" => true,
                "message" => "Channel created successfully",
                "data" => $channel
            ]);
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
            $channel = Channels::find($id);
            if ($channel) {
                $channel->delete();
                return response()->json([
                    "success" => true,
                    "message" => "Channel deleted successfully"
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Channel not found"
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
