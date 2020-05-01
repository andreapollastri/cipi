<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{

    public function index() {
        $user = Auth::user();
        return view('settings', compact('user'));
    }

    public function profile(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required'
        ]);
        $user = Auth::user();
        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $request->session()->flash('alert-success', 'Profile has been updated!');
        return redirect('/settings');
    }

    public function password(Request $request) {
        $this->validate($request, [
            'current' => 'required',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8'
        ]);
        $user = Auth::user();
        if ($request->password != $request->password_confirmation) {
            return redirect('/settings');
        }
        if (!Hash::check($request->current, $user->password)) {
            $request->session()->flash('alert-error', 'Wrong password!');
            return redirect('/settings');
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $request->session()->flash('alert-success', 'Password has been updated!');
        return redirect('/settings');
    }

}
