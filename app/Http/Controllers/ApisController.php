<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Server;
use App\Application;
use App\Alias;
use Ping;
use Helper;

class ApisController extends Controller
{

    public function __construct()
    {

    }


    public function index()
    {
        //
    }


    public function start($servercode)
    {
        $servercode = Server::where('servercode', $servercode)->where('complete', 0)->value('servercode');

        if(!$servercode) {
            return abort(403);
        }

        $server = Server::where('servercode', $servercode)->update(['complete' => 1]);
        die("OK");
    }


    public function finalize($servercode)
    {
        $servercode = Server::where('servercode', $servercode)->where('complete', 1)->value('servercode');

        if(!$servercode) {
            return abort(403);
        }

        $server = Server::where('servercode', $servercode)->update(['complete' => 2]);
        die("OK");
    }


    public function status($servercode)
    {
        $this->middleware('auth');

        $server = Server::where('servercode', $servercode)->where('complete', 2)->get()->first();

        if(!$server) {
            return abort(403);
        }

        return file_get_contents('http://'.$server->ip.'/stats_'.$server->servercode.'.php');
    }


    public function ping($servercode)
    {
        $this->middleware('auth');

        $server = Server::where('servercode', $servercode)->where('complete', 2)->get()->first();

        if(!$server) {
            return abort(403);
        }

        return Ping::check('http://'.$server->ip.'/ping_'.$server->servercode.'.php');
    }


    public function ajaxservers()
    {

        $this->middleware('auth');

        $servers = Server::select('id','name','ip')->where('complete', 2)->orderBy('name')->orderBy('provider')->orderBy('location')->get();

        return $servers->toJson();

    }


    public function ajaxapplications($sever)
    {

        $this->middleware('auth');

        $applications = Application::select('id','domain')->where('server_id', $sever)->orderBy('domain')->get();

        return $applications->toJson();

    }


    public function sslapplication($applicationcode)
    {

        $this->middleware('auth');

        $application = Application::where('appcode', $applicationcode)->get()->first();

        if(!$application) {
            return abort(403);
        }


        // Attempt to login with SSH key.
        $ssh = new \phpseclib\Net\SSH2($application->server->ip, $application->server->port);
        $key = new \phpseclib\Crypt\RSA();

        $key->loadKey(file_get_contents('/cipi/id_rsa'));

        if (!$ssh->login($application->server->username, $key)) {
            // If login failed, default back to password.
            if (!$ssh->login($application->server->username, $application->server->password)) {
                $messagge = 'There was a problem with server connection. Try later!';
                return view('generic', compact('profile','messagge'));
            }
        }

        $ssh->setTimeout(60);
        $response = $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/ssl.sh -d '.$application->domain);

        if(strpos($response, '###CIPI###') === false) {
            return abort(403);    
        }


        $response = explode('###CIPI###', $response);
        if($response[1] == "Ok\n" && Helper::sslcheck($application->domain) == 1) {  
            die("OK");
        } else {
            return abort(403);
        }

    }



    public function sslalias($aliascode)
    {

        $this->middleware('auth');

        $alias = Alias::where('aliascode', $aliascode)->get()->first();

        if(!$alias) {
            return abort(403);
        }

        // Attempt to login with SSH key.
        $ssh = new \phpseclib\Net\SSH2($alias->server->ip, $alias->server->port);
        $key = new \phpseclib\Crypt\RSA();

        $key->loadKey(file_get_contents('/cipi/id_rsa'));

        if (!$ssh->login($alias->server->username, $key)) {
            // If login failed, default back to password.
            if (!$ssh->login($alias->server->username, $alias->server->password)) {
                $messagge = 'There was a problem with server connection. Try later!';
                return view('generic', compact('profile','messagge'));
            }
        }
        $ssh->setTimeout(60);
        $response = $ssh->exec('echo '.$alias->server->password.' | sudo -S sudo sh /cipi/ssl.sh -d '.$alias->domain);

        if(strpos($response, '###CIPI###') === false) {
            return abort(403);    
        }

        $response = explode('###CIPI###', $response);
        if($response[1] == "Ok\n" && Helper::sslcheck($alias->domain) == 1) {  
            die("OK");
        } else {
            return abort(403);
        }

    }



}
