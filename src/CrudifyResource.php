<?php

namespace Taha\Crudify;

use Illuminate\Http\Resources\Json\JsonResource;

class CrudifyResource extends JsonResource
{
    public function toArray($request)
    {
        if (is_null($this->resource)) {
            return [];
        }

        $resource = $this->resource;

        $appends = $request->query('append', '');
        if ($appends !== '' && !is_array($resource)) {
            foreach (explode(',', $appends) as $append) {
                $resource->append(trim($append));
            }
        }

        $visibles = $request->query('visible', '');
        if ($visibles !== '' && !is_array($resource)) {
            foreach (explode(',', $visibles) as $visible) {
                $resource->makeVisible(trim($visible));
            }
        }

        return is_array($resource) ? $resource : $resource->toArray();
    }
}
