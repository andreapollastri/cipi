<?php

namespace App\Http\Controllers;
use App\Server;

class DashboardController extends Controller {

    public function index() {
        $servers = Server::where('complete', 2)->orderBy('name')->get();
        return view('dashboard', compact('servers'));
    }

}
