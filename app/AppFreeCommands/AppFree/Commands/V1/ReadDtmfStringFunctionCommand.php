<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Commands\V1;

use AppFree\AppFree;
use AppFree\AppFreeCommands\AppFreeDto;

class ReadDtmfStringFunctionCommand extends AppFreeDto
{
    public readonly \Closure $callback;
    public int $dtmfLength;

    public function __construct(int $length, \Closure $callback)
    {
        $this->callback = $callback;
        $this->dtmfLength = $length;
    }

    // todo: typprüfung der argumente mit reflection

    public function __serialize(): array
    {
        return [];
    }

}
