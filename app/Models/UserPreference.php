<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserPreference",
 *     type="object",
 *     @OA\Property(property="user_id", type="integer", description="User ID"),
 *     @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string"), description="Preferred news sources"),
 *     @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string"), description="Preferred news categories"),
 *     @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string"), description="Preferred authors")
 * )
 */
class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'preferred_sources', 'preferred_categories', 'preferred_authors'];
    protected $casts = [
        'preferred_sources' => 'array',
        'preferred_categories' => 'array',
        'preferred_authors' => 'array',
    ];
}