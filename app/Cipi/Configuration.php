<?php

namespace App\Cipi;

use App\Jobs\UpdateServerName;

class Configuration
{
    public static function updateServerName($name)
    {
        UpdateServerName::dispatch($name);
    }
}
