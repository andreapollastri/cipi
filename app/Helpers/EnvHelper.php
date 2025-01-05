<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EnvHelper
{
    public static function update($envKey, $configKey, $newValue)
    {
        if (! $envKey || ! $configKey || ! $newValue) {
            return false;
        }

        $env = Str::replace(
            $envKey.'="'.config($configKey).'"',
            $envKey.'="'.$newValue.'"',
            file_get_contents(base_path('.env'))
        );

        unlink(base_path('.env'));
        file_put_contents(base_path('.env'), $env);

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }
}
