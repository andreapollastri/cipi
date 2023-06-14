<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\Alias;
use Firebase\JWT\JWT;
use App\Models\Server;
use App\Jobs\NewSiteSSH;
use App\Jobs\SslSiteSSH;
use App\Jobs\NewAliasSSH;
use App\Jobs\SiteDbPwdSSH;
use App\Jobs\DeleteSiteSSH;
use Illuminate\Support\Str;
use App\Jobs\DeleteAliasSSH;
use App\Jobs\EditSitePhpSSH;
use App\Jobs\SiteUserPwdSSH;
use Illuminate\Http\Request;
use App\Jobs\EditSiteDeploySSH;
use App\Jobs\EditSiteDomainSSH;
use App\Jobs\EditSiteBasepathSSH;
use Barryvdh\DomPDF\Facade as PDF;
use App\Jobs\EditSiteSupervisorSSH;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    /**
     * List all sites
     *
     * @OA\Get(
     *      path="/api/sites",
     *      summary="List all sites",
     *      tags={"Sites"},
     *      description="List all sites managed by panel.",
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
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
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
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function index()
    {
        $sites = Site::where('panel', false)->get();
        $response = [];

        foreach ($sites as $site) {
            $data = [
                'site_id'       => $site->site_id,
                'domain'        => $site->domain,
                'username'      => $site->username,
                'server_id'     => $site->server->server_id,
                'server_name'   => $site->server->name,
                'server_ip'     => $site->server->ip,
                'language'      => $site->language,
                'php'           => $site->php,
                'basepath'      => $site->basepath,
                'aliases'       => count($site->aliases)
            ];
            array_push($response, $data);
        }

        return response()->json($response);
    }




    /**
     * Add a new site
     *
     * @OA\Post(
     *      path="/api/sites",
     *      summary="Add a new site",
     *      tags={"Sites"},
     *      description="Add a new site in panel.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Site creation payload",
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="server_id",
     *                  description="Site server ID",
     *                  type="string",
     *                  example="abc-123-def-456",
     *             ),
     *             @OA\Property(
     *                  property="domain",
     *                  description="Site main domain",
     *                  type="string",
     *                  example="domain.ltd",
     *             ),
     *             @OA\Property(
     *                    property="php",
     *                    description="Site PHP version",
     *                    type="string",
     *                    example="7.4"
     *               ),
     *             @OA\Property(
     *                  property="basepath",
     *                  description="Site basepath",
     *                  type="string",
     *                  example="public"
     *             ),
     *             required={"server_id","domain"}
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *          @OA\JsonContent(
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
     *                    property="password",
     *                    description="Site password",
     *                    type="string",
     *                    example="Secret_123"
     *                ),
     *                @OA\Property(
     *                    property="database",
     *                    description="Site database",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_username",
     *                    description="Site database username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_password",
     *                    description="Site database password",
     *                    type="string",
     *                    example="Secret_123"
     *                ),
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
     *                ),
     *                @OA\Property(
     *                    property="pdf",
     *                    description="Site summary pdf (temp 3 minutes link)",
     *                    type="string",
     *                    example="https://panel.domain.ltd/pdf/123454/1233442"
     *                ),
     *          )
     *      ),
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
     *          description="Site domain conflict"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      )
     * )
    */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'domain'    => 'required',
            'server_id' => 'required',
            'language' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Bad Request.',
                'errors' => $validator->errors()->getMessages()
            ], 400);
        }

        if ($request->php) {
            if (!in_array($request->php, config('cipi.phpvers'))) {
                return response()->json([
                    'message' => 'Bad Request.',
                    'errors' => 'Invalid PHP version.'
                ], 400);
            }
            $php = $request->php;
        } else {
            $php = config('cipi.default_php');
        }

        $server = Server::where('server_id', $request->server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Required server does not exists into panel or it is not installed yet.',
                'errors' => 'Server not found.'
            ], 404);
        }

        $conflict = false;
        foreach ($server->allsites as $site) {
            if ($site->domain == $request->domain) {
                $conflict = true;
                foreach ($site->aliases as $alias) {
                    if ($alias->domain == $request->domain) {
                        $conflict = true;
                    }
                }
            }
        }
        if ($conflict) {
            return response()->json([
                'message' => 'Required domain is used by another site/alias on this server.',
                'errors' => 'Site domain conflict.'
            ], 409);
        }

        $pdftoken = JWT::encode(['iat' => time(),'exp' => time() + 180], config('cipi.jwt_secret').'-Pdf');

        $site_id = Str::uuid();

        $site = new Site();
        $site->site_id    = $site_id;
        $site->server_id  = $server->id;
        $site->domain     = $request->domain;
        $site->language     = $request->language;
        $site->php        = $php;
        $site->basepath   = $request->basepath;
        $site->username   = 'cp'.hash('crc32', (Str::uuid()->toString())).rand(1, 9);
        $site->password   = Str::random(24);
        $site->database   = Str::random(24);
        $site->deploy     = ' ';
        $site->rootpath     = '/home/'.$site->username.'/web';
        $site->save();

        NewSiteSSH::dispatch($server, $site)->delay(Carbon::now()->addSeconds(3));

        return response()->json([
            'site_id'           => $site->site_id,
            'domain'            => $site->domain,
            'username'          => $site->username,
            'password'          => $site->password,
            'database'          => $site->username,
            'database_username' => $site->username,
            'database_password' => $site->database,
            'server_id'         => $server->server_id,
            'server_name'       => $server->name,
            'server_ip'         => $server->ip,
            'php'               => $site->php,
            'basepath'          => $site->basepath,
            'pdf'               => URL::to('/pdf/'.$site_id.'/'. $pdftoken)
        ]);
    }


    /**
     * Edit site information
     *
     * @OA\Patch(
     *      path="/api/sites/{site_id}",
     *      summary="Edit site information",
     *      tags={"Sites"},
     *      description="Edit site information by site_id.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to edit.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Site edit payload",
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="domain",
     *                  description="Site main domain",
     *                  type="string",
     *                  example="domain.ltd",
     *             ),
     *             @OA\Property(
     *                  property="basepath",
     *                  description="Site basepath",
     *                  type="string",
     *                  example="public",
     *             ),
     *             @OA\Property(
     *                  property="php",
     *                  description="PHP FPM version",
     *                  type="string",
     *                  example="8.0",
     *             ),
     *             @OA\Property(
     *                  property="repository",
     *                  description="Github repository",
     *                  type="string",
     *                  example="andreapollastri/cipi",
     *             ),
     *            @OA\Property(
     *                  property="branch",
     *                  description="Git branch",
     *                  type="string",
     *                  example="latest",
     *             ),
     *             @OA\Property(
     *                  property="supervisor",
     *                  description="Supervisor command",
     *                  type="string",
     *                  example="7.4",
     *             ),
     *             @OA\Property(
     *                  property="deploy",
     *                  description="Deploy scripts",
     *                  type="string",
     *             ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *          @OA\JsonContent(
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
     *                    property="database",
     *                    description="Site database",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_username",
     *                    description="Site database username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
     *                ),
     *                @OA\Property(
     *                       property="repository",
     *                       description="Github repository",
     *                       type="string",
     *                       example="andreapollastri/cipi",
     *                 ),
     *                 @OA\Property(
     *                       property="branch",
     *                       description="Git branch",
     *                       type="string",
     *                       example="latest",
     *                 ),
     *                @OA\Property(
     *                    property="deploy",
     *                    description="Deploy custom configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="deploy_key",
     *                    description="Deploy SSH Key",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="supervisor",
     *                    description="Supervisor configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="aliases",
     *                    description="The count of related aliases",
     *                    type="integer",
     *                    example="8",
     *                ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
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
     *          description="Site domain conflict"
     *      ),
     * )
    */
    public function edit(Request $request, string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        if ($request->domain) {
            $validator = Validator::make($request->all(), [
                'domain' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Bad Request.',
                    'errors' => $validator->errors()->getMessages()
                ], 400);
            }

            if ($site->domain != $request->domain) {
                $sites = Site::where('server_id', $site->server->id)->get();

                foreach ($sites as $site) {
                    if ($request->domain == $site->domain) {
                        return response()->json([
                            'message' => 'There is another site with same domain into database.',
                            'errors' => 'Server conflict.'
                        ], 409);
                    }
                    foreach ($site->aliases as $alias) {
                        if ($request->domain == $alias->domain) {
                            return response()->json([
                                'message' => 'There is another alias with same domain into database.',
                                'errors' => 'Server conflict.'
                            ], 409);
                        }
                    }
                }
            }

            $last_domain = $site->domain;
            $site->domain = $request->domain;
            $site->save();

            EditSiteDomainSSH::dispatch($site, $last_domain)->delay(Carbon::now()->addSeconds(1));
        }

        if ($request->has('basepath')) {
            if ($site->basepath != $request->basepath) {
                $last_basepath = $site->basepath;
                $site->basepath = $request->basepath;
                $site->save();
                EditSiteBasepathSSH::dispatch($site, $last_basepath)->delay(Carbon::now()->addSeconds(5));
            }
        }

        if ($request->php) {
            if ($site->php != $request->php) {
                $last_php = $site->php;
                $site->php = $request->php;
                $site->save();
                EditSitePhpSSH::dispatch($site, $last_php)->delay(Carbon::now()->addSeconds(10));
            }
        }

        if ($request->has('supervisor')) {
            if ($site->supervisor != $request->supervisor) {
                $site->supervisor = $request->supervisor;
                $site->save();
                EditSiteSupervisorSSH::dispatch($site, $site->supervisor)->delay(Carbon::now()->addSeconds(15));
            }
        }

        $deploy_patch = false;

        if ($request->deploy) {
            if ($site->deploy != $request->deploy) {
                $site->deploy = $request->deploy;
                $site->save();
                $deploy_patch = true;
            }
        }

        if ($request->repository) {
            if ($site->repository != $request->repository) {
                $site->repository = $request->repository;
                $site->save();
                $deploy_patch = true;
            }
        }

        if ($request->branch) {
            if ($site->branch != $request->branch) {
                $site->branch = $request->branch;
                $site->save();
                $deploy_patch = true;
            }
        }

        if ($deploy_patch) {
            EditSiteDeploySSH::dispatch($site)->delay(Carbon::now()->addSeconds(1));
        }

        $site->save();

        return response()->json([
            'site_id'           => $site->site_id,
            'domain'            => $site->domain,
            'username'          => $site->username,
            'database'          => $site->username,
            'database_username' => $site->username,
            'server_id'         => $site->server->server_id,
            'server_name'       => $site->server->name,
            'server_ip'         => $site->server->ip,
            'php'               => $site->php,
            'basepath'          => $site->basepath,
            'repository'        => $site->repository,
            'branch'            => $site->branch,
            'deploy'            => $site->deploy,
            'deploy_key'        => $site->server->github_key,
            'supervisor'        => $site->supervisor,
            'aliases'           => count($site->aliases)
        ]);
    }



    /**
     * Show site information
     *
     * @OA\Get(
     *      path="/api/sites/{site_id}",
     *      summary="Show site information",
     *      tags={"Sites"},
     *      description="Get site information by site_id.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to show.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *          @OA\JsonContent(
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
     *                    property="database",
     *                    description="Site database",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_username",
     *                    description="Site database username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
     *                ),
     *                @OA\Property(
     *                       property="repository",
     *                       description="Github repository",
     *                       type="string",
     *                       example="andreapollastri/cipi",
     *                 ),
     *                 @OA\Property(
     *                       property="branch",
     *                       description="Git branch",
     *                       type="string",
     *                       example="latest",
     *                 ),
     *                @OA\Property(
     *                    property="deploy",
     *                    description="Deploy custom configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="deploy_key",
     *                    description="Deploy SSH Key",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="supervisor",
     *                    description="Supervisor configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="aliases",
     *                    description="The count of related aliases",
     *                    type="integer",
     *                    example="8",
     *                ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
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
     *          description="Site domain conflict"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      )
     * )
    */
    public function show(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        return response()->json([
            'site_id'           => $site->site_id,
            'domain'            => $site->domain,
            'username'          => $site->username,
            'database'          => $site->username,
            'database_username' => $site->username,
            'server_id'         => $site->server->server_id,
            'server_name'       => $site->server->name,
            'server_ip'         => $site->server->ip,
            'language'          => $site->language,
            'php'               => $site->php,
            'basepath'          => $site->basepath,
            'repository'        => $site->repository,
            'branch'            => $site->branch,
            'deploy'            => $site->deploy,
            'deploy_key'        => $site->server->github_key,
            'supervisor'        => $site->supervisor,
            'rootpath'          => $site->rootpath,
            'aliases'           => count($site->aliases)
        ]);
    }




    /**
     * Delete a Site
     *
     * @OA\Delete(
     *      path="/api/sites/{site_id}",
     *      summary="Delete a Site",
     *      tags={"Sites"},
     *      description="Delete a site from panel.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to delete.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful site deleted",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
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
    public function destroy(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        if ($site->panel) {
            return response()->json([
                'message' => 'Cannot delete default site from panel.',
                'errors' => 'Bad Request.'
            ], 400);
        }

        DeleteSiteSSH::dispatch($site)->delay(Carbon::now()->addSeconds(1));

        return response()->json([]);
    }


    /**
     * SSL request for site (and its aliases)
     *
     * @OA\Post(
     *      path="/api/sites/{site_id}/ssl",
     *      summary="SSL request for site (and its aliases)",
     *      tags={"Sites"},
     *      description="Require SSL certs for site and its aliases.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to certificate (with its aliases).",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful SSL request",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function ssl(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        SslSiteSSH::dispatch($site)->delay(Carbon::now()->addSeconds(3));

        return response()->json([]);
    }



    /**
     * Reset site SSH password
     *
     * @OA\Post(
     *      path="/api/sites/{site_id}/reset/ssh",
     *      summary="Reset site SSH password",
     *      tags={"Sites"},
     *      description="Require a reset for site SSH password.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful password reset",
     *          @OA\JsonContent(
     *                @OA\Property(
     *                    property="password",
     *                    description="Site SSH password",
     *                    type="string",
     *                    example="Secret_123"
     *                ),
     *                @OA\Property(
     *                    property="pdf",
     *                    description="Site summary pdf (temp 3 minutes link)",
     *                    type="string",
     *                    example="https://panel.domain.ltd/pdf/123454/1233442"
     *                ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function resetssh(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        $newpassword = Str::random(24);

        $site->password = $newpassword;
        $site->save();

        SiteUserPwdSSH::dispatch($site, $newpassword)->delay(Carbon::now()->addSeconds(1));

        $pdftoken = JWT::encode(['iat' => time(),'exp' => time() + 180], config('cipi.jwt_secret').'-Pdf');

        return response()->json([
            'password'  => $site->password,
            'pdf'       => URL::to('/pdf/'.$site->site_id.'/'. $pdftoken)
        ]);
    }


    /**
     * Reset site MySql password
     *
     * @OA\Post(
     *      path="/api/sites/{site_id}/reset/db",
     *      summary="Reset site MySql password",
     *      tags={"Sites"},
     *      description="Require a reset for site MySql password.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful password reset",
     *          @OA\JsonContent(
     *                @OA\Property(
     *                    property="password",
     *                    description="Site MySql password",
     *                    type="string",
     *                    example="Secret_123"
     *                ),
     *                @OA\Property(
     *                    property="pdf",
     *                    description="Site summary pdf (temp 3 minutes link)",
     *                    type="string",
     *                    example="https://panel.domain.ltd/pdf/123454/1233442"
     *                ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
    */
    public function resetdb(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        $last_password = $site->database;

        $site->database = Str::random(24);
        $site->save();

        SiteDbPwdSSH::dispatch($site, $last_password)->delay(Carbon::now()->addSeconds(1));

        $pdftoken = JWT::encode(['iat' => time(),'exp' => time() + 180], config('cipi.jwt_secret').'-Pdf');

        return response()->json([
            'password'  => $site->database,
            'pdf'       => URL::to('/pdf/'.$site->site_id.'/'. $pdftoken)
        ]);
    }


    public function pdf(string $site_id, string $pdftoken)
    {
        try {
            JWT::decode($pdftoken, config('cipi.jwt_secret').'-Pdf', ['HS256']);
        } catch (\Throwable $th) {
            abort(403);
        }

        $site = Site::where('site_id', $site_id)->firstOrFail();

        $data = [
            'username'      => $site->username,
            'password'      => $site->password,
            'path'          => $site->basepath,
            'ip'            => $site->server->ip,
            'domain'        => $site->domain,
            'dbpass'        => $site->database,
            'php'           => $site->php,
        ];

        $pdf = PDF::loadView('pdf', $data);

        return $pdf->download($site->username.'_'.date('YmdHi').'_'.date('s').'.pdf');
    }

    /**
     * List all site aliases
     *
     * @OA\Get(
     *      path="/api/sites/{site_id}/aliases",
     *      summary="List all site aliases",
     *      tags={"Sites"},
     *      description="List all aliases related to a site.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                @OA\Property(
     *                    property="alias_id",
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
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
     *      ),
     * )
    */
    public function aliases(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        $response = [];

        foreach ($site->aliases as $alias) {
            $data = [
                'alias_id'      => $alias->alias_id,
                'domain'        => $alias->domain
            ];
            array_push($response, $data);
        }

        return response()->json($response);
    }

    /**
     * Add an alias to site
     *
     * @OA\Post(
     *      path="/api/sites/{site_id}/aliases",
     *      summary="Add an alias to site",
     *      tags={"Sites"},
     *      description="Create an alias for required site.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful created",
     *          @OA\JsonContent(
     *                @OA\Property(
     *                    property="alias_id",
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
     *           ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Site domain conflict"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
     *      ),
     * )
    */
    public function createalias(Request $request, string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'domain'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Bad Request.',
                'errors' => $validator->errors()->getMessages()
            ], 400);
        }

        $conflict = false;
        foreach ($site->server->allsites as $site) {
            if ($site->domain == $request->domain) {
                $conflict = true;
                foreach ($site->aliases as $alias) {
                    if ($alias->domain == $request->domain) {
                        $conflict = true;
                    }
                }
            }
        }
        if ($conflict) {
            return response()->json([
                'message' => 'Required domain is used by another site/alias on this server.',
                'errors' => 'Domain conflict.'
            ], 409);
        }

        $alias = new Alias();
        $alias->alias_id  = Str::uuid();
        $alias->site_id   = $site->id;
        $alias->domain    = $request->domain;
        $alias->save();

        NewAliasSSH::dispatch($site, $alias)->delay(Carbon::now()->addSeconds(3));

        return response()->json([
            'alias_id'=> $alias->alias_id,
            'domain'  => $alias->domain
        ]);
    }


    /**
     * Delete an alias
     *
     * @OA\Delete(
     *      path="/api/sites/{site_id}/aliases/{alias_id}",
     *      summary="Delete an alias",
     *      tags={"Sites"},
     *      description="Delete an alias from a site.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          name="alias_id",
     *          description="The id of the alias to delete.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Success Delete"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource not found"
     *      ),
     * )
    */
    public function destroyalias(string $site_id, string $alias_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }

        $alias = Alias::where('alias_id', $alias_id)->first();

        if (!$alias) {
            return response()->json([
                'message' => 'Required alias does not exists into panel.',
                'errors' => 'Alias not found.'
            ], 404);
        }

        DeleteAliasSSH::dispatch($site, $alias)->delay(Carbon::now()->addSeconds(1));

        return response()->json([]);
    }

    public function autoLoginPMA(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return back();
        }

        return redirect()->to("mysecureadmin/index.php?username=".$site->username."&password=".$site->database);

    }
}
