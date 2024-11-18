<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'currentsApi',
                'base_url' => 'https://api.currentsapi.services/v1',
                'api_key' => 'IVTuMiTilfrc1r0FKpCpDFQVAmc2SWVAVD1N-CuHc4hn3diQ',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'theGuardian',
                'base_url' => 'https://content.guardianapis.com',
                'api_key' => '76e6d97c-94a1-4501-bac9-5080e66f0535',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'newYorkTimes',
                'base_url' => 'https://api.nytimes.com/svc/search/v2',
                'api_key' => 'CBOGntfTGDEAlkInoWHRzovItnof6RAa',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('sources')->insert($sources);
    }
}
