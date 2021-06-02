<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Jobs\CronSSH;
use App\Models\Server;
use App\Jobs\PhpCliSSH;
use phpseclib3\Net\SSH2;
use App\Jobs\RootResetSSH;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\PanelDomainAddSSH;
use App\Jobs\PanelDomainSslSSH;
use App\Jobs\PanelDomainRemoveSSH;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ServerController extends Controller
{

    /**
     * List all servers
     *
     * @OA\Get(
     *      path="/api/servers",
     *      summary="List all servers",
     *      tags={"Servers"},
     *      description="List all servers managed by panel.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="name",
     *                    description="Server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="ip",
     *                    description="Server IP",
     *                    type="string",
     *                    example="123.123.123.123",
     *                ),
     *                @OA\Property(
     *                    property="provider",
     *                    description="Server provider",
     *                    type="string",
     *                    example="AWS",
     *                ),
     *                @OA\Property(
     *                    property="location",
     *                    description="Server location",
     *                    type="string",
     *                    example="Frankfurt"
     *                ),
     *                @OA\Property(
     *                    property="php",
     *                    description="Server PHP CLI version",
     *                    type="string",
     *                    example="7.4"
     *                ),
     *                @OA\Property(
     *                    property="default",
     *                    description="Server default status (panel server)",
     *                    type="boolean",
     *                    example="false"
     *                ),
     *                @OA\Property(
     *                    property="status",
     *                    description="Server installation status (0 not installed, 1 installed)",
     *                    type="integer",
     *                    example="1"
     *                ),
     *                @OA\Property(
     *                    property="sites",
     *                    description="The number of sites on this server",
     *                    type="integer",
     *                    example="12"
     *                ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function index()
    {
        $servers = Server::all();
        $response = [];

        foreach ($servers as $server) {
            $data = [
                'server_id' => $server->server_id,
                'name'      => $server->name,
                'ip'        => $server->ip,
                'provider'  => $server->provider,
                'location'  => $server->location,
                'default'   => $server->default,
                'status'    => $server->status,
                'sites'     => count($server->sites)
            ];
            array_push($response, $data);
        }

        return response()->json($response);
    }


    /**
     * Add a new server
     *
     * @OA\Post(
     *      path="/api/servers",
     *      summary="Add a new Server",
     *      tags={"Servers"},
     *      description="Add a new server to manage with panel.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Server creation payload",
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *             ),
     *             @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Production Server",
     *                  minLength=3
     *             ),
     *             @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="Digital Ocean",
     *             ),
     *             @OA\Property(
     *                  property="location",
     *                  description="Server location",
     *                  type="string",
     *                  example="Amsterdam",
     *             ),
     *             required={"ip","name"}
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful server creation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="location",
     *                  description="Server location",
     *                  type="string",
     *                  example="Frankfurt"
     *              ),
     *              @OA\Property(
     *                  property="setup",
     *                  description="Server setup script",
     *                  type="string",
     *                  example="https://panel.domain.ltd/sh/setup/123456"
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Server conflict"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip'    => 'required|ip',
            'name'  => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('cipi.bad_request'),
                'errors' => $validator->errors()->getMessages()
            ], 400);
        }

        if ($request->ip == $request->server('SERVER_ADDR')) {
            return response()->json([
                'message' => __('cipi.server_conflict_ip_current_message'),
                'errors' => __('cipi.server_conflict')
            ], 409);
        }

        if (Server::where('ip', $request->ip)->first()) {
            return response()->json([
                'message' => __('cipi.server_conflict_ip_duplicate_message'),
                'errors' => __('cipi.server_conflict')
            ], 409);
        }

        $server = new Server();
        $server->ip         = $request->ip;
        $server->name       = $request->name;
        $server->provider   = $request->provider;
        $server->location   = $request->location;
        $server->password   = Str::random(24);
        $server->database   = Str::random(24);
        $server->server_id  = Str::uuid();
        $server->cron       = ' ';
        $server->save();

        return response()->json([
            'server_id'     => $server->server_id,
            'name'          => $request->name,
            'provider'      => $request->provider,
            'location'      => $request->location,
            'ip'            => $request->ip,
            'setup'         => URL::to('/sh/setup/'.$server->server_id)
        ]);
    }


    /**
     * Delete a server
     *
     * @OA\Delete(
     *      path="/api/servers/{server_id}",
     *      summary="Delete a Server",
     *      tags={"Servers"},
     *      description="Delete a server from panel.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to delete.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful server deleted",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function destroy(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message_default'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        if ($server->default) {
            return response()->json([
                'message' => __('cipi.delete_default_server_message'),
                'errors' => __('cipi.bad_request')
            ], 400);
        }

        $server->delete();

        return response()->json([]);
    }


    /**
     * Server information
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}",
     *      summary="Server information",
     *      tags={"Servers"},
     *      description="Get server information.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful server information",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="default",
     *                  description="Server default status (panel server)",
     *                  type="boolean",
     *                  example="false"
     *              ),
     *              @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4"
     *              ),
     *              @OA\Property(
     *                  property="github_key",
     *                  description="Server Github deploy key",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="build",
     *                  description="Server build version",
     *                  type="integer",
     *                  example="20210317001"
     *              ),
     *              @OA\Property(
     *                  property="cron",
     *                  description="Server cron",
     *                  type="text",
     *              ),
     *               @OA\Property(
     *                    property="sites",
     *                    description="The number of sites on this server",
     *                    type="integer",
     *                    example="12"
     *                ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function show(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        return response()->json([
            'sever_id'  => $server->server_id,
            'name'      => $server->name,
            'ip'        => $server->ip,
            'location'  => $server->location,
            'provider'  => $server->provider,
            'default'   => $server->default,
            'php'       => $server->php,
            'github_key'=> $server->github_key,
            'build'     => $server->build,
            'cron'      => $server->cron,
            'sites'     => count($server->sites)
        ]);
    }


    /**
     * Panel server information
     *
     * @OA\Get(
     *      path="/api/servers/panel",
     *      summary="Panel server information",
     *      tags={"Servers"},
     *      description="Get panel server information.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful server information",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="domain",
     *                  description="Server default domain for panel",
     *                  type="string",
     *                  example="panel.domain.ltd"
     *              ),
     *              @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4"
     *              ),
     *              @OA\Property(
     *                  property="github_key",
     *                  description="Server Github deploy key",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="build",
     *                  description="Server build version",
     *                  type="integer",
     *                  example="20210317001"
     *              ),
     *              @OA\Property(
     *                  property="cron",
     *                  description="Server cron",
     *                  type="text",
     *              ),
     *               @OA\Property(
     *                    property="sites",
     *                    description="The number of sites on this server",
     *                    type="integer",
     *                    example="12"
     *                ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     * )
    */
    public function panel()
    {
        $server = Server::where('default', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_native_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        $site = Site::where('server_id', $server->id)->where('panel', 1)->first();

        if (!$site) {
            $domain = '';
        } else {
            $domain = $site->domain;
        }

        return response()->json([
            'sever_id'  => $server->server_id,
            'name'      => $server->name,
            'ip'        => $server->ip,
            'location'  => $server->location,
            'provider'  => $server->provider,
            'domain'    => $domain,
            'php'       => $server->php,
            'github_key'=> $server->github_key,
            'build'     => $server->build,
            'cron'      => $server->cron,
            'sites'     => count($server->sites)
        ]);
    }



    /**
     * Add a domain / subdomain to panel
     *
     * @OA\Patch(
     *      path="/api/servers/panel/domain",
     *      summary="Add a domain / subdomain to panel",
     *      tags={"Servers"},
     *      description="Add a domain / subdomain to panel.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Panel domain payload",
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="domain",
     *                  description="Panel domain",
     *                  type="string",
     *                  example="panel.domain.ltd",
     *             ),
     *          )
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful panel domain update",
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     * )
    */
    public function paneldomain(Request $request)
    {
        $server = Server::where('default', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_native_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        $site = Site::where('server_id', $server->id)->where('panel', true)->first();
        if ($site) {
            $site->delete();
            PanelDomainRemoveSSH::dispatch($server)->delay(Carbon::now()->addSeconds(3));
        }

        if ($request->domain && $request->domain != '') {
            $validator = Validator::make($request->all(), [
                'domain'    => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('cipi.bad_request'),
                    'errors' => $validator->errors()->getMessages()
                ], 400);
            }
            $newsite = new Site;
            $newsite->server_id = $server->id;
            $newsite->domain = $request->domain;
            $newsite->site_id = sha1(microtime());
            $newsite->username = md5(microtime());
            $newsite->password = 'Secret_123';
            $newsite->database = 'Secret_123';
            $newsite->panel = true;
            $newsite->save();
            PanelDomainAddSSH::dispatch($server)->delay(Carbon::now()->addSeconds(3));
        }

        return response()->json([]);
    }


    /**
     * Require SSL for panel
     *
     * @OA\Post(
     *      path="/api/servers/panel/ssl",
     *      summary="Require SSL for panel",
     *      tags={"Servers"},
     *      description="Require SSL for panel domain / subdomain.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful SSL generation"
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     * )
    */
    public function panelssl()
    {
        $server = Server::where('default', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_native_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        $site = Site::where('server_id', $server->id)->where('panel', true)->first();

        if ($site) {
            PanelDomainSslSSH::dispatch($server, $site)->delay(Carbon::now()->addSeconds(3));
        } else {
            return response()->json([
                'message' => __('cipi.ssl_request_error_message'),
                'errors' => __('cipi.bad_request')
            ], 400);
        }

        return response()->json([]);
    }


    /**
     * Server edit
     *
     * @OA\Patch(
     *      path="/api/servers/{server_id}",
     *      summary="Server edit",
     *      tags={"Servers"},
     *      description="Edit server information.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to edit.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Server creation payload",
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *             ),
     *             @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Production Server",
     *                  minLength=3
     *             ),
     *             @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="Digital Ocean",
     *             ),
     *             @OA\Property(
     *                  property="location",
     *                  description="Server location",
     *                  type="string",
     *                  example="Amsterdam",
     *             ),
     *             @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4",
     *             ),
     *             @OA\Property(
     *                  property="cron",
     *                  description="Server crontab",
     *                  type="text",
     *             ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful server editing",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="default",
     *                  description="Server default status (panel server)",
     *                  type="boolean",
     *                  example="false"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="Server status",
     *                  type="integer",
     *                  example="1"
     *              ),
     *              @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4"
     *              ),
     *              @OA\Property(
     *                  property="github_key",
     *                  description="Server Github deploy key",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="build",
     *                  description="Server build version",
     *                  type="integer",
     *                  example="20210317001"
     *              ),
     *              @OA\Property(
     *                  property="cron",
     *                  description="Server cron",
     *                  type="text",
     *              ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Server conflict"
     *      ),
     * )
    */
    public function edit(Request $request, string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        if ($request->ip) {
            $validator = Validator::make($request->all(), [
                'ip'    => 'required|ip'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('cipi.bad_request'),
                    'errors' => $validator->errors()->getMessages()
                ], 400);
            }
            if (!$server->default && $request->ip == str_replace("\n", '', file_get_contents('https://checkip.amazonaws.com'))) {
                return response()->json([
                    'message' => __('cipi.edit_server_current_ip_error_message'),
                    'errors' => __('cipi.server_conflict')
                ], 409);
            }
            if (Server::where('ip', $request->ip)->where('server_id', '<>', $server_id)->first()) {
                return response()->json([
                    'message' => __('cipi.server_conflict_ip_duplicate_message'),
                    'errors' => __('cipi.server_conflict')
                ], 409);
            }
            if ($server->default) {
                $server->ip = str_replace("\n", '', file_get_contents('https://checkip.amazonaws.com'));
            } else {
                $server->ip = $request->ip;
            }
        }

        if ($request->name) {
            $validator = Validator::make($request->all(), [
                'name'   => 'required|min:3'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('cipi.bad_request'),
                    'errors' => $validator->errors()->getMessages()
                ], 400);
            }
            $server->name = $request->name;
        }

        if ($request->provider) {
            $server->provider = $request->provider;
        }

        if ($request->location) {
            $server->location = $request->location;
        }

        if ($request->cron) {
            $server->cron = $request->cron;
            $server->save();
            CronSSH::dispatch($server)->delay(Carbon::now()->addSeconds(3));
        }

        if ($request->php) {
            if (!in_array($request->php, config('cipi.phpvers'))) {
                return response()->json([
                    'message' => __('cipi.bad_request'),
                    'errors' => 'Invalid PHP version.'
                ], 400);
            }
            PhpCliSSH::dispatch($server, $request->php)->delay(Carbon::now()->addSeconds(3));
            $server->php = $request->php;
        }

        $server->save();

        return response()->json([
            'sever_id'  => $server->server_id,
            'name'      => $server->name,
            'ip'        => $server->ip,
            'location'  => $server->location,
            'provider'  => $server->provider,
            'default'   => $server->default,
            'status'    => $server->status,
            'php'       => $server->php,
            'github_key'=> $server->github_key,
            'build'     => $server->build,
            'cron'      => $server->cron
        ]);
    }



    /**
     * Server ping
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/ping",
     *      summary="Server ping",
     *      tags={"Servers"},
     *      description="Check real time server ping.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to check.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful server ping check",
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=503,
     *          description="Server unavailable"
     *      ),
     * )
    */
    public function ping(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        try {
            $remote = Http::get('http://'.$server->ip.'/ping_'.$server->server_id.'.php');
            if ($remote->status() == 200) {
                //
            } else {
                return response()->json([
                    'message' => __('cipi.server_unavailable_message'),
                    'errors' => __('cipi.server_unavailable')
                ], 503);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('cipi.server_unavailable_message'),
                'errors' => __('cipi.server_unavailable')
            ], 503);
        }
    }


    /**
     * Server healthy
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/healthy",
     *      summary="Server healthy",
     *      tags={"Servers"},
     *      description="Check real time server healthy.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to check.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful server healthy check",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="cpu",
     *                  description="Current usage of CPU in %",
     *                  type="float",
     *                  example="72.50"
     *              ),
     *              @OA\Property(
     *                  property="ram",
     *                  description="Current usage of RAM in %",
     *                  type="float",
     *                  example="56.34",
     *              ),
     *              @OA\Property(
     *                  property="hdd",
     *                  description="Current usage of HDD in %",
     *                  type="float",
     *                  example="32",
     *              ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      ),
     * )
    */
    public function healthy(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        try {
            $remote = Http::get('http://'.$server->ip.'/ping_'.$server->server_id.'.php');
            if ($remote->status() != 200) {
                return response()->json([
                    'cpu' => '0',
                    'ram' => '0',
                    'hdd' => '0'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'cpu' => '0',
                'ram' => '0',
                'hdd' => '0'
            ]);
        }

        try {
            $ssh = new SSH2($server->ip, 22);
            if (!$ssh->login('cipi', $server->password)) {
                return response()->json([
                    'message' => __('cipi.server_error_ssh_error_message').$server->server_id,
                    'errors' => __('cipi.server_error')
                ], 500);
            }
            $ssh->setTimeout(360);
            $status = $ssh->exec('echo "`LC_ALL=C top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1}\'`%;`free -m | awk \'/Mem:/ { printf("%3.1f%%", $3/$2*100) }\'`;`df -h / | awk \'/\// {print $(NF-1)}\'`"');
            $ssh->exec('exit');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('cipi.something_error_message'),
                'errors' => __('cipi.error')
            ], 500);
        }

        $status = str_replace('%', '', $status);
        $status = str_replace("\n", '', $status);

        $api = explode(';', $status);

        return response()->json([
            'cpu' => $api[0],
            'ram' => $api[1],
            'hdd' => $api[2]
        ]);
    }

    /**
     * Server root password reset
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/rootreset",
     *      summary="Server root password reset",
     *      tags={"Servers"},
     *      description="Reset server root password (for cipi user).",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful password reset",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="password",
     *                  description="New assigned password for cipi root user",
     *                  type="string",
     *                  example="Secret_123"
     *              ),
     *          )
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     * )
    */
    public function rootreset(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        $last_password = $server->password;
        $new_password = Str::random(24);
        $server->password = $new_password;
        $server->save();

        RootResetSSH::dispatch($server, $new_password, $last_password)->delay(Carbon::now()->addSeconds(1));

        return response()->json([
            'password' => $server->password
        ]);
    }


    /**
     * Server service restart
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/servicerestart/{service}",
     *      summary="Server service restart",
     *      tags={"Servers"},
     *      description="Restart a server server (nginx, php, mysql, redis or supervisor).",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="service",
     *          description="The service to restart.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful service restart"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      ),
     * )
    */
    public function servicerestart(string $server_id, string $service)
    {
        if (!in_array($service, config('cipi.services'))) {
            return response()->json([
                'message' => __('cipi.invalid_service_error_message'),
                'errors' => __('cipi.bad_request')
            ], 400);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        try {
            $ssh = new SSH2($server->ip, 22);
            if (!$ssh->login('cipi', $server->password)) {
                return response()->json([
                    'message' => __('cipi.server_error_ssh_error_message').$server->server_id,
                    'errors' => __('cipi.server_error')
                ], 500);
            }

            $ssh->setTimeout(360);
            switch ($service) {
                case 'nginx':
                    $ssh->exec('sudo systemctl restart nginx.service');
                    break;
                case 'php':
                    $ssh->exec('sudo service php8.0-fpm restart');
                    $ssh->exec('sudo service php7.4-fpm restart');
                    $ssh->exec('sudo service php7.3-fpm restart');
                    break;
                case 'mysql':
                    $ssh->exec('sudo service mysql restart');
                    break;
                case 'redis':
                    $ssh->exec('sudo systemctl restart redis.service');
                    break;
                case 'supervisor':
                    $ssh->exec('service supervisor restart');
                    break;
                default:
                    //
                    break;
            }
            $ssh->exec('exit');

            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('cipi.something_error_message'),
                'errors' => __('cipi.error')
            ], 500);
        }
    }


    /**
     * List all server sites
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/sites",
     *      summary="List all server sites",
     *      tags={"Servers"},
     *      description="List all sites in required server.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                @OA\Property(
     *                    property="site_id",
     *                    description="Site unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="domain",
     *                    description="Main site domain",
     *                    type="string",
     *                    example="domain.ltd"
     *                ),
     *                @OA\Property(
     *                    property="username",
     *                    description="Site username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="php",
     *                    description="Site PHP version",
     *                    type="string",
     *                    example="7.4"
     *                ),
     *                @OA\Property(
     *                    property="basepath",
     *                    description="Site basepath",
     *                    type="string",
     *                    example="/public"
     *                ),
     *                @OA\Property(
     *                    property="aliases",
     *                    description="The number of aliases of this site",
     *                    type="integer",
     *                    example="8"
     *                ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function sites(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        $sites = Site::where('panel', false)->where('server_id', $server->id)->get();
        $response = [];

        foreach ($sites as $site) {
            $data = [
                'site_id'       => $site->site_id,
                'domain'        => $site->domain,
                'username'      => $site->username,
                'php'           => $site->php,
                'basepath'      => $site->basepath,
                'aliases'       => count($site->aliases)
            ];
            array_push($response, $data);
        }

        return response()->json($response);
    }


    /**
     * List all server domains
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/domains",
     *      summary="List all server domains",
     *      tags={"Servers"},
     *      description="List all domains hosted in required server.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successfull response (Domain list array)"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function domains(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (!$server) {
            return response()->json([
                'message' => __('cipi.server_not_found_message'),
                'errors' => __('cipi.server_not_found')
            ], 404);
        }

        $response = [];

        foreach ($server->allsites as $site) {
            array_push($response, $site->domain);
            foreach ($site->aliases as $alias) {
                array_push($response, $alias->domain);
            }
        }

        return response()->json($response);
    }
}
