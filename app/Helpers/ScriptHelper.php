<?php

namespace App\Helpers;

class ScriptHelper
{
    public static function shFile($script)
    {
        return storage_path('app/scripts/'.$script.'.sh');
    }
}
