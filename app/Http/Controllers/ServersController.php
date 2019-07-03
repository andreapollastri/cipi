<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Server;
use App\User;

class ServersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $user = User::find(Auth::id());
        $profile = $user->name;

        $servers = Server::all();

        return view('servers', compact('profile', 'servers'));
    }


    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required', 'ip' => 'required'
        ]);
        
        if($request->ip == $request->server('SERVER_ADDR')) {
            $user = User::find(Auth::id());
            $profile = $user->name;
            $messagge = "You can't install a client server into the same Cipi s$
            return view('generic', compact('profile','messagge'));
            die();
        }

        Server::create([
            'name'      => $request->name,
            'provider'  => $request->provider,
            'location'  => $request->location,
            'ip'        => $request->ip,
            'port'      => env('SSH_DEFAULT_PORT', '1759'),
            'username'  => uniqid(),
            'password'  => str_random(32),
            'dbroot'    => str_random(32),
            'servercode'=> md5(uniqid().microtime().$request->name),
        ]);

        return redirect()->route('servers');
    }



    public function delete(Request $request)
    {
        
        $this->validate($request, [
            'servercode' => 'required',
        ]);

        $server = Server::where('servercode', $request->servercode)->get()->first();

        $server->delete();

        return redirect()->route('servers');

    }


}
