<?php

declare(strict_types=1);

namespace AppFree\ErrorHandling\Constants;

class ErrorIds
{
    public const E_WATCHDOG_COULD_NOT_SAVE = "Could not save WatchdogLog to DB";
    public const E_WATCHDOG_COULD_MAKE_DTO = "Could not construct PingPongDto from Payload";
}
