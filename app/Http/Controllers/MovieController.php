<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Series;
use App\Models\Season;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    // Get all movies and series
    public function index(Request $request)
    {
        $movies = Movie::with('categories', 'tags')->paginate(10);

        if ($request->has('category')) {
            $movies = Movie::with('categories', 'tags')->whereHas('categories', function ($query) use ($request) {
                $query->where('name', $request->category);
            })->paginate(10);
        }

        if ($request->has(key: 'tag')) {
            $movies = Movie::with('categories', 'tags')->whereHas('tags', function ($query) use ($request) {
                $query->where('name', $request->tag);
            })->paginate(10);
        }

        return response()->json([
            'success' => true,
            'message' => 'Movies and series retrieved successfully',
            'movies' => $movies->items(),
            'meta' => [
                'total' => $movies->total(),
                'currentPage' => $movies->currentPage(),
                'perPage' => $movies->perPage(),
                'lastPage' => $movies->lastPage()
            ]
        ], 200);
    }

    public function getAllSeries()
    {
        $series =  Series::with('seasons.episodes')->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Series retrieved successfully',
            'series' => $series->items(),
            'meta' => [
                'total' => $series->total(),
                'currentPage' => $series->currentPage(),
                'perPage' => $series->perPage(),
                'lastPage' => $series->lastPage()
            ]
        ], 200);
    }

    // Add a new movie
    public function storeMovie(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'director' => 'nullable|string',
            'producer' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'rating' => 'nullable|string',
            'poster' => 'nullable|string',
            'trailer_url' => 'nullable|string',
            'movie_url' => 'nullable|string',
            'categories' => 'array',
            'tags' => 'array'
        ]);

        $movie = Movie::create($data);

        if ($request->has('categories')) {
            // Create categories if they don't exist
            foreach ($request->categories as $category) {
                Category::firstOrCreate(['name' => $category]);
            }

            $categoryIds = Category::whereIn('name', $request->categories)->pluck('id');
            $movie->categories()->sync($categoryIds);
        }

        // Sync tags
        if ($request->has('tags')) {
            // Create tags if they don't exist
            foreach ($request->tags as $tag) {
                Tag::firstOrCreate(['name' => $tag]);
            }
            $tagIds = Tag::whereIn('name', $request->tags)->pluck('id');
            $movie->tags()->sync($tagIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Movie created successfully',
            'movie' => $movie
        ], 200);
    }

    // Add a new series
    public function storeSeries(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'poster' => 'nullable|string'
        ]);

        $series = Series::create($data);

        return response()->json($series);
    }

    // Add a season to a series
    public function storeSeason(Request $request, $seriesId)
    {
        try {
            $data = $request->validate([
                'season_number' => 'required|integer'
            ]);

            $series = Series::findOrFail($seriesId);
            $season = $series->seasons()->create($data);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Season created successfully',
                    'season' => $season
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 404);
        }
    }

    // Add an episode to a season
    public function storeEpisode(Request $request, $seasonId)
    {
        try {
            $data = $request->validate([
                'episode_number' => 'required|integer',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'trailer_url' => 'nullable|string',
                'episode_url' => 'nullable|string'
            ]);

            $season = Season::findOrFail($seasonId);
            $episode = $season->episodes()->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Episode created successfully',
                'episode' => $episode
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 404);
        }
    }

    public function dashboard()
    {
        $newAddedMovies = Movie::orderBy('created_at', 'desc')->limit(10)->get();
        $newAddedSeries = Series::orderBy('created_at', 'desc')->limit(10)->get();
        $trendingMovies = Movie::orderBy('view_count', 'desc')->limit(10)->get();
        $trendingSeries = Series::orderBy('view_count', 'desc')->limit(10)->get();
        $recommendedMovies = Movie::inRandomOrder()->limit(10)->get();
        $recommendedSeries = Series::inRandomOrder()->limit(10)->get();
        $allCategories = Category::all();
        $popularInCategory = [];
        foreach ($allCategories as $category) {
            $movies = Movie::whereHas('categories', function ($query) use ($category) {
                $query->where('name', $category->name);
            })->orderBy('view_count', 'desc')->limit(5)->get();
            $popularInCategory[] = [
                'title' =>  "Popular in " . $category->name,
                'data' => $movies
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                [
                    'title' => 'Recommended Movies',
                    'data' => $recommendedMovies,
                    'type' => 'movies'
                ],
                [
                    'title' => 'Recommended Series',
                    'data' => $recommendedSeries,
                    'type' => 'series'
                ],
                [
                    'title' => 'Newly Added Movies',
                    'type' => 'movies',
                    'data' => $newAddedMovies
                ],
                [
                    'title' => 'Newly Added Series',
                    'data' => $newAddedSeries,
                    'type' => 'series'
                ],
                [
                    'title' => 'Trending Movies',
                    'data' => $trendingMovies,
                    'type' => 'movies'
                ],
                [
                    'title' => 'Trending Series',
                    'data' => $trendingSeries,
                    'type' => 'series'
                ],

            ] + $popularInCategory
        ], 200);
    }


    // Search for movies and series
    public function search(Request $request)
    {
        $query = $request->query('q');
        $movies = Movie::where('title', 'like', "%$query%")->limit(10)->get();
        $series = Series::where('title', 'like', "%$query%")->limit(10)->get();

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data' => [
                'movies' => $movies,
                'series' => $series
            ]
        ], 200);
    }


    // Get series by id
    public function getSeries($id)
    {
        $series = Series::with('seasons.episodes')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Series retrieved successfully',
            'series' => $series
        ], 200);
    }
}
