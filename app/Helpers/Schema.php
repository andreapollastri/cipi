<?php

namespace App\Helpers;

class Schema
{
    public static function force()
    {
        if (config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
