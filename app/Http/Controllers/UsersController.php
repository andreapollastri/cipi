<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Application;
use \phpseclib\Net\SSH2 as SSH;


class UsersController extends Controller {


    public function index() {
        $users = Application::all();
        return view('users', compact('users'));
    }


    public function reset(Request $request) {
        $this->validate($request, [
            'username' => 'required'
        ]);
        $application = Application::where('username', $request->username)->with('server')->firstOrFail();
        $ssh = New SSH($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/users');
        }
        $pass   = sha1(uniqid().microtime().$application->domain);
        $dbpass = sha1(microtime().uniqid().$application->server->ip);
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/passwd.sh -u '.$request->username.' -p '.$pass.' -dbp '.$dbpass. ' -dbop '.$application->dbpass);
        if(strpos($response, '###CIPI###') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server scripts.');
            return redirect('/users');
        }
        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server scripts.');
            return redirect('/users');
        }
        $application->password = $pass;
        $application->dbpass   = $dbpass;
        $application->save();
        $app = [
            'user'          => $request->username,
            'pass'          => $pass,
            'dbname'        => $request->username,
            'dbuser'        => $request->username,
            'dbpass'        => $dbpass,
            'path'          => $application->basepath,
            'domain'        => $application->domain,
            'php'           => $application->php,
            'host'          => $application->server->ip,
            'port'          => $application->server->port,
        ];
        $appcode = $application->appcode;
        return view('application', compact('app','appcode'));
    }

}
