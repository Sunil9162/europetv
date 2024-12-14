<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChannelsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::resource('channels', ChannelsController::class);

Route::get('/addmovies', function () {
    return  view('addmovies');
})->name('addmovies');

Route::get('/addchannels', function () {
    return  view('addchannels');
})->name('addchannels');

Route::get('/unauthenticated', function () {
    return response()->json([
        "sucess" => false,
        "message" => "Unauthorized Access Denied",
    ], 401);
})->name('unauthenticated');
