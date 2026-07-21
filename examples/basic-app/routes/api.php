<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;
use App\Models\Tag;

Route::crud('posts', Post::class);
Route::crud('tags', Tag::class);
