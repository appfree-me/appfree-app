<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class Playback extends AppFreeDto
{
    public readonly string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

}
