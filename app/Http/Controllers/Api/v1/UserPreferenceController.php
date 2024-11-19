<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserPreferenceController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make ($request->all(), [
            'sources' => 'array|min:1',
            'sources.*' => 'exists:sources,id',
            'categories' => 'array|min:1',
            'categories.*' => 'exists:categories,id',
            'authors' => 'array|min:1',
            'authors.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors()->all(), 422);
        }

        $user = Auth::user();

        $preferences = UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_sources' => $request->get('sources', []),
                'preferred_categories' => $request->get('categories', []),
                'preferred_authors' => $request->get('authors', []),
            ]
        );

        return $this->sendResponse($preferences, 'Preferences updated successfully.');
    }

    /**
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            $preferences = UserPreference::where('user_id', $user->id)->first();

            if (!$preferences) {
                return $this->sendError('Preferences not found.');
            }

            return $this->sendResponse($preferences, 'Preferences retrieved successfully.');

        } catch (\Throwable $exception) {
            Log::error('Error while getting Preferences: ' . $exception->getMessage());
            return $this->sendError('PrError while getting Preferences', code: 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPersonalizedFeed(Request $request): JsonResponse
    {
        $user = Auth::user();
        $userPreference = UserPreference::where('user_id', $user->id)->first();

        // Check if the user has preferences
        if (!$userPreference->preferred_sources && !$userPreference->preferred_categories && !$userPreference->preferred_authors) {
            return response()->json([
                'message' => 'No preferences found. Please set your preferences to view personalized articles.'
            ], 404);
        }

        // Build the query
        $query = Article::query();

        // Filter by sources
        if ($userPreference->preferred_sources) {
            $query->whereIn('source_id', $userPreference->preferred_sources);
        }

        // Filter by categories
        if ($userPreference->preferred_categories) {
            $query->whereIn('category_id', $userPreference->preferred_categories);
        }

        // Filter by authors
        if (!empty($userPreference->preferred_authors)) {
            $query->where(function ($subQuery) use ($userPreference) {
                foreach ($userPreference->preferred_authors as $author) {
                    $subQuery->orWhere('author', 'like', "%{$author}%");
                }
            });
        }

        $articles = $query->latest()->paginate(10);


        return $this->sendResponse($articles, 'User Preferred Articles retrieved successfully.');
    }
}
