<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Application;
use App\Server;

class ServerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($servercode)
    {

        $user = User::find(Auth::id());
        $profile = $user->name;

        $server = Server::where('servercode', $servercode)->get()->first();

        $applications = Application::where('server_id', $server->id)->orderBy('domain')->orderBy('username')->get();

        return view('server', compact('profile', 'applications', 'server'));
    }


}
