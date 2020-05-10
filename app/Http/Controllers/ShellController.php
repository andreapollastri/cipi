<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Storage;
use App\Application;
use App\Server;

class ShellController extends Controller
{

    protected $url;

    public function __construct(UrlGenerator $url) {
        $this->url = $url;
    }

    public function install($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 0)->firstOrFail();
        $script = Storage::get('scripts/install.sh');
        $script = Str::replaceArray('???', [
            $this->url->to('/'),
            $server->port,
            $server->username,
            $server->password,
            $server->dbroot,
            $server->servercode
        ], $script);
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function hostadd($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 1)->firstOrFail();
        $script = Storage::get('scripts/hostadd.sh');
        $script = Str::replaceArray('???', [
            $server->dbroot
        ], $script);
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function hostget($appcode) {
        $application = Application::where('appcode', $appcode)->firstOrFail();
        if($application->basepath) {
            $basepath = '/home/'.$application->username.'/web/'.$application->basepath;
        } else {
            $basepath = '/home/'.$application->username.'/web';
        }
        $script = Storage::get('scripts/haget.conf');
        $script = str_replace('???USER???', $application->username, $script);
        $script = str_replace('???BASE???', $basepath, $script);
        $script = str_replace('???PHP???', $application->php, $script);
        $script = str_replace('???DOMAIN???', $application->domain, $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function phpfpm($appcode) {
        $application = Application::where('appcode', $appcode)->firstOrFail();
        $script = Storage::get('scripts/phpfpm.conf');
        $script = str_replace('???USER???', $application->username, $script);
        $script = str_replace('???PHP???', $application->php, $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function hostdel($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 1)->firstOrFail();
        $script = Storage::get('scripts/hostdel.sh');
        $script = Str::replaceArray('???', [
            $server->dbroot,
        ], $script);
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function passwd($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 1)->firstOrFail();
        $script = Storage::get('scripts/passwd.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function root($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 1)->firstOrFail();
        $script = Storage::get('scripts/root.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function aliasadd($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 1)->firstOrFail();
        $script = Storage::get('scripts/aliasadd.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function aliasdel($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 1)->firstOrFail();
        $script = Storage::get('scripts/aliasdel.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function aliasget($appcode,$domain) {
        $application = Application::where('appcode', $appcode)->firstOrFail();
        if($application->basepath) {
            $basepath = '/home/'.$application->username.'/web/'.$application->basepath;
        } else {
            $basepath = '/home/'.$application->username.'/web';
        }
        $script = Storage::get('scripts/haget.conf');
        $script = str_replace('???USER???', $application->username, $script);
        $script = str_replace('???BASE???', $basepath, $script);
        $script = str_replace('???PHP???', $application->php, $script);
        $script = str_replace('???DOMAIN???', $domain, $script);
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function nginx() {
        $script = Storage::get('scripts/nginx.conf');
        return response($script)->withHeaders(['Content-Type' =>'text/plain']);
    }

    public function ssl() {
        $script = Storage::get('scripts/ssl.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function status() {
        $script = Storage::get('scripts/status.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

    public function deploy() {
        $script = Storage::get('scripts/deploy.sh');
        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
    }

}
