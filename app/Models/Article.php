<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Article",
 *     type="object",
 *     title="Article",
 *     description="Article model",
 *     @OA\Property(property="id", type="integer", description="Unique identifier for the article"),
 *     @OA\Property(property="title", type="string", description="Title of the article"),
 *     @OA\Property(property="source", type="string", description="Source of the article"),
 *     @OA\Property(property="category", type="string", description="Category of the article"),
 *     @OA\Property(property="content", type="string", description="Content of the article"),
 *     @OA\Property(property="published_at", type="string", format="date-time", description="Published date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last updated date")
 * )
 */
class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'category', 'source', 'author', 'published_at'];
}
