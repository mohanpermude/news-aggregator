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
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
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
    public function show()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $preferences = UserPreference::where('user_id', Auth::id())->first();
        return response()->json($preferences);
    }

    // Fetch personalized news feed
    public function personalizedFeed()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
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
