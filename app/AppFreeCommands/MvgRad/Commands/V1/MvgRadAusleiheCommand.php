<?php
declare(strict_types=1);

namespace AppFree\AppFreeCommands\MvgRad\Commands\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class MvgRadAusleiheCommand  extends AppFreeDto {
    public readonly string $radnummer;
    public readonly ?string $pin;

    public function __construct(string $radnummer, ?string $setPin = null) {
        $this->radnummer = $radnummer;
        $this->pin = $setPin;
    }

}
