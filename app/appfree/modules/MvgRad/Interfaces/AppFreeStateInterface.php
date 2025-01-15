<?php

namespace AppFree\appfree\modules\MvgRad\Interfaces;

use AppFree\AppFreeCommands\AppFreeDto;
use Finite\State\StateInterface;

interface AppFreeStateInterface extends StateInterface
{
    public function onEvent(AppFreeDto $dto): void;

    public function run(): \Generator;
}
