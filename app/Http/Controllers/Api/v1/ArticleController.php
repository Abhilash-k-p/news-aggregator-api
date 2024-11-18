<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10); // Default is 10 items per page
            $articles = Article::query()
                ->with(['category', 'source']) // Include related models if needed
                ->orderBy('published_at', 'desc') // Sort by published date
                ->paginate($perPage);

            return $this->sendResponse($articles, 'Articles fetched successfully');
        } catch (\Throwable $th) {
            Log::error("Error fetching articles: {$th->getMessage()}");
            return $this->sendError('Failed to fetch articles. Please try again later.', code: 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
        ]);

        $articles = Article::query()
            ->with(['category', 'source']) // Eager load relationships
            ->filterByKeyword($validated['keyword'] ?? null)
            ->filterByPublishedDate($validated['date'] ?? null)
            ->filterByAuthor($validated['author'] ?? null)
            ->filterByCategoryName($validated['category'] ?? null)
            ->filterBySourceName($validated['source'] ?? null)
            ->paginate(10); // Paginate the results

        return $this->sendResponse($articles, 'Articles fetched successfully');
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Define a unique cache key
        $cacheKey = "article_{$id}";

        // Attempt to retrieve the article from the cache
        $article = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return Article::with(['category', 'source'])->find($id);
        });

        if (!$article) {
            return response()->json([
                'message' => 'Article not found.'
            ], 404);
        }

        return $this->sendResponse($article, 'Article fetched successfully');
    }
}
