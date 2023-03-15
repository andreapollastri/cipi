<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Auth login via username and password for mobile app
     */
    public function appLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt($request->username, $request->password);

        if (! $user) {
            return response()->json([
                'message' => __('cipi.invalid_login_message'),
                'errors' => __('cipi.invalid_login'),
            ], 401);
        }

        return response()->json([
            'username' => $user->username,
            'apikey' => $user->apikey,
        ]);
    }

    /**
     * JWT Auth login via username and password
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt($request->username, $request->password);

        if (! $user) {
            return response()->json([
                'message' => __('cipi.invalid_login_message'),
                'errors' => __('cipi.invalid_login'),
            ], 401);
        }

        $user->jwt = JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_refresh')], config('cipi.jwt_secret').'-Rfs');
        $user->save();

        return response()->json([
            'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs'),
            'refresh_token' => $user->jwt,
            'username' => $user->username,
        ]);
    }

    /**
     * JWT Auth refresh token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'refresh_token' => 'required',
        ]);

        $user = Auth::check($request->username, $request->refresh_token);

        if ($user) {
            $user->jwt = JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_refresh')], config('cipi.jwt_secret').'-Rfs');
            $user->save();

            return response()->json([
                'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs'),
                'refresh_token' => $user->jwt,
                'username' => $user->username,
            ]);
        } else {
            return response()->json([
                'message' => __('cipi.invalid_token_message'),
                'errors' => __('cipi.invalid_token'),
            ], 401);
        }
    }

    /**
     * Auth profile patch
     */
    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt($request->username, $request->password);

        if (! $user) {
            return response()->json([
                'message' => __('cipi.invalid_login_message'),
                'errors' => __('cipi.invalid_login'),
            ], 401);
        }

        if ($request->newusername) {
            $request->validate([
                'newusername' => 'required|min:6|max:64',
            ]);

            $newuser = Str::lower($request->newusername);

            if (! Auth::where('username', $newuser)->first()) {
                $user->username = $newuser;
            } else {
                return response()->json([
                    'message' => __('cipi.username_conflict_message'),
                    'errors' => __('cipi.username_conflict'),
                ], 409);
            }
        }

        if ($request->newpassword && ! Hash::check($request->newpassword, $user->password)) {
            $request->validate([
                'newpassword' => 'required|min:8|max:64',
            ]);
            $user->password = Hash::make($request->newpassword);
        }

        if ($request->apikey) {
            $user->apikey = Str::random(48);
        }

        $user->save();

        return response()->json([
            'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs'),
            'refresh_token' => $user->jwt,
            'username' => $user->username,
            'apikey' => $user->apikey,
        ]);
    }

    /**
     * JWT Auth sign out
     */
    public function logout(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'refresh_token' => 'required',
        ]);

        $user = Auth::check($request->username, $request->refresh_token);

        if ($user) {
            $user->jwt = null;
            $user->save();
        } else {
            return response()->json([
                'message' => __('cipi.invalid_token_message'),
                'errors' => __('cipi.invalid_token'),
            ], 401);
        }
    }
}
