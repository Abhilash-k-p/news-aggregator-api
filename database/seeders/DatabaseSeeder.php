<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!Source::exists()) {
            $this->call(SourceSeeder::class);
        }
        if (!Category::exists()) {
            $this->call(CategorySeeder::class);
        }
    }
}
