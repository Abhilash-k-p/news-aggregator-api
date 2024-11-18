<?php

namespace App\Services\Source\Vendors;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CurrentsApiService extends BaseApiService
{
    /**
     * @return array
     */
    public function fetchArticles(): array
    {
        $articles = [];
        $categories = $this->getCategories();
        $url = "{$this->source->base_url}/latest-news";
        $defaultParams = [
            'apiKey' => $this->source->api_key,
            'page_size' => 10
        ];

        foreach ($categories as $category) {
            $params = array_merge($defaultParams, ['category' => $category->name]);
            $data = $this->makeRequest($url, $params);

            if ($data && isset($data['news'])) {
                $processedArticles = $this->processArticles($data['news'], $category->id);
                $articles = array_merge($articles, $processedArticles);
            } else {
                Log::error("CurrentsApi Error", [
                    'response' => $data ?? 'Unknown error',
                    'params' => $params,
                    'url' => $url,
                ]);
            }
        }

        return $articles;
    }

    /**
     * @param array $articles
     * @param int $categoryId
     * @return array
     */
    public function processArticles(array $articles, int $categoryId): array
    {
        return array_map(function ($article) use ($categoryId) {
            return [
                'title' => $article['title'] ?? null,
                'source_article_id' => $article['id'],
                'content' => $article['description'] ?? '',
                'url' => $article['url'] ?? null,
                'published_at' => Carbon::parse($article['published'])->toDateTimeString() ?? null,
                'category_id' => $categoryId,
                'source_id' => $this->source->id,
                'author' => $article['author'] ?? null,
            ];
        }, $articles);
    }
}
