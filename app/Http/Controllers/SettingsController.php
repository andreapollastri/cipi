<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Smtp;
use App\Alias;
use App\Application;
use App\Server;

class SettingsController extends Controller
{

    public function index() {
        $user = auth()->user();
        $smtp = Smtp::first();
        return view('settings', compact('user', 'smtp'));
    }

    public function updateUsername(Request $request) {
        $this->validate($request, [
            'email' => 'required|email'
        ]);
        auth()->user()->update([
          'email' => $request->email
        ]);
        $request->session()->flash('alert-success', 'Username has been updated!');
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

    public function updateSecret() {
        $appsecret = sha1(microtime());
        auth()->user()->update([
          'appsecret' => $appsecret
        ]);
        return $appsecret;
    }

    public function exportCipi() {
        $servers = Server::all();
        $applications = Application::all();
        $aliases = Alias::all();
        $data = '';
        foreach($servers as $server) {
            $data .= $server->id.','.$server->name.','.$server->provider.','.$server->location.','.$server->ip.','.$server->port.','.$server->username.','.$server->password.','.$server->dbroot.','.$server->status.','.$server->servercode.'###CIPISERVER###';
        }
        $data .= '###CIPIBR###';
        foreach($applications as $application) {
            $data .= $application->id.','.$application->domain.','.$application->server_id.','.$application->username.','.$application->password.','.$application->dbpass.','.$application->basepath.','.$application->php.','.$application->appcode.'###CIPIAPPLICATION###';
        }
        $data .= '###CIPIBR###';
        foreach($aliases as $alias) {
            $data .= $alias->id.','.$alias->domain.','.$alias->application_id.','.$alias->aliascode.'###CIPIALIAS###';
        }
        $encryption_key = base64_decode('#CiPi-MiGrAtIoN@v2');
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public function importCipi(Request $request) {
        $data = $request->input('cipikey');
        $encryption_key = base64_decode('#CiPi-MiGrAtIoN@v2');
        try {
            list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        } catch (\Throwable $th) {
            $request->session()->flash('alert-error', 'Invalid Cipi migration key. Retry!');
            return redirect('/settings');
        }
        try {
            $data = openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
        } catch (\Throwable $th) {
            $request->session()->flash('alert-error', 'Invalid Cipi migration key. Retry!');
            return redirect('/settings');
        }
        if(strpos($data, '###CIPIBR###') === false) {
            $request->session()->flash('alert-error', 'Invalid Cipi migration key. Retry!');
            return redirect('/settings');
        } else {
            Alias::query()->delete();
            Application::query()->delete();
            Server::query()->delete();
        }
        $data = explode('###CIPIBR###', $data);
        $servers = explode('###CIPISERVER###', $data[0]);
        $applications = explode('###CIPIAPPLICATION###', $data[1]);
        $aliases = explode('###CIPIALIAS###', $data[2]);
        foreach ($servers as $server) {
            if($server) {
                $server = explode(',', $server);
                Server::create([
                    'id'        => $server[0],
                    'name'      => $server[1],
                    'provider'  => $server[2],
                    'location'  => $server[3],
                    'ip'        => $server[4],
                    'port'      => $server[5],
                    'username'  => $server[6],
                    'password'  => $server[7],
                    'dbroot'    => $server[8],
                    'status'    => $server[9],
                    'servercode'=> $server[10]
                ]);
            }
        }
        foreach ($applications as $application) {
            if($application) {
                $application = explode(',', $application);
                Application::create([
                    'id'        => $application[0],
                    'domain'    => $application[1],
                    'server_id' => $application[2],
                    'username'  => $application[3],
                    'password'  => $application[4],
                    'dbpass'    => $application[5],
                    'basepath'  => $application[6],
                    'php'       => $application[7],
                    'appcode'   => $application[8]
                ]);
            }
        }
        foreach ($aliases as $alias) {
            if($alias) {
                $alias = explode(',', $alias);
                Alias::create([
                    'id'            => $alias[0],
                    'domain'        => $alias[1],
                    'application_id'=> $alias[2],
                    'aliascode'     => $alias[3]
                ]);
            }
        }
        $request->session()->flash('alert-success', 'Your migration has been imported.');
        return redirect('/settings');
    }


}
