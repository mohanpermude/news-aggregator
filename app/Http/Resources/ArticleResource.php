<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

 /**
     * @OA\Schema(
     *     schema="ArticleResource",
     *     type="object",
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="title", type="string"),
     *     @OA\Property(property="source", type="string"),
     *     @OA\Property(property="category", type="string"),
     *     @OA\Property(property="published_at", type="string", format="date-time"),
     * )
*/
class ArticleResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'source' => $this->source,
            'author' => $this->author,
            'published_at' => $this->published_at,
        ];
    }
}

