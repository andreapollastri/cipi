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

}
