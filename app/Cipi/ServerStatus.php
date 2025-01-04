<?php

namespace App\Cipi;

use App\Helpers\ScriptHelper;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ServerStatus
{
    public static function getCpuStatus()
    {
        return Str::of(
            Process::run('sh '.ScriptHelper::shFile('getCpuStatus'))->output()
        )->trim();
    }

    public static function getRamStatus()
    {
        return Str::of(
            Process::run('sh '.ScriptHelper::shFile('getRamStatus'))->output()
        )->trim();
    }

    public static function getHddStatus()
    {
        return Str::of(
            Process::run('sh '.ScriptHelper::shFile('getHddStatus'))->output()
        )->trim();
    }
}
