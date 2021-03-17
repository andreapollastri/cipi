<?php

namespace App\Http\Controllers;

use App\Models\Alias;
use App\Models\Server;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ShellController extends Controller
{
    public function setup($server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 0)->firstOrFail();
        $script = Storage::get('cipi/setup.sh');
        $script = Str::replaceArray('???', [
            $server->password,
            $server->database,
            $server->server_id
        ], $script);
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function serversrootreset()
    {
        $script = Storage::get('cipi/rootreset.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function newsite()
    {
        $script = Storage::get('cipi/newsite.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function newalias($alias_id)
    {
        $alias = Alias::where('alias_id', $alias_id)->firstOrFail();
        $script = Storage::get('cipi/newalias.sh');
        $script = str_replace('???DOMAIN???', $alias->domain, $script);
        $script = str_replace('???ALIASID???', $alias->alias_id, $script);
        $script = str_replace('???PHP???', $alias->site->php, $script);
        $script = str_replace('???REMOTE???', config('app.url'), $script);
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function delsite()
    {
        $script = Storage::get('cipi/delsite.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function sitepass()
    {
        $script = Storage::get('cipi/sitepass.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }
}
