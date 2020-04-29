<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Server;

class RemoteController extends Controller {

    public function start($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 0)->first();
        if(!$server) {
            return abort(403);
        }
        $server->update(['complete' => 1]);
        return 'OK';
    }

    public function finalize($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 1)->first();
        if(!$server) {
            return abort(403);
        }
        $server->update(['complete' => 2]);
        return 'OK';
    }

    public function ping($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 2)->first();
        if(!$server) {
            return abort(403);
        }
        $remote = Http::get('http://'.$server->ip.'/ping_'.$server->servercode.'.php');
        return $remote->status();
    }

    public function status($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 2)->first();
        if(!$server) {
            return abort(403);
        }
        $remote = Http::get('http://'.$server->ip.'/ping_'.$server->servercode.'.php');
        return $remote->body();
    }

}
