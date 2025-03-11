<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\MvgRad\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class MvgRadSessionToken extends AppFreeDto
{
    public function __construct(public readonly string $token)
    {
    }
}
