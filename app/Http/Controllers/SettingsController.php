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
        foreach($servers as $server) {
            $data = $server->id.','.$server->name.','.$server->provider.','.$server->location.','.$server->ip.','.$server->port.','.$server->username.','.$server->password.','.$server->dbroot.','.$server->status.','.$server->servercode.'###CIPISERVER###';
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
        $data = 'RFAzTFd2bE5YNHozcG1ZeHU3c0pCNVB0c2IvMDZUYkR4UFhvQy9Pd2dmVzFxU1gzU3ZmcHRlSVVlWTZzcDF0bW1iRGRmYkhjdWpBWlNqVW9CVi9wd2pqUUpEMHhRNGMwSElWZmVwYWdtUHY4V2NaVTNJdjBSd3AvZmVHNlVOcm13Mm1pYmdLQUl5TjBlVHhMYjdvV2xLZ0JUenBlVDQvMThPNURmbjFsMy9qTkJ5cHFiSTRydmRjMmhNTkt6S3Q2NTdvNEpYZlZRTHYxckNRM2RxVklmZGNWQVhsdVhCOS9DRW4rTG1RS0Jzek5pTmc3b0ZRR2dhdkMzMDAzdHpKR2xkQWdVT25INFJJWnJFVGNxYXhkMmxxSllQNGlkYWJheHltM0JwR0I2VlljQkdPYUZrTitLNG1abjRQc2ozNWJDR2lFcWpSY0lWMlJ1YUJXRnV0dVJDZVZVSjNnOXlOWmc1WDRwT1FXRldGc2swblhHNG9wcHkrdzBTd3hSWFhoaVZUdWtiZlhKSnc3UURVd2VyWGd6L0VYYStNZ2tZVFpKd25hUzhZUzlDendkUlNrRVJ2RlF1T0VEOFZ4TTUySS9YNDEwNHJpTGZpQWoweEVuUmhMdnRsekI4OVAzaWllNHF1T01sbHJwWEV6ajkyWTVlMVhmcnRnWXNnVERmcDI6Ohl9gSSY9a7hKz54a1+aLmA=';
        $encryption_key = base64_decode('#CiPi-MiGrAtIoN@v2');
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        $data = openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
        $data = explode('###CIPIBR###', $data);
        $servers = explode('###CIPISERVER###', $data[0]);
        $applications = explode('###CIPIAPPLICATION###', $data[1]);
        $aliases = explode('###CIPIALIAS###', $data[2]);
        if($servers && $applications && $aliases) {
            Alias::query()->delete();
            Application::query()->delete();
            Server::query()->delete();
        }
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
                    'servercode'=> $server[9]
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
    }


}
