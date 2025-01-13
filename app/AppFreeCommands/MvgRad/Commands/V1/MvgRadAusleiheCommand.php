<?php
declare(strict_types=1);

namespace AppFree\AppFreeCommands\MvgRad\Commands\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class MvgRadAusleiheCommand  extends AppFreeDto {
    public readonly string $radnummer;

    public function __construct(string $radnummer) {
        $this->radnummer = $radnummer;
    }

}
