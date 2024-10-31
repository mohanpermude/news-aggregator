<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserPreference;

class UserPreferenceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            UserPreference::create([
                'user_id' => $user->id,
                'preferred_sources' => json_encode(['Source 1', 'Source 2']),
                'preferred_categories' => json_encode(['Category 1', 'Category 2']),
                'preferred_authors' => json_encode(['Author 1', 'Author 2']),
            ]);
        }
    }
}
