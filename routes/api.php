<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChannelsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MyPlansController;
use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




// Routes for login and register
Route::post('/login', [UserController::class, 'login']);

// Route for logout
Route::post('/logout', [UserController::class, 'logout']);

// Route for getting user profile
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/myplans', [MyPlansController::class, 'index']);
});

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/series', [MovieController::class, 'getAllSeries']);
Route::get('/dashboard', [MovieController::class, 'dashboard']);
Route::get('/search', [MovieController::class, 'search']);
Route::get('/channels', [ChannelsController::class, 'index']);
Route::get('/series/{id}', [MovieController::class, 'getSeries']);



// Routes for Admin
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/register', [AdminController::class, 'register']);
Route::get('/admin/movies', [MovieController::class, 'index']);
Route::get('/admin/series', [MovieController::class, 'getAllSeries']);



Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/admin/logout', [AdminController::class, 'logout']);
    Route::get('/admin/profile', [AdminController::class, 'profile']);
    Route::post('/register/user', [UserController::class, 'register']);

    // Routes for adding movies, series, seasons and episodes
    Route::post('/admin/movies', [MovieController::class, 'storeMovie']);
    Route::delete('/admin/movies/{id}', [MovieController::class, 'deleteMovie']);
    Route::post("/movies/{id}/update", [MovieController::class, 'updateMovie']);
    Route::post('/series', [MovieController::class, 'storeSeries']);
    Route::post('/series/{seriesId}/season', [MovieController::class, 'storeSeason']);
    Route::post('/season/{seasonId}/episode', [MovieController::class, 'storeEpisode']);
    Route::post('channels', [ChannelsController::class, 'store']);
    Route::delete('channels/{id}', [ChannelsController::class, 'destroy']);
    Route::post('/store/series', [MovieController::class, 'storeSeriesData']);
    Route::post('/update/series/{seriesId}', [MovieController::class, 'updateSeriesData']);
    Route::post('/update/seriesstatus/{seriesId}', [MovieController::class, 'updateSeriesStatus']);
    Route::delete('/delete/series/{seriesId}', [MovieController::class, 'deleteSeries']);

    // Route for plans
    Route::get('/plans', [PlanController::class, 'index']);
    Route::post('/plans', [PlanController::class, 'storePlan']);
    Route::put('/plans/{id}', [PlanController::class, 'updatePlan']);
    Route::delete('/plans/{id}', [PlanController::class, 'deletePlan']);
    Route::post('/subscribe', [PlanController::class, 'subscribe']);
    Route::get('/planhistory/{userId}', [PlanController::class, 'planHistory']);


    // Admin routes for users
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
    Route::put('/admin/users/{id}', [UserController::class, 'update']);


    // Admin routes for my plans
    Route::get('/admin/myplans', [MyPlansController::class, 'allPlans']);
    Route::get('/admin/myplans/{id}', [MyPlansController::class, 'show']);

    //Admin Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);

    //Search Users
    Route::get("/admin/search", [UserController::class, 'search']);
});


// Artisan routes
Route::get('/migrate', function () {
    Artisan::call('migrate');
    return 'Migrated';
});

// Optimize routes
Route::get('/optimize', function () {
    Artisan::call('optimize');
    return 'Optimized';
});
