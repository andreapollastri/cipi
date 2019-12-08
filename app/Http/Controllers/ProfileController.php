<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;

class ProfileController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $user = User::find(Auth::id());
        $profile = $user->name;

        $name = $user->name;
        $email = $user->email;

        return view('profile', compact('profile', 'name', 'email'));
    }



    public function edit(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required'
        ]);

        $user = User::find(Auth::id());

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('profile');
    }



    public function password(Request $request)
    {
        $this->validate($request, [
            'current' => 'required',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8'
        ]);

        $user = User::find(Auth::id());

        if ($request->password != $request->password_confirmation) {
            return redirect()->route('profile');
        }

        if (!Hash::check($request->current, $user->password)) {
            return redirect()->route('profile');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile');
    }

}
