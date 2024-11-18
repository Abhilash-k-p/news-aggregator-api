<?php

namespace App\Services\Source\Vendors;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NytApiService extends BaseApiService
{
    /**
     * @return array
     */
    public function fetchArticles(): array
    {
        $articles = [];
        $categories = $this->getCategories();
        $url = "{$this->source->base_url}/articlesearch.json";
        $params = [
            'api-key' => $this->source->api_key,
        ];

        foreach ($categories as $category) {
            $params['fq'] = "section_name:\"{$category->name}\"";
            /**
             * Api throwing 503 error without these header, so I have added these
             */
            $data = $this->makeRequest($url, $params, [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8',
                'Cache-Control' => 'max-age=0',
                'Connection' => 'keep-alive',
                'DNT' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Upgrade-Insecure-Requests' => '1',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
                'sec-ch-ua' => '"Chromium";v="130", "Google Chrome";v="130", "Not?A_Brand";v="99"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"macOS"',
                'Cookie' => '_ga=GA1.2.1018739136.1731689854; _gid=GA1.2.130341832.1731689854; nyt-a=gNE7T95hmS9JIuqD3GB-S-; NYT-Edition=edition|INTERNATIONAL; purr-cache=<G_<C_<T0<Tp1_<Tp2_<Tp3_<Tp4_<Tp7_<a0_<K0<S0<r<ua; nyt-gdpr=0; nyt-us=0; nyt-geo=IN; nyt-b3-traceid=1ecbf25c51bd4c5ba8d9c0ae51da0c27; __gads=ID=f43017de4e48c511:T=1731690010:RT=1731783573:S=ALNI_MZ-2k0VqlGYq6qzEb26-_rl_nfW4Q; __gpi=UID=00000f69fb1f9768:T=1731690010:RT=1731783573:S=ALNI_MYIs8HIvxPsT6kMsRZ2HzTOvd0qRg; __eoi=ID=066e070b62551718:T=1731690010:RT=1731783573:S=AA-AfjYm_1ifpVGBajYa0KTKU154; nyt-jkidd=uid=0&lastRequest=1731783574920&activeDays=%5B0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C0%2C1%2C1%5D&adv=2&a7dv=2&a14dv=2&a21dv=2&lastKnownType=anon&newsStartDate=&entitlements=; _cb=wbTklCfgzlqDEPY2R; _chartbeat2=.1731783575176.1731783575176.1.CL26JGDr5IIxD_EUYcCmi4AKDOCn9U.1; _cb_svref=https%3A%2F%2Fwww.google.com%2F; iter_id=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhaWQiOiI2NzM4ZWI5NzZmZmYyNDE3YTkzNDhkMTQiLCJjb21wYW55X2lkIjoiNWMwOThiM2QxNjU0YzEwMDAxMmM2OGY5IiwiaWF0IjoxNzMxNzgzNTc1fQ.EhAwVJFTiWfMasJ3HGIqBWBgKrqiBWIEjWCVCWC9XD4; nyt-tos-viewed=true; purr-pref-agent=<G_<C_<T0<Tp1_<Tp2_<Tp3_<Tp4_<Tp7_<a12; nyt-purr=cfhhpfhhhckfhdfhhgah2; _gat_gtag_UA_132660901_1=1; _gat=1',
            ]);
            if ($data && isset($data['response']['docs'])) {
                $processedArticles = $this->processArticles($data['response']['docs'], $category->id);
                $articles = array_merge($articles, $processedArticles);
            } else {
                Log::error("NytApi Error", [
                    'response' => $data,
                    'params' => $params,
                    'url' => $url
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
                'title' => $article['headline']['main'] ?? null,
                'source_article_id' => $article['_id'],
                'content' => $article['lead_paragraph'] ?? '',
                'url' => $article['web_url'] ?? null,
                'published_at' => Carbon::parse($article['pub_date'])->toDateTimeString() ?? null,
                'category_id' => $categoryId,
                'source_id' => $this->source->id,
                'author' => ($article['byline']['person']['firstname'] ?? null)
                    ? "{$article['byline']['person']['firstname']} {$article['byline']['person']['lastname']}"
                    : null,
            ];
        }, $articles);
    }
}
