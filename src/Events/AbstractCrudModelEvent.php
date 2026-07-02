<?php

namespace Taha\Crudify\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Taha\Crudify\Actions\ActionPayloadInterface;

abstract class AbstractCrudModelEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ActionPayloadInterface $actionPayload,
    ) {
    }
}
