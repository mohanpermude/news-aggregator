<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Article;

/**
 *
 */
class NewsAPICollectorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

   /**
     * @var string
     */
    protected ?string $apiKey;

    /**
     * @var string
     */
    protected ?string $apiUrl;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        $this->onQueue(QueueType::HIGH);
        $this->apiKey = env('NWS_API_KEY');
        $this->apiUrl = env('NWS_API_URL');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      
        $resApi = $this->callRequest();

        $this->saveNews($resApi['articles']);

    }

    /**
     * @return mixed
     */
    private function callRequest(): mixed
    {
        $today = today()->format('Y-m-d');
        $url = $this->apiUrl.'/v2/everything?q=tesla&from='.$today.'&sortBy=publishedAt&apiKey='.$this->apiKey;
        $response = Http::get($url);
        $articles = $response->json()['articles'] ?? [];
        $resApi['articles'] = $articles;

        return $resApi;
    }

    /**
     * @param $articles
     * @return void
     */
    private function saveNews($articles): void
    {
        foreach ($articles as $articleData) {
            dd($articleData);

            $publishedAt = \Carbon\Carbon::parse($articleData['publishedAt'])->format('Y-m-d H:i:s');
            $maxWords = 30; // Specify your desired word limit
            $content = $this->limitWords($articleData['content'], $maxWords);
            
            try {
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

            } catch (\Exception $e) {

                continue;
            }
        }
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