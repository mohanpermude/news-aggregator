<?php

// app/Http/Controllers/UserPreferenceController.php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Article;

class UserPreferenceController extends Controller
{
    // Set user preferences
    /**
     * @OA\Post(
     *     path="/api/preferences",
     *     tags={"User Preferences"},
     *     summary="Set user preferences",
     *     description="Allows the authenticated user to set their preferred news sources, categories, and authors.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string"), description="Array of preferred news sources"),
     *             @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string"), description="Array of preferred news categories"),
     *             @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string"), description="Array of preferred authors")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User preferences updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserPreference")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferred_sources' => 'nullable|array',
            'preferred_categories' => 'nullable|array',
            'preferred_authors' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
     
        $preferences = UserPreference::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'preferred_sources' => json_encode($request->input('preferred_sources')),
                'preferred_categories' => json_encode($request->input('preferred_categories')),
                'preferred_authors' => json_encode($request->input('preferred_authors')),
            ]
        );

         return response()->json($preferences, 200);
    }

    // Get user preferences
    /**
     * @OA\Get(
     *     path="/api/preferences",
     *     tags={"User Preferences"},
     *     summary="Get user preferences",
     *     description="Fetches the authenticated user's news preferences, including preferred sources, categories, and authors.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User preferences retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserPreference")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show()
    {
        $preferences = UserPreference::where('user_id', Auth::id())->first();
        return response()->json($preferences);
    }

    // Fetch personalized news feed
    /**
     * @OA\Get(
     *     path="/api/personalized-feed",
     *     tags={"User Preferences"},
     *     summary="Fetch personalized news feed",
     *     description="Returns a paginated list of articles based on the user's preferences.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Personalized news feed fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="links", type="object", description="Pagination links"),
     *             @OA\Property(property="meta", type="object", description="Pagination metadata")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function personalizedFeed()
    {
        $preferences = UserPreference::where('user_id', Auth::id())->first();
    
        $query = Article::query();
    
        if ($preferences) {
            // Decode JSON strings back into arrays
            $preferredSources = json_decode($preferences->preferred_sources, true);
            $preferredCategories = json_decode($preferences->preferred_categories, true);
    
            // Check if decoded values are arrays
            if (is_array($preferredSources)) {
                $query->whereIn('source', $preferredSources);
            }
            if (is_array($preferredCategories)) {
                $query->whereIn('category', $preferredCategories);
            }
        }
    
        $articles = $query->paginate(10);

        return response()->json($articles);
    }
}
