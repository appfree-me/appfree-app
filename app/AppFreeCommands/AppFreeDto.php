<?php
declare(strict_types=1);

namespace AppFree\AppFreeCommands;



use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;

abstract class AppFreeDto
{
     public function getChannel(): ?Channel {
         return $this->channel ?? null;
     }
}

