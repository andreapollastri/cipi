<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Application;
use App\Server;
use App\Alias;
use phpseclib\Net\SSH2 as SSH;
use PDF;

class ApplicationsController extends Controller {

    public function index() {
        $applications = Application::with('server')->with('aliases')->get();
        return view('applications', compact('applications'));
    }

    public function api() {
        return Application::orderBy('domain')->get();
    }

    public function create(Request $request) {
        $this->validate($request, [
            'domain' => 'required',
            'server_id' => 'required',
            'basepath' => 'required',
            'php' => 'required'
        ]);
        if(Application::where('domain', $request->domain)->where('server_id', $request->server_id)->first()) {
            $request->session()->flash('alert-error', 'This domain is already taken on this server');
            return redirect('/applications');
        }
        $aliases = Alias::where('domain', $request->domain)->get();
        foreach($aliases as $alias) {
            if($alias->application->sever->id == $request->server_id) {
                $request->session()->flash('alert-error', 'This domain is already taken on this server');
                return redirect('/applications');
            }
        }
        $server = Server::where('id', $request->server_id)->where('complete', 2)->first();
        if(!$server) {
            return abort(403);
        }
        $chars  = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
        $code   = hash('crc32', substr($chars, 0, 64)).uniqid().substr($chars, 0, 3);
        $pass   = substr($chars, 0, 32);
        $dbpass = substr($chars, 0, 24);
        $appcode= sha1(uniqid().microtime().$request->name);
        $base   = $request->basepath;
        Application::create([
            'domain'        => $request->domain,
            'server_id'     => $request->server_id,
            'username'      => $code,
            'password'      => $pass,
            'dbpass'        => $dbpass,
            'basepath'      => $base,
            'php'           => $request->php,
            'appcode'       => $appcode,
        ]);
        $ssh = New SSH($server->ip, $server->port);
        if(!$ssh->login($server->username, $server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/applications');
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$server->password.' | sudo -S sudo sh /cipi/host-add.sh -d '.$request->domain.' -u '.$code.' -p '.$pass.' -dbp '.$dbpass.' -b '.$base.' -a '.$appcode);
        if(strpos($response, '###CIPI###') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/applications');
        }
        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/applications');
        }
        $app = [
            'user'          => $code,
            'pass'          => $pass,
            'dbname'        => $code,
            'dbuser'        => $code,
            'dbpass'        => $dbpass,
            'path'          => $base,
            'php'           => $request->php,
            'domain'        => $request->domain,
            'host'          => $server->ip,
            'port'          => $server->port,
        ];
        return view('application', compact('app','appcode'));
    }

    public function destroy(Request $request) {
        $this->validate($request, [
            'appcode' => 'required',
        ]);
        $application = Application::where('appcode', $request->appcode)->get()->first();
        if(!$application) {
            return abort(403);
        }
        $ssh = New SSH($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/applications');
        }
        $ssh->setTimeout(60);
        foreach ($application->aliases as $alias) {
            $ssh->exec('echo '.$application->server->password.' | sudo -S unlink /etc/cron.d/certbot_renew_'.$alias->domain.'.crontab');
            $ssh->exec('echo '.$application->server->password.' | sudo -S unlink /cipi/certbot_renew_'.$alias->domain.'.sh');
            $ssh->exec('echo '.$application->server->password.' | sudo -S unlink /etc/nginx/sites-enabled/'.$alias->domain.'.conf');
            $ssh->exec('echo '.$application->server->password.' | sudo -S unlink /etc/nginx/sites-available/'.$alias->domain.'.conf');
        }
        $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/host-del.sh -u '.$application->username.' -d '.$application->domain);
        $application->delete();
        $request->session()->flash('alert-success', 'Application has been removed!');
        return redirect('/applications');
    }

    public function pdf($appcode) {
        $application = Application::where('appcode', $appcode)->first();
        $data = [
            'username'      => $application->username,
            'password'      => $application->password,
            'path'          => $application->basepath,
            'ip'            => $application->server->ip,
            'port'          => $application->server->port,
            'domain'        => $application->domain,
            'dbpass'        => $application->dbpass,
            'php'           => $application->php,
        ];
        $pdf = PDF::loadView('pdf', $data);
        return $pdf->download($application->username.'_'.date('YmdHi').'_'.date('s').'.pdf');
    }

}
