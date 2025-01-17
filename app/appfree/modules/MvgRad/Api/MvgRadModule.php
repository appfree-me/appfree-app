<?php

namespace AppFree\appfree\modules\MvgRad\Api;

use Swagger\Client\Api\ChannelsApi;
use Swagger\Client\ApiException;

class MvgRadModule
{

    public static function hasLastPin(): bool
    {
        return true;
    }

}
