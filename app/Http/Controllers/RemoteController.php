<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Server;

class RemoteController extends Controller {

    public function start($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 0)->value('servercode');
        if(!$servercode) {
            return abort(403);
        }
        $server = Server::where('servercode', $servercode)->update(['complete' => 1]);
        return 'OK';
    }

    public function finalize($servercode) {
        $servercode = Server::where('servercode', $servercode)->where('complete', 1)->value('servercode');
        if(!$servercode) {
            return abort(403);
        }
        $server = Server::where('servercode', $servercode)->update(['complete' => 2]);
        return 'OK';
    }

    public function ping($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 2)->get()->first();
        if(!$server) {
            return abort(403);
        }
        $response = Http::get('http://'.$server->ip.'/ping_'.$server->servercode.'.php');
        return $response->status();
    }

    public function status($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 2)->get()->first();
        if(!$server) {
            return abort(403);
        }
        $response = Http::get('http://'.$server->ip.'/ping_'.$server->servercode.'.php');
        return $response->body();
    }

}
