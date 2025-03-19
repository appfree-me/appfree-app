<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Commands\StateMachine\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class WatchdogExecuteApiCall extends AppFreeDto
{
    public function __construct(
        public readonly string $apiFqcn,
        public readonly string $method,
        public readonly array $arguments,
        public readonly string $instanceId
    ) {

    }

}
