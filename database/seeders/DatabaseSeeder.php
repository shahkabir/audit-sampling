<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Listing;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\User::factory(10)->create();
         

         //Using model to seed data
         Listing::create([
            'title' => 'This is a title',
            'description' => 'This is a description',
            'email' => 'xyz@email.com',
         ]);

         Listing::create([
            'title' => 'This is a title 2',
            'description' => 'This is a description 2',
            'email' => 'xyz@email.com',
         ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
