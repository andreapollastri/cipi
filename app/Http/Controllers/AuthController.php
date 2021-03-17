<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * IGNORE-@OA\Post(
     *      path="/auth",
     *      summary="Login endpoint",
     *      tags={"Auth"},
     *      description="Login into Cipi Control Panel using username a password to obtain a JWT token.",
     *      IGNORE-@OA\Parameter(
     *          name="x-csrf-token",
     *          required=true,
     *          in="header",
     *          IGNORE-@OA\Schema(type="string")
     *     ),
     *     IGNORE-@OA\RequestBody(
     *         required=true,
     *         IGNORE-@OA\MediaType(
     *             mediaType="multipart/form-data",
     *             IGNORE-@OA\Schema(
     *                 IGNORE-@OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="User username",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password",
     *                 ),
     *                 required={"username","password"}
     *             )
     *         )
     *     ),
     *     IGNORE-@OA\Response(
     *          response=200,
     *          description="Successful user login",
     *          IGNORE-@OA\JsonContent(
     *              IGNORE-@OA\Property(
     *                  property="access_token",
     *                  type="string",
     *                  example="JwtAccessToken123"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="refresh_token",
     *                  type="string",
     *                  example="JwtRefreshToken123"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="username",
     *                  type="string",
     *                  example="administrator"
     *              ),
     *          )
     *      ),
     *      IGNORE-@OA\Response(
     *          response=419,
     *          description="CSRF Token validation errod"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=422,
     *          description="Payload validation error"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt($request->username, $request->password);

        if (!$user) {
            return response()->json([
                'message' => 'Given credentials are invalid.',
                'errors' => 'Username and password don\'t match.'
            ], 401);
        }

        $user->jwt = JWT::encode(['iat' => time(),'exp' => time() + config('cipi.jwt_refresh')], config('cipi.jwt_secret').'-Rfs');
        $user->save();

        return response()->json([
            'access_token' => JWT::encode(['iat' => time(),'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs'),
            'refresh_token' => $user->jwt,
            'username' => $user->username
        ]);
    }



    /**
     * IGNORE-@OA\Get(
     *      path="/auth",
     *      summary="JWT refresh token endpoint",
     *      tags={"Auth"},
     *      description="Refresh Cipi Control Panel JWT tokens.",
     *      IGNORE-@OA\Parameter(
     *          name="x-csrf-token",
     *          required=true,
     *          in="header",
     *          IGNORE-@OA\Schema(type="string")
     *     ),
     *     IGNORE-@OA\RequestBody(
     *         required=true,
     *         IGNORE-@OA\MediaType(
     *             mediaType="multipart/form-data",
     *             IGNORE-@OA\Schema(
     *                 IGNORE-@OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="User username",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="refresh_token",
     *                      type="string",
     *                      description="JWT refresh token",
     *                 ),
     *                 required={"username","refresh_token"}
     *             )
     *         )
     *     ),
     *     IGNORE-@OA\Response(
     *          response=200,
     *          description="Successful tokens refresh",
     *          IGNORE-@OA\JsonContent(
     *              IGNORE-@OA\Property(
     *                  property="access_token",
     *                  type="string",
     *                  example="JwtAccessToken123"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="refresh_token",
     *                  type="string",
     *                  example="JwtRefreshToken123"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="username",
     *                  type="string",
     *                  example="administrator"
     *              ),
     *          )
     *      ),
     *      IGNORE-@OA\Response(
     *          response=419,
     *          description="CSRF Token validation errod"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=422,
     *          description="Payload validation error"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function refresh(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'refresh_token' => 'required',
        ]);

        $user = Auth::check($request->username, $request->refresh_token);

        if ($user) {
            $user->jwt = JWT::encode(['iat' => time(),'exp' => time() + config('cipi.jwt_refresh')], config('cipi.jwt_secret').'-Rfs');
            $user->save();
            return response()->json([
                'access_token' => JWT::encode(['iat' => time(),'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs'),
                'refresh_token' => $user->jwt,
                'username' => $user->username
            ]);
        } else {
            return response()->json([
                'message' => 'Given token is invalid.',
                'errors' => 'Invalid token.'
            ], 401);
        }
    }


    /**
     * IGNORE-@OA\Patch(
     *      path="/auth",
     *      summary="Patch user profile",
     *      tags={"Auth"},
     *      description="Edit and update user profile.",
     *      IGNORE-@OA\Parameter(
     *          name="x-csrf-token",
     *          required=true,
     *          in="header",
     *          IGNORE-@OA\Schema(type="string")
     *     ),
     *     IGNORE-@OA\RequestBody(
     *         required=true,
     *         IGNORE-@OA\MediaType(
     *             mediaType="multipart/form-data",
     *             IGNORE-@OA\Schema(
     *                 IGNORE-@OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="User username",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="newusername",
     *                      type="string",
     *                      minLength=8,
     *                      maxLength=50,
     *                      description="Set a new user username",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="newpassword",
     *                      type="string",
     *                      minLength=8,
     *                      maxLength=50,
     *                      description="Set a new user password",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="apikey",
     *                      type="bool",
     *                      description="Require a new API Key",
     *                 ),
     *                 required={"username","password"}
     *             )
     *         )
     *     ),
     *     IGNORE-@OA\Response(
     *          response=200,
     *          description="Successful user patch",
     *          IGNORE-@OA\JsonContent(
     *              IGNORE-@OA\Property(
     *                  property="access_token",
     *                  type="string",
     *                  example="JwtAccessToken123"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="refresh_token",
     *                  type="string",
     *                  example="JwtRefreshToken123"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="username",
     *                  type="string",
     *                  example="administrator"
     *              ),
     *              IGNORE-@OA\Property(
     *                  property="apikey",
     *                  type="string",
     *                  example="ApiKey123"
     *              ),
     *          )
     *      ),
     *      IGNORE-@OA\Response(
     *          response=419,
     *          description="CSRF Token validation errod"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=422,
     *          description="Payload validation error"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=409,
     *          description="Patch parameter conflict"
     *      )
     * )
    */
    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt($request->username, $request->password);

        if (!$user) {
            return response()->json([
                'message' => 'Given credentials are invalid.',
                'errors' => 'Username and password don\'t match.'
            ], 401);
        }

        if ($request->newusername) {
            $request->validate([
                'newusername' => 'required|min:6|max:64'
            ]);

            $newuser = Str::lower($request->newusername);

            if (!Auth::where('username', $newuser)->first()) {
                $user->username = $newuser;
            } else {
                return response()->json([
                    'message' => 'Required username is used into database.',
                    'errors' => 'Username Conflict.'
                ], 409);
            }
        }

        if ($request->newpassword && !Hash::check($request->newpassword, $user->password)) {
            $request->validate([
                'newpassword' => 'required|min:8|max:64'
            ]);
            $user->password = Hash::make($request->newpassword);
        }

        if ($request->apikey) {
            $user->apikey = Str::random(48);
        }

        $user->save();

        return response()->json([
            'access_token' => JWT::encode(['iat' => time(),'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs'),
            'refresh_token' => $user->jwt,
            'username' => $user->username,
            'apikey' => $user->apikey
        ]);
    }


    /**
     * IGNORE-@OA\Delete(
     *      path="/auth",
     *      summary="Logout endpoint",
     *      tags={"Auth"},
     *      description="Logout from Cipi Control Panel.",
     *      IGNORE-@OA\Parameter(
     *          name="x-csrf-token",
     *          required=true,
     *          in="header",
     *          IGNORE-@OA\Schema(type="string")
     *     ),
     *     IGNORE-@OA\RequestBody(
     *         required=true,
     *         IGNORE-@OA\MediaType(
     *             mediaType="multipart/form-data",
     *             IGNORE-@OA\Schema(
     *                 IGNORE-@OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="User username",
     *                 ),
     *                 IGNORE-@OA\Property(
     *                      property="refresh_token",
     *                      type="string",
     *                      description="JWT refresh token",
     *                 ),
     *                 required={"username","refresh_token"}
     *             )
     *         )
     *     ),
     *     IGNORE-@OA\Response(
     *          response=200,
     *          description="Successful user logout",
     *      ),
     *      IGNORE-@OA\Response(
     *          response=419,
     *          description="CSRF Token validation errod"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=422,
     *          description="Payload validation error"
     *      ),
     *      IGNORE-@OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
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
                'message' => 'Given token is invalid.',
                'errors' => 'Invalid token.'
            ], 401);
        }
    }
}
