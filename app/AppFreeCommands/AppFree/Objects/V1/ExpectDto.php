<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Objects\V1;

use AppFree\AppFreeCommands\AppFreeDto;

class ExpectDto extends AppFreeDto
{
    public readonly AppFreeDto $dto;

    public function __construct(AppFreeDto $dto)
    {
        $this->dto = $dto;
    }

}
