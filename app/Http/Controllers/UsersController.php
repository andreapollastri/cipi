<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Application;


class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $user = User::find(Auth::id());
        $profile = $user->name;

        $hostusers = Application::all();

        return view('users', compact('profile', 'hostusers'));
    }


    public function reset(Request $request)
    {

        $user = User::find(Auth::id());
        $profile = $user->name;

        $this->validate($request, [
            'username' => 'required'
        ]);

        $application = Application::where('username', $request->username)->get()->first();

        if(!$application) {
            return redirect()->route('users');
        }


        $ssh = New \phpseclib\Net\SSH2($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $messagge = 'There was a problem with server connection. Try later!';
            return view('generic', compact('profile','messagge'));
        }

        $pass   = str_random(32);
        $dbpass = str_random(16);

        $ssh->setTimeout(60);
        $response = $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/passwd.sh -u '.$request->username.' -p '.$pass.' -dbp '.$dbpass. ' -dbop '.$application->dbpass);

        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            $messagge = 'There was a problem with server. Try later!';
            return view('generic', compact('profile','messagge'));
        }

        Application::where(['id' => $application->id])->update([
            'password'  => $pass,
            'dbpass'    => $dbpass,
        ]);

        $app = [
            'user'          => $request->username,
            'pass'          => $pass,
            'dbname'        => $request->username,
            'dbuser'        => $request->username,
            'dbpass'        => $dbpass,
            'path'          => $application->basepath,
            'domain'        => $application->domain,
            'autoinstall'   => $application->autoinstall,
            'host'          => $application->server->ip,
            'port'          => $application->server->port,
        ];

        $appcode = $application->appcode;

        return view('application', compact('profile','app','appcode'));
    }

}
