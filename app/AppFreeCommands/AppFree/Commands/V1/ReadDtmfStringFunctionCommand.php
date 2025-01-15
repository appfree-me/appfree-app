<?php
declare(strict_types=1);


namespace AppFree\AppFreeCommands\AppFree\Commands\V1;

use AppFree\AppFree;
use AppFree\AppFreeCommands\AppFreeDto;

class ReadDtmfStringFunctionCommand extends AppFreeDto
{
    public readonly \Closure $closure;

    public function __construct(\Closure $closure) {
        $this->closure = $closure;
    }

    // todo: typprüfung der argumente mit reflection

    public function __serialize(): array
    {
        return [];
    }

}
