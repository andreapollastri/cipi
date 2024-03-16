<?php

namespace App\Helpers;

class Php
{
    public static function availableVersions()
    {
        return [
            '8.0' => '8.0',
            '8.1' => '8.1',
            '8.2' => '8.2',
            '8.3' => '8.3',
        ];
    }

    public static function defaultVersion()
    {
        return '8.3';
    }
}
