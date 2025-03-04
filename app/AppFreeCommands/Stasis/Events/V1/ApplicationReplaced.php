<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Stasis\Events\V1;

use AppFree\AppFreeCommands\AppFreeDto;

/*
 * Description:
Notification that another WebSocket has taken over for an application. An application may only be subscribed to by a single WebSocket at a time. If multiple WebSockets attempt to subscribe to the same application, the newer WebSocket wins, and the older one receives this event.
 *
 * */

class ApplicationReplaced extends AppFreeDto
{
    public function __construct()
    {
    }
}
