<?php

namespace App\Helpers;

class Password
{
    public static function defaults()
    {
        return \Illuminate\Validation\Rules\Password::defaults(function () {
            return self::validation();
        });
    }

    public static function validation()
    {
        return \Illuminate\Validation\Rules\Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }
}
