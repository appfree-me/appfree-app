<?php

declare(strict_types=1);

namespace AppFree\Helpers;

class Helpers
{
    public static function makeAsteriskId(int $bytes = 4): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
