<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Commands\StateMachine\V1;

use AppFree\AppFreeCommands\AppFreeDto;
use Closure;

class WatchdogWebsocketPing extends AppFreeDto
{
    public function __construct(
        public readonly Closure $createResponse,
    ) {

    }

    // https://github.com/ratchetphp/Pawl/issues/99
    /*
     *         $conn->on('pong', function (FrameInterface $frame) {
            echo date('Y-m-d H:i:s T') . ' PONG:' . PHP_EOL;
        });

     */

    /*
     * https://stitcher.io/blog/php-8-named-arguments
     *


    $input = [
    'age' => 25,
    'name' => 'Brent',
    'email' => 'brent@stitcher.io',
];
    $data = new CustomerData(...$input);
     */



}
