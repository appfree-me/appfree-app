<?php

declare(strict_types=1);

namespace AppFree\Ari\Interfaces;

use AppFree\AppFreeCommands\AppFreeDto;
use Swagger\Client\Model\ModelInterface;

interface EventReceiverInterface
{
    public function receive(AppFreeDto $eventDto): void;
}
