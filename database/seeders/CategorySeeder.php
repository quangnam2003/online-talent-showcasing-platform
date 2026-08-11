<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Music',       'slug' => 'music',       'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Dance',       'slug' => 'dance',       'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Art',         'slug' => 'art',         'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Coding',      'slug' => 'coding',      'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Comedy',      'slug' => 'comedy',      'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Photography', 'slug' => 'photography', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
