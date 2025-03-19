<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Commands\StateMachine\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class ReadDtmfStringFunctionCommand extends AppFreeDto
{
    public function __construct(public readonly int $length, public readonly \Closure $callback)
    {
    }

    // todo: typprüfung der argumente mit reflection

    // Closure type cannot be serialized!

}
