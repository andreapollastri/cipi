<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Application;

class DatabasesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $user = User::find(Auth::id());
        $profile = $user->name;

        $databases = Application::orderBy('username')->orderBy('domain')->get();
        
        return view('databases', compact('profile', 'databases'));
    }

}
