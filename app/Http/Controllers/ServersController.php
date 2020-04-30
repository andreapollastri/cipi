<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Server;

class ServersController extends Controller
{


    public function index() {
        $servers = Server::all();
        return view('servers', compact('servers'));
    }


    public function api() {
        return Server::orderBy('name')->get();
    }


    public function get($servercode) {
        $server = Server::where('servercode', $servercode)->with('applications')->first();
        if(!$server) {
            abort(404);
        }
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


    public function destroy(Request $request) {
        $this->validate($request, [
            'servercode' => 'required',
        ]);
        $server = Server::where('servercode', $request->servercode)->first();
        $request->session()->flash('alert-success', 'Server '.$server->name.' has been deleted!');
        $server->delete();
        return redirect('/servers');
    }


}
