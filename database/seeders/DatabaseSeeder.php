<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10) // Tạo 10 user
            ->hasPosts(5) // Mỗi user có 5 bài viết
            ->create();
    }
}
