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

class GuardianNewsAPICollectorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    protected int $limit;

    /**
     * Create a new job instance.
     */
    public function __construct(int $limit = 50)
    {
        $this->limit = $limit;
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
        $url = 'https://content.guardianapis.com/search?from-date='. $today.'&show-fields=byline,publication,body&api-key=c8a6414a-04e5-4444-a668-36eacb9c5220';
        $response = Http::get($url);
        $articles = $response->json() ?? [];
        $resApi = $articles['response']['results'] ?? $articles;

        return $resApi;
    }

    /**
     * @param $results
     * @return void
     */
    private function saveNews($articles): void
    {
        foreach ($articles as $articleData) {

            $publishedAt = \Carbon\Carbon::parse($articleData['webPublicationDate'])->format('Y-m-d H:i:s');
           
            try {
                Article::updateOrCreate(
                    ['title' => $articleData['webTitle']], 
                    [
                        'content' => isset($articleData['fields']['body']) ? 
                                        strip_tags($articleData['fields']['body']) : 'No content available',
                        'category' => $articleData['sectionId'] ?? 'general',
                        'source' =>  $articleData['fields']['publication'] ?? "",
                        'author' =>  $articleData['fields']['byline'] ?? "",
                        'published_at' => $publishedAt,
                    ]
                );

            } catch (\Exception $e) {

                continue;
            }
        }
    }
}