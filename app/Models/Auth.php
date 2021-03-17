<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Auth extends Model
{
    use HasFactory;

    public static function attempt($username, $password)
    {
        $user = self::where('username', $username)->first();

        if ($user) {
            if (Hash::check($password, $user->password)) {
                return $user;
            }
        }
    }

    public static function check($username, $jwt)
    {
        return self::where('username', $username)
            ->where('jwt', $jwt)
            ->first();
    }
}
