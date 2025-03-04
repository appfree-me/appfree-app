<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Events\V1;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;

class ChannelDtmfReceived extends AppFreeDto
{
    public readonly string $digit;
    public readonly Channel $channel;

    public function __construct(Channel $channel, string $digit)
    {
        // Wieso gibt es StasisStart Model in Swagger API?
        // Wieso ist das relevant für die HTTP Rest API?
        $this->digit = $digit;
        $this->channel = $channel;
    }
}
