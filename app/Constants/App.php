<?php

declare(strict_types=1);

namespace AppFree\Constants;

class App
{
    public static function name(): string
    {
        return "appfree-app_" . config("app.env");
    }
}
