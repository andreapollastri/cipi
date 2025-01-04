<?php

namespace App\Helpers;

class PasswordHelper
{
    public static function default()
    {
        return \Illuminate\Validation\Rules\Password::defaults(function () {
            return self::rules();
        });
    }

    public static function rules()
    {
        return \Illuminate\Validation\Rules\Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }
}
