<?php

namespace Database\Seeders;

use Phare\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PostSeeder::class);
    }
}
