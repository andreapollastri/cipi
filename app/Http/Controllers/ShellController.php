<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Alias;
use App\Models\Server;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ShellController extends Controller
{

    /**
     * Server Setup script
     *
    */
    public function setup(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 0)->firstOrFail();

        $script = Storage::get('cipi/setup.sh');
        $script = Str::replaceArray('???', [
            $server->password,
            $server->database,
            $server->server_id
        ], $script);

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    /**
     * Server Deploy script
     *
    */
    public function deploy(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        $script = Storage::get('cipi/deploy.sh');
        $script = str_replace('???USER???', $site->username, $script);
        $script = str_replace('???REPO???', $site->repository, $script);
        $script = str_replace('???BRANCH???', $site->branch, $script);
        $script = str_replace('???SCRIPT???', $site->deploy, $script);

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    /**
     * Server Root User Reset script
     *
    */
    public function serversrootreset()
    {
        $script = Storage::get('cipi/rootreset.sh');

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    /**
     * New Site script
     *
    */
    public function newsite()
    {
        $script = Storage::get('cipi/newsite.sh');

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }


    /**
     * Delete Site script
     *
    */
    public function delsite()
    {
        $script = Storage::get('cipi/delsite.sh');

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }


    /**
     * Reset Site Credentials script
     *
    */
    public function sitepass()
    {
        $script = Storage::get('cipi/sitepass.sh');

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }


    /**
     * Client Patch - 202112091
     *
    */
    public function patch202112091()
    {
        $script = Storage::get('cipi/patch202112091.sh');

        return response($script)
                ->withHeaders(['Content-Type' =>'application/x-sh']);
    }
}
