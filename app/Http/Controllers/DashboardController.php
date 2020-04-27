<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Server;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $servers = Server::where('complete', 1)->get();
        return view('dashboard', compact('servers'));
    }
}
