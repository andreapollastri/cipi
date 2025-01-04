<?php

namespace App\Helpers;

class PhpHelper
{
    public static function defaultVersion()
    {
        return '8.4';
    }

    public static function availableVersions()
    {
        return [
            '8.1' => '8.1',
            '8.2' => '8.2',
            '8.3' => '8.3',
            '8.4' => '8.4',
        ];
    }
}
