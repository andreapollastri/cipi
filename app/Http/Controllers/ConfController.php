<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Alias;
use App\Models\Server;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ConfController extends Controller
{
    public function cron($server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->firstOrFail();

        $script = Storage::get('cipi/cron.conf');
        if ($server->cron) {
            $script = Str::replaceArray('???', [
                $server->cron
            ], $script);
        } else {
            $script = Str::replaceArray('???', [
                ' '
            ], $script);
        }
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function panel()
    {
        $server = Server::where('default', 1)->firstOrFail();
        $site = Site::where('server_id', $server->id)->where('panel', 1)->firstOrFail();
        $script = Storage::get('cipi/panel.conf');
        $script = Str::replaceArray('???', [
            $site->domain
        ], $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function host($site_id)
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        
        if ($site->basepath) {
            $basepath = '/home/'.$site->username.'/web/'.$site->basepath;
        } else {
            $basepath = '/home/'.$site->username.'/web';
        }

        $script = Storage::get('cipi/host.conf');
        $script = str_replace('???USER???', $site->username, $script);
        $script = str_replace('???BASE???', $basepath, $script);
        $script = str_replace('???PHP???', $site->php, $script);
        $script = str_replace('???DOMAIN???', $site->domain, $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }


    public function alias($alias_id)
    {
        $alias = Alias::where('alias_id', $alias_id)->firstOrFail();
        
        if ($alias->site->basepath) {
            $basepath = '/home/'.$alias->site->username.'/web/'.$alias->site->basepath;
        } else {
            $basepath = '/home/'.$alias->site->username.'/web';
        }

        $script = Storage::get('cipi/host.conf');
        $script = str_replace('???USER???', $alias->site->username, $script);
        $script = str_replace('???BASE???', $basepath, $script);
        $script = str_replace('???PHP???', $alias->site->php, $script);
        $script = str_replace('???DOMAIN???', $alias->domain, $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }


    public function php($site_id)
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        
        $script = Storage::get('cipi/php.conf');
        $script = str_replace('???USER???', $site->username, $script);
        $script = str_replace('???PHP???', $site->php, $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function nginx()
    {
        $script = Storage::get('cipi/nginx.conf');
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }
}
