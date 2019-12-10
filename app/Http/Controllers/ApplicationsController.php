<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Application;
use App\Server;
use App\Alias;
use Helper;
use PDF;

class ApplicationsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

    	$user = User::find(Auth::id());
        $profile = $user->name;

        $applications = Application::orderBy('domain')->orderBy('server_id')->get();

        return view('applications', compact('profile', 'applications'));
    }


    public function create(Request $request)
    {


        $user = User::find(Auth::id());
        $profile = $user->name;


        $this->validate($request, [
            'domain' => 'required', 'server_id' => 'required', 'basepath' => 'required',
        ]);


        if(Application::where('domain', $request->domain)->where('server_id', $request->server_id)->get()->first()) {
            $messagge = "This domain is already taken on this server.";
            return view('generic', compact('profile','messagge'));
        }


        if(Alias::where('domain', $request->domain)->where('server_id', $request->server_id)->get()->first()) {
            $messagge = "This domain is already taken on this server.";
            return view('generic', compact('profile','messagge'));
        }


        $server = Server::where('id', $request->server_id)->where('complete', 2)->get()->first();


        if(!$server) {
            return abort(403);
        }


        $code   = hash('crc32', uniqid()).str_random(2);
        $pass   = str_random(32);
        $dbpass = str_random(16);
        $appcode= sha1(uniqid().microtime().$request->name);


        $autoinstall = $request->autoinstall;


        switch ($autoinstall) {
            case 'laravel':
                $base = 'laravel/public';
                break;
            case 'wordpress':
                $base = 'wordpress';
                break;
            default:
                $base = $request->basepath;
                break;
        }


        $ssh = New \phpseclib\Net\SSH2($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            $messagge = 'There was a problem with server connection. Try later!';
            return view('generic', compact('profile','messagge'));
        }


        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$server->password.' | sudo -S sudo sh /cipi/host-add.sh -d '.$request->domain.' -u '.$code.' -p '.$pass.' -dbp '.$dbpass.' -b '.$base.' -ai '.$autoinstall);


        if(strpos($response, '###CIPI###') === false) {
            $messagge = 'There was a problem with server. Try later!';
            return view('generic', compact('profile','messagge'));
        }


        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            $messagge = 'There was a problem with server. Try later!';
            return view('generic', compact('profile','messagge'));
        }


        Application::create([
            'domain'        => $request->domain,
            'server_id'     => $request->server_id,
            'username'      => $code,
            'password'      => $pass,
            'dbpass'        => $dbpass,
            'basepath'      => $base,
            'autoinstall'   => $autoinstall,
            'appcode'       => $appcode,
        ]);

        $app = [
            'user'          => $code,
            'pass'          => $pass,
            'dbname'        => $code,
            'dbuser'        => $code,
            'dbpass'        => $dbpass,
            'path'          => $base,
            'autoinstall'   => $autoinstall,
            'domain'        => $request->domain,
            'host'          => $server->ip,
            'port'          => $server->port,
        ];

        return view('application', compact('profile','app','appcode'));

    }






    public function delete(Request $request)
    {

        $user = User::find(Auth::id());
        $profile = $user->name;

        $this->validate($request, [
            'appcode' => 'required',
        ]);

        $application = Application::where('appcode', $request->appcode)->get()->first();

        if(!$application) {
            return abort(403);
        }

        $application->delete();

        $ssh = New \phpseclib\Net\SSH2($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $messagge = 'There was a problem with server connection. Try later!';
            return view('generic', compact('profile','messagge'));
        }

        $ssh->setTimeout(60);
        foreach ($application->aliases as $alias) {
            $ssh->exec('echo '.$application->server->password.' | sudo -S unlink /etc/cron.d/certbot_renew_'.$alias->domain.'.crontab');
            $ssh->exec('echo '.$application->server->password.' | sudo -S unlink /cipi/certbot_renew_'.$alias->domain.'.sh');
        }
        $response = $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/host-del.sh -u '.$application->username);

        $application->delete();

        return redirect()->route('applications');

    }





    public function pdf($applicationcode)
    {

        $application = Application::where('appcode', $applicationcode)->get()->first();
        $data = [
            'username'      => $application->username,
            'password'      => $application->password,
            'path'          => $application->basepath,
            'ip'            => $application->server->ip,
            'port'          => $application->server->port,
            'domain'        => $application->domain,
            'dbpass'        => $application->dbpass,
            'autoinstall'   => $application->autoinstall,
        ];

        $pdf = PDF::loadView('pdf', $data);
        return $pdf->download($application->username.'_'.date('YmdHi').'_'.date('s').'.pdf');

    }



}
