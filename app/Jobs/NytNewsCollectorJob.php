<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Article;

class NytNewsCollectorJob implements ShouldQueue
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
     */
    public function __construct()
    {
        $this->apiKey = env('NYT_API_KEY');
        $this->apiUrl = env('NYT_API_URL');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resApi = $this->callRequest();
        $this->saveNews($resApi);

    }

    /**
     * @return mixed
     */
    private function callRequest(): mixed
    {
        $today = today()->format('Y-m-d');
        $url = $this->apiUrl.'/svc/topstories/v2/home.json?api-key='. $this->apiKey;
        $response = Http::get($url);
    
        $articles = $response->json() ?? [];
        $resApi = $articles['results'] ?? $articles;

        return $resApi;
    }

    /**
     * @param $results
     * @return void
     */
    private function saveNews($articles): void
    {
        foreach ($articles as $articleData) {

            $publishedAt = \Carbon\Carbon::parse($articleData['published_date'])->format('Y-m-d H:i:s');
           
            try {
                Article::updateOrCreate(
                    ['title' => $articleData['title']], 
                    [
                        'content' => $articleData['abstract'] ?? 'No content available',
                        'category' => $articleData['section'] ?? 'general',
                        'source' => "The New York Times",
                        'author' =>  $articleData['byline'] ?? "",
                        'published_at' => $publishedAt,
                    ]
                );
            } catch (\Exception $e) {

                continue;
            }
        }
    }
}