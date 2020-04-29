<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Server;

class RemoteController extends Controller {

    public function start($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 0)->first();
        $server->complete = 1;
        $server->save();
        return 'OK';
    }

    public function finalize($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 1)->first();
        $server->complete = 2;
        $server->save();
        return 'OK';
    }

    public function ping($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 2)->first();
        $remote = Http::get('http://'.$server->ip.'/ping_'.$server->servercode.'.php');
        return $remote->status();
    }

    public function status($servercode) {
        $server = Server::where('servercode', $servercode)->where('complete', 2)->first();
        $remote = Http::get('http://'.$server->ip.'/status_'.$server->servercode.'.php');
        if($remote->status() != 200) {
            return '--;--;--';
        }
        return $remote->body();
    }

}
