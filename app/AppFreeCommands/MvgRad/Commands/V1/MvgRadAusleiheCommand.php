<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\MvgRad\Commands\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class MvgRadAusleiheCommand extends AppFreeDto
{
    public function __construct(
        public readonly string $radnummer,
        public readonly ?string $setPin = null,
    ) {
    }

}
