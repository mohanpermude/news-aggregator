<?php

// app/Console/Commands/FetchArticles.php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchArticles extends Command
{
    protected $signature = 'articles:fetch';
    protected $description = 'Fetch articles from different news APIs';

    public function handle()
    {
        $apiUrls = [
            'https://newsapi.org/v2/everything?q=tesla&from=2024-09-27&sortBy=publishedAt&apiKey=ca3633aaf9ad4778b784e5b408eed74c', // Example API
            // Add more API endpoints here
        ];

        foreach ($apiUrls as $url) {
            $response = Http::get($url);
            $articles = $response->json()['articles'] ?? [];
            foreach ($articles as $articleData) {
               
            $publishedAt = \Carbon\Carbon::parse($articleData['publishedAt'])->format('Y-m-d H:i:s');
            $maxWords = 30; // Specify your desired word limit
            $content = $this->limitWords($articleData['content'], $maxWords);
            
                Article::updateOrCreate(
                    ['title' => $articleData['title']], // Ensure unique titles or use another identifier
                    [
                        'content' => $articleData['description'] ??  $content ?? 'No content available',
                        'category' => $articleData['category'] ?? 'general',
                        'source' => $articleData['source']['name'] ?? 'unknown',
                        'author' =>  $articleData['author'] ?? 'unknown',
                        'published_at' => $publishedAt,
                    ]
                );
            }
        }

        $this->info('Articles fetched and stored successfully.');
    }

     /**
     * Limit the number of words in a string.
     *
     * @param string $string
     * @param int $maxWords
     * @return string
     */
    private function limitWords($string, $maxWords)
    {
        $words = explode(' ', $string);
        if (count($words) > $maxWords) {
            return implode(' ', array_slice($words, 0, $maxWords)) . '...'; // Add ellipsis if truncated
        }
        return $string; // Return the original string if within the limit
    }
}
