<?php
declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Events\V1;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Playback;

class PlaybackStarted extends AppFreeDto
{
    public readonly Playback $playback;

    public function __construct(Playback $playback)
    {
        // Wieso gibt es StasisStart Model in Swagger API?
        // Wieso ist das relevant für die HTTP Rest API?
        $this->playback = $playback;
    }
}
