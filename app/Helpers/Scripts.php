<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class Scripts
{
    public static function shFile($script)
    {
        return storage_path('app/scripts/'.$script.'.sh');
    }

    public static function getCpuStatus()
    {
        return Str::of(
            Process::run('sh '.self::shFile('getCpuStatus'))->output()
        )->trim();
    }

    public static function getRamStatus()
    {
        return Str::of(
            Process::run('sh '.self::shFile('getRamStatus'))->output()
        )->trim();
    }

    public static function getHddStatus()
    {
        return Str::of(
            Process::run('sh '.self::shFile('getHddStatus'))->output()
        )->trim();
    }

    public static function updateServerName($name)
    {
        $env = Str::replace(
            'PANEL_SERVER_NAME="'.config('panel.serverName').'"',
            'PANEL_SERVER_NAME="'.$name.'"',
            file_get_contents(base_path('.env'))
        );

        unlink(base_path('.env'));
        file_put_contents(base_path('.env'), $env);

        $processi = Process::run('php artisan cache:clear');
    }
}
