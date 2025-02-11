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
        $movies = Movie::with('categories', 'tags')->paginate(15);

        if ($request->has('category')) {
            $movies = Movie::with('categories', 'tags')->whereHas('categories', function ($query) use ($request) {
                $query->where('name', $request->category);
            })->paginate(15);
        }

        if ($request->has(key: 'tag')) {
            $movies = Movie::with('categories', 'tags')->whereHas('tags', function ($query) use ($request) {
                $query->where('name', $request->tag);
            })->paginate(15);
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
        $series = Series::with('seasons.episodes')->paginate(10);
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
        if ($request->has('poster') && !str_starts_with($request->poster, 'http')) {
            $posterData = base64_decode($request->poster);
            $posterPath = 'posters/' . uniqid() . '.jpg';
            file_put_contents(public_path($posterPath), $posterData);
            $data['poster'] = url($posterPath);
        }
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




    public function storeSeriesData(Request $request)
    {
        try {
            $data = $request->validate([
                'series.name' => 'required|string',
                'series.description' => 'nullable|string',
                'series.coverImage' => 'nullable|string',
                'series.seasons' => 'required|array',
                'series.seasons.*.seasonNumber' => 'required|integer',
                'series.seasons.*.description' => 'nullable|string',
                'series.seasons.*.image' => 'nullable|string',
                'series.seasons.*.episodes' => 'required|array',
                'series.seasons.*.episodes.*.episodeNumber' => 'required|integer',
                'series.seasons.*.episodes.*.title' => 'required|string',
                'series.seasons.*.episodes.*.description' => 'nullable|string',
                'series.seasons.*.episodes.*.thumbnail' => 'nullable|string',
                'series.seasons.*.episodes.*.url' => 'required|string',
            ]);

            $seriesData = $data['series'];
            if (isset($seriesData['coverImage']) && !str_starts_with($seriesData['coverImage'], 'http')) {
                $coverImageData = base64_decode($seriesData['coverImage']);
                // If folder not avaiable create new
                if (!file_exists(public_path('covers'))) {
                    mkdir(public_path('covers'));
                }
                $coverImagePath = 'covers/' . uniqid() . '.jpg';
                file_put_contents(public_path($coverImagePath), $coverImageData);
                $seriesData['coverImage'] = url($coverImagePath);
            }

            $series = Series::create([
                'title' => $seriesData['name'],
                'description' => $seriesData['description'],
                'poster' => $seriesData['coverImage'] ?? null,
            ]);

            foreach ($seriesData['seasons'] as $seasonData) {
                if (isset($seasonData['image']) && !str_starts_with($seasonData['image'], 'http')) {
                    $seasonImageData = base64_decode($seasonData['image']);
                    if (!file_exists(public_path('seasons'))) {
                        mkdir(public_path('seasons'));
                    }
                    $seasonImagePath = 'seasons/' . uniqid() . '.jpg';
                    file_put_contents(public_path($seasonImagePath), $seasonImageData);
                    $seasonData['image'] = url($seasonImagePath);
                }

                $season = $series->seasons()->create([
                    'season_number' => $seasonData['seasonNumber'],
                    'description' => $seasonData['description'],
                    'image' => $seasonData['image'] ?? null,
                ]);

                foreach ($seasonData['episodes'] as $episodeData) {
                    $newThumbnail = $episodeData['thumbnail'] ?? null;
                    if (isset($episodeData['thumbnail']) && !str_starts_with($episodeData['thumbnail'], 'http')) {
                        $thumbnailData = base64_decode($episodeData['thumbnail']);
                        if (!file_exists(public_path('thumbnails'))) {
                            mkdir(public_path('thumbnails'));
                        }
                        $thumbnailPath = 'thumbnails/' . uniqid() . '.jpg';
                        file_put_contents(public_path($thumbnailPath), $thumbnailData);
                        $newThumbnail = url($thumbnailPath);
                    }

                    $season->episodes()->create([
                        'episode_number' => $episodeData['episodeNumber'],
                        'title' => $episodeData['title'],
                        'description' => $episodeData['description'],
                        'image_url' => $newThumbnail,
                        'episode_url' => $episodeData['url'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Series, seasons, and episodes created successfully',
                'series' => $series->load('seasons.episodes')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    // Update series, seasons, and episodes
    public function updateSeriesData(Request $request, $seriesId)
    {
        try {
            $data = $request->validate([
                'series.name' => 'required|string',
                'series.description' => 'nullable|string',
                'series.coverImage' => 'nullable|string',
                'series.seasons' => 'required|array',
                'series.seasons.*.seasonNumber' => 'required|integer',
                'series.seasons.*.description' => 'nullable|string',
                'series.seasons.*.image' => 'nullable|string',
                'series.seasons.*.episodes' => 'required|array',
                'series.seasons.*.episodes.*.episodeNumber' => 'required|integer',
                'series.seasons.*.episodes.*.title' => 'required|string',
                'series.seasons.*.episodes.*.description' => 'nullable|string',
                'series.seasons.*.episodes.*.thumbnail' => 'nullable|string',
                'series.seasons.*.episodes.*.url' => 'required|string',
            ]);

            $seriesData = $data['series'];
            $series = Series::findOrFail($seriesId);

            if (isset($seriesData['coverImage']) && !str_starts_with($seriesData['coverImage'], 'http')) {
                $coverImageData = base64_decode($seriesData['coverImage']);
                if (!file_exists(public_path('covers'))) {
                    mkdir(public_path('covers'));
                }
                $coverImagePath = 'covers/' . uniqid() . '.jpg';
                file_put_contents(public_path($coverImagePath), $coverImageData);
                $seriesData['coverImage'] = url($coverImagePath);
            }

            $series->update([
                'title' => $seriesData['name'],
                'description' => $seriesData['description'],
                'poster' => $seriesData['coverImage'] ?? $series->poster,
            ]);

            foreach ($seriesData['seasons'] as $seasonData) {
                $season = $series->seasons()->where('season_number', $seasonData['seasonNumber'])->first();

                if (!$season) {
                    $season = $series->seasons()->create([
                        'season_number' => $seasonData['seasonNumber'],
                        'description' => $seasonData['description'],
                        'image' => $seasonData['image'] ?? null,
                    ]);
                } else {
                    if (isset($seasonData['image']) && !str_starts_with($seasonData['image'], 'http')) {
                        $seasonImageData = base64_decode($seasonData['image']);
                        if (!file_exists(public_path('seasons'))) {
                            mkdir(public_path('seasons'));
                        }
                        $seasonImagePath = 'seasons/' . uniqid() . '.jpg';
                        file_put_contents(public_path($seasonImagePath), $seasonImageData);
                        $seasonData['image'] = url($seasonImagePath);
                    }

                    $season->update([
                        'description' => $seasonData['description'],
                        'image' => $seasonData['image'] ?? $season->image,
                    ]);
                }

                foreach ($seasonData['episodes'] as $episodeData) {
                    $episode = $season->episodes()->where('episode_number', $episodeData['episodeNumber'])->first();

                    if (!$episode) {
                        $season->episodes()->create([
                            'episode_number' => $episodeData['episodeNumber'],
                            'title' => $episodeData['title'],
                            'description' => $episodeData['description'],
                            'thumbnail' => $episodeData['thumbnail'] ?? null,
                            'url' => $episodeData['url'],
                        ]);
                    } else {
                        if (isset($episodeData['thumbnail']) && !str_starts_with($episodeData['thumbnail'], 'http')) {
                            $thumbnailData = base64_decode($episodeData['thumbnail']);
                            if (!file_exists(public_path('thumbnails'))) {
                                mkdir(public_path('thumbnails'));
                            }
                            $thumbnailPath = 'thumbnails/' . uniqid() . '.jpg';
                            file_put_contents(public_path($thumbnailPath), $thumbnailData);
                            $episodeData['thumbnail'] = url($thumbnailPath);
                        }

                        $episode->update([
                            'title' => $episodeData['title'],
                            'description' => $episodeData['description'],
                            'thumbnail' => $episodeData['thumbnail'] ?? $episode->thumbnail,
                            'url' => $episodeData['url'],
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Series, seasons, and episodes updated successfully',
                'series' => $series->load('seasons.episodes')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
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
                'title' => "Popular in " . $category->name,
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

    public function deleteMovie($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json([
            'success' => true,
            'message' => 'Movie deleted successfully'
        ], 200);
    }

    public function updateMovie(Request $request, $id)
    {
        try {
            $data = $request->only([
                'title',
                'description',
                'director',
                'producer',
                'release_year',
                'rating',
                'poster',
                'trailer_url',
                'movie_url',
                'categories',
                'tags',
                'status'
            ]);

            $movie = Movie::findOrFail($id);
            if ($request->has('poster') && !str_starts_with($request->poster, 'http')) {
                $posterData = base64_decode($request->poster);
                $posterPath = 'posters/' . uniqid() . '.jpg';
                file_put_contents(public_path($posterPath), $posterData);
                $data['poster'] = url($posterPath);
            }
            $movie->update($data);
            if ($request->has('categories')) {
                // Create categories if they don't exist
                foreach ($request->categories as $category) {
                    Category::firstOrCreate(['name' => $category]);
                }
                $categoryIds = Category::whereIn('name', $request->categories)->pluck('id');
                $movie->categories()->sync($categoryIds);
            }
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
                'message' => 'Movie updated successfully',
                'movie' => $movie
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
