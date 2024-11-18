<?php

namespace App\Services\Source\Vendors;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseApiService
{
    protected mixed $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * available categories
     *
     * @return Collection
     */
    protected function getCategories(): Collection
    {
        return Category::notUnknown()->get();
    }

    /**
     * call api in common place
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return array|null
     */
    protected function makeRequest(string $url, array $params, array $headers = []): ?array
    {
        try {
            $response = empty($headers)
                ? Http::get($url, $params)
                : Http::withHeaders($headers)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("API Error", [
                'url' => $url,
                'params' => $params,
                'status' => $response->status(),
                'response' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error("API Exception", ['message' => $e->getMessage(), 'url' => $url]);
        }

        return null;
    }

    abstract public function fetchArticles(): array;
    abstract protected function processArticles(array $articles, int $categoryId): array;
}
