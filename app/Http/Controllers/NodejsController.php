<?php

namespace App\Http\Controllers;

use App\Jobs\NodejsSetupSSH;
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

class NodejsController extends Controller
{

    /**
     * Setup Nodejs information
     *
     * @OA\Patch(
     *      path="/api/nodejs/{site_id}",
     *      summary="Setup Nodejs information",
     *      tags={"Nodejs", "Sites"},
     *      description="Setup Nodejs information by site_id.",
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to setup nodejs for.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Site nodejs payload",
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="path",
     *                  description="Js script to start",
     *                  type="string",
     *                  example="./bin/www",
     *             )
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
    public function setup(Request $request, string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => 'Required site does not exists into panel.',
                'errors' => 'Site not found.'
            ], 404);
        }


        $site->node_script = $request->path;
        $site->node_status = 1;
        $site->save();

        NodejsSetupSSH::dispatch($site)->delay(Carbon::now()->addSeconds(1));

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
            'node_script'        => $site->node_script,
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
            'node_script'       => $site->node_script,
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





    public function autoLoginPMA(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return back();
        }

        return redirect()->to("mysecureadmin/index.php?username=".$site->username."&password=".$site->database);

    }
}
