<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array of categories to insert
        $categories = [
            ['name' => 'technology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'world', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'sports', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'education', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'travel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'food', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'unknown', 'created_at' => now(), 'updated_at' => now()]
        ];

        // Use DB::insert to insert multiple rows at once
        DB::table('categories')->insert($categories);
    }
}
