<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class Channel extends AppFreeDto
{
    public readonly string $id;
    public readonly Caller $caller;

    public function __construct(string $id, Caller $caller)
    {
        $this->id = $id;
        $this->caller = $caller;
    }

}
