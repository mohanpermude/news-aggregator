<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Jobs\NewsAPICollectorJob;
use App\Jobs\GuardianNewsAPICollectorJob;
use App\Jobs\NytNewsCollectorJob;


class FetchArticles extends Command
{
    protected $signature = 'articles:fetch';
    protected $description = 'Fetch articles from different news APIs';

    public function handle()
    {
        NewsAPICollectorJob::dispatch();
        GuardianNewsAPICollectorJob::dispatch();
        NytNewsCollectorJob::dispatch();
        $this->info('Articles fetched and stored successfully.');
    }
}
