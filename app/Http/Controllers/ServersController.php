<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Server;
use phpseclib\Net\SSH2 as SSH;

class ServersController extends Controller
{


    public function index() {
        $servers = Server::all();
        return view('servers', compact('servers'));
    }


    public function api() {
        return Server::orderBy('name')->orderBy('ip')->where('status', 2)->get();
    }


    public function get($servercode) {
        $server = Server::where('servercode', $servercode)->with('applications')->firstOrFail();
        return view('server', compact('server'));
    }


    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'ip' => 'required'
        ]);
        if($request->ip == $request->server('SERVER_ADDR')) {
            $request->session()->flash('alert-error', 'You can\'t install a client server into the same Cipi Server!');
            return redirect('/servers');
        }
        Server::create([
            'name'      => $request->name,
            'provider'  => $request->provider,
            'location'  => $request->location,
            'ip'        => $request->ip,
            'port'      => 22,
            'username'  => 'cipi',
            'password'  => sha1(uniqid().microtime().$request->ip),
            'dbroot'    => sha1(microtime().uniqid().$request->name),
            'servercode'=> sha1(uniqid().$request->name.microtime().$request->ip)
        ]);
        $request->session()->flash('alert-success', 'Server '.$request->name.' has been created!');
        return redirect('/servers');
    }


    public function changeip(Request $request) {
        $this->validate($request, [
            'servercode' => 'required',
            'ip'         => 'required'
        ]);
        $server = Server::where('servercode', $request->servercode)->first();
        if($request->ip == $request->server('SERVER_ADDR')) {
            $request->session()->flash('alert-error', 'You can\'t setup the same Cipi IP!');
            return redirect('/servers');
        }
        $server->ip = $request->input('ip');
        $server->save();
        $request->session()->flash('alert-success', 'The IP of server '.$server->name.' has been updated!');
        return redirect('/servers');
    }


    public function changename(Request $request) {
        $this->validate($request, [
            'servercode' => 'required',
            'name'       => 'required'
        ]);
        $server = Server::where('servercode', $request->servercode)->first();
        $server->name = $request->input('name');
        $server->save();
        $request->session()->flash('alert-success', 'The name of server '.$server->ip.' has been updated!');
        return redirect('/servers');
    }


    public function destroy(Request $request) {
        $this->validate($request, [
            'servercode' => 'required',
        ]);
        $server = Server::where('servercode', $request->servercode)->firstOrFail();
        $server->delete();
        $request->session()->flash('alert-success', 'Server '.$server->name.' has been deleted!');
        return redirect('/servers');
    }


    public function reset($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 2)->firstOrFail();
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            abort(500);
        }
        $pass = sha1(uniqid().microtime().$server->ip);
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$server->password.' | sudo -S sudo sh /cipi/root.sh -p '.$pass);
        if(strpos($response, '###CIPI###') === false) {
            abort(500);
        }
        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            abort(500);
        }
        $server->password = $pass;
        $server->save();
        return $pass;
    }

    public function nginx($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 2)->firstOrFail();
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            abort(500);
        }
        $ssh->setTimeout(360);
        $ssh->exec('sudo systemctl restart nginx.service');
        return 'OK';
    }

    public function php($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 2)->firstOrFail();
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            abort(500);
        }
        $ssh->setTimeout(360);
        $ssh->exec('sudo service php7.4-fpm restart');
        $ssh->exec('sudo service php7.3-fpm restart');
        $ssh->exec('sudo service php7.2-fpm restart');
        return 'OK';
    }

    public function mysql($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 2)->firstOrFail();
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            abort(500);
        }
        $ssh->setTimeout(360);
        $ssh->exec('sudo service mysql restart');
        return 'OK';
    }


    public function redis($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 2)->firstOrFail();
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            abort(500);
        }
        $ssh->setTimeout(360);
        $ssh->exec('sudo systemctl restart redis.service');
        return 'OK';
    }


    public function supervisor($servercode) {
        $server = Server::where('servercode', $servercode)->where('status', 2)->firstOrFail();
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            abort(500);
        }
        $ssh->setTimeout(360);
        $ssh->exec('service supervisor restart');
        return 'OK';
    }


}
