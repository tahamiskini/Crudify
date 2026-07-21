<?php

namespace App\Http\Requests;

use Taha\Crudify\CrudifyRequest;

class PostRequest extends CrudifyRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published' => 'boolean',
        ];
    }
}
