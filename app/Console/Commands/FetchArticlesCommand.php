<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Source;
use App\Services\Source\SourceFactory;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-articles-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from all sources and update the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sources = Source::all();

        foreach ($sources as $source) {
            $this->info("Fetching articles from {$source->name}");

            try {
                $service = SourceFactory::getSourceService($source);
                $articles = $service->fetchArticles();

                if (!empty($articles)) {
                    Article::upsert($articles, ['source_article_id'], ['title', 'url', 'content', 'category_id', 'published_at', 'source_id']);
                    $this->info("Articles from {$source->name} updated successfully.");
                } else {
                    $this->error("No articles found for {$source->name}");
                }
            } catch (\Exception $e) {
                $this->error("Error fetching articles from {$source->name}: {$e->getMessage()}");
            }
        }
    }
}
