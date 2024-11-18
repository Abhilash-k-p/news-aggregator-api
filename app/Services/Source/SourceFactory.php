<?php

namespace App\Services\Source;

use App\Models\Source;
use App\Services\Source\Vendors\GuardianApiService;
use App\Services\Source\Vendors\CurrentsApiService;
use App\Services\Source\Vendors\NytApiService;
use Exception;

class SourceFactory
{
    /**
     * @throws Exception
     */
    public static function getSourceService(Source $source): CurrentsApiService|GuardianApiService|NytApiService
    {
        return match ($source->name) {
            'currentsApi' => new CurrentsApiService($source),
            'theGuardian' => new GuardianApiService($source),
            'newYorkTimes' => new NytApiService($source),
            default => throw new Exception("Source {$source->name} not supported."),
        };
    }
}
