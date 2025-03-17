<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\MvgRad\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class MvgRadUserInfo extends AppFreeDto
{
    public const ACCOUNT_STATUS_OK = "OK";

    public function __construct(public readonly string $accountStatus)
    {
    }
}
