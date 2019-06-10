<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Server;
use App\User;

class ScriptsController extends Controller
{

    protected $url;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }


    public function install($servercode)
    {
        
        $server = Server::where([['servercode', $servercode]])->where([['complete', 0]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/install.sh');
        $script = Str::replaceArray('???', [
            $server->ip,
            $server->port,
            $server->username,
            $server->password,
            $server->dbroot,
            $server->servercode,
            $this->url->to('/')
        ], $script);

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);

    }


    public function hostadd($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/hostadd.sh');
        $script = Str::replaceArray('???', [
            $server->dbroot,
            $server->ip,
        ], $script);

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);

    }


    public function hostdel($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/hostdel.sh');
        $script = Str::replaceArray('???', [
            $server->dbroot,
        ], $script);

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
        
    }


    public function hostssl($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/hostssl.sh');
        $script = Str::replaceArray('???', [
            env('USER_EMAIL', 'admin@admin.com'),
            env('USER_EMAIL', 'admin@admin.com'),
        ], $script);

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
        
    }


    public function passwd($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/passwd.sh');

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
        
    }


    public function aliasadd($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/aliasadd.sh');

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
        
    }


    public function aliasdel($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/aliasdel.sh');

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
        
    }


    public function status($servercode)
    {

        $server = Server::where([['servercode', $servercode]])->where([['complete', 1]])->get()->first();
        
        if(!$server) {
            return abort(403);
        }

        $script = Storage::get('scripts/status.sh');

        return response($script)->withHeaders(['Content-Type' =>'application/x-sh']);
        
    }



}
