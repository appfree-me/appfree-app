<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class Caller extends AppFreeDto
{
    public readonly string $name;
    public readonly string $number;

    public function __construct(string $name, string $number)
    {
        $this->name = $name;
        $this->number = $number;
    }

}
