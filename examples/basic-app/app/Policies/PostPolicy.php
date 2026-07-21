<?php

namespace App\Policies;

use App\Models\Post;
use Illuminate\Foundation\Auth\User;

class PostPolicy
{
    public function readMore(?User $user): bool
    {
        return true;
    }

    public function readOne(?User $user, Post $post): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, Post $post): bool
    {
        return true;
    }

    public function delete(?User $user, Post $post): bool
    {
        return true;
    }

    public function massCreate(?User $user): bool
    {
        return true;
    }

    public function massUpdate(?User $user): bool
    {
        return true;
    }

    public function massDelete(?User $user): bool
    {
        return true;
    }

    public function massCreateOrUpdate(?User $user): bool
    {
        return true;
    }
}
