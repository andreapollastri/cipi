<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Smtp;

class SettingsController extends Controller
{

    public function index() {
        $user = auth()->user();
        $smtp = Smtp::first();
        return view('settings', compact('user', 'smtp'));
    }

    public function updateProfile(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required'
        ]);
        auth()->user()->update([
          'name' => $request->name,
          'email' => $request->email
        ]);
        $request->session()->flash('alert-success', 'Profile has been updated!');
        return redirect('/settings');
    }

    public function updatePassword(Request $request) {
        $this->validate($request, [
            'current' => 'required',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8|same:password'
        ]);
        if (!Hash::check($request->current, auth()->user()->password)) {
            $request->session()->flash('alert-error', 'Invalid current password!');
            return redirect('/settings');
        }
        auth()->user()->update([
          'password' => Hash::make($request->password)
        ]);
        $request->session()->flash('alert-success', 'Password has been updated!');
        return redirect('/settings');
    }

    public function updateSmtp(Request $request) {
        $mail = Smtp::first();
        $mail->host         = $request->host;
        $mail->port         = $request->port;
        $mail->from         = $request->from;
        $mail->encryption   = $request->encryption;
        $mail->username     = $request->username;
        $mail->password     = $request->password;
        $mail->save();
        $request->session()->flash('alert-success', 'SMTP configuration has been updated!');
        return redirect('/settings');
    }

}
