<?php

namespace Taha\Crudify\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class CrudifyPolicy
{
    use HandlesAuthorization;

    public function readOne(?Model $user, Model $model): bool
    {
        return true;
    }

    public function readMore(?Model $user): bool
    {
        return true;
    }

    public function create(?Model $user): bool
    {
        return true;
    }

    public function update(?Model $user, Model $model): bool
    {
        return true;
    }

    public function delete(?Model $user, Model $model): bool
    {
        return true;
    }
}
