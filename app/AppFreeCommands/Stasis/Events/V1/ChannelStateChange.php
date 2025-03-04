<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Events\V1;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;

class ChannelStateChange extends AppFreeDto
{
    public function __construct(public readonly Channel $channel, public readonly string $digit)
    {
        // Wieso gibt es StasisStart Model in Swagger API?
        // Wieso ist das relevant für die HTTP Rest API?
    }
}
