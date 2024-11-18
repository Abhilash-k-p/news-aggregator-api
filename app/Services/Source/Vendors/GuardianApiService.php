<?php

namespace App\Services\Source\Vendors;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GuardianApiService extends BaseApiService
{
    private const CATEGORY_MAPPER = [
        'sports' => 'sport'
    ];

    /**
     * @return array
     */
    public function fetchArticles(): array
    {
        $articles = [];
        $categories = $this->getCategories();
        $params = [
            'api-key' => $this->source->api_key,
            'show-fields' => 'all',
            'type' => 'article'
        ];

        foreach ($categories as $category) {
            $url = $this->source->base_url . '/' . (self::CATEGORY_MAPPER[$category->name] ?? $category->name);
            $data = $this->makeRequest($url, $params);
            if ($data && isset($data['response']['results'])) {
                $processedArticles = $this->processArticles($data['response']['results'], $category->id);
                $articles = array_merge($articles, $processedArticles);
            } else {
                Log::error("GuardianApi Error", [
                    'response' => json_encode($data) ?? 'Unknown error',
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
                'title' => $article['fields']['headline'] ?? null,
                'source_article_id' => $article['id'],
                'content' => $article['fields']['bodyText'] ?? '',
                'url' => $article['webUrl'] ?? null,
                'published_at' => Carbon::parse($article['webPublicationDate'])->toDateTimeString() ?? null,
                'category_id' => $categoryId,
                'source_id' => $this->source->id,
                'author' => $article['fields']['byline'] ?? null,
            ];
        }, $articles);
    }
}
