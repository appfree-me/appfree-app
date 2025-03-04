<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Expectations;

use AppFree\AppFreeCommands\AppFreeDto;

abstract class Expectation extends AppFreeDto
{
    abstract public function hasMatch(AppFreeDto $inputDto): bool;
}
