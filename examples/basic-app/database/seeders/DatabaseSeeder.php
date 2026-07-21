<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Post::create(['title' => 'Hello World', 'body' => 'First post via Crudify!', 'published' => true]);
        Post::create(['title' => 'Laravel Tips', 'body' => 'Some tips and tricks.', 'published' => true]);
        Post::create(['title' => 'Draft Post', 'body' => 'Not yet published.', 'published' => false]);

        Tag::create(['name' => 'php']);
        Tag::create(['name' => 'laravel']);
        Tag::create(['name' => 'tutorial']);
    }
}
