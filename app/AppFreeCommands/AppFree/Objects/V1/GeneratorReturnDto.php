<?php
declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;
use Closure;

class GeneratorReturnDto  extends AppFreeDto {
    public readonly ?Closure $fn;
    public readonly ?AppFreeDto $expectedDto;

    public function __construct(?AppFreeDto $expectedDto, ?callable $fn) {
        $this->expectedDto = $expectedDto;
        $this->fn = $fn;
    }

}
