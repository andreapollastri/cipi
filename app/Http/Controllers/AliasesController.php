<?php

namespace App\Http\Controllers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Http\Request;
use App\Application;
use App\Alias;
use phpseclib\Net\SSH2 as SSH;

class AliasesController extends Controller {

    protected $url;

    public function __construct(UrlGenerator $url) {
        $this->url = $url;
    }

    public function index() {
        $aliases = Alias::orderBy('domain')->orderBy('application_id')->with('application')->get();
        return view('aliases', compact('aliases'));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'domain' => 'required',
            'application_id' => 'required'
        ]);
        $application = Application::where('id', $request->application_id)->with('server')->with('aliases')->firstOrFail();
        if(Application::where('server_id', $application->server_id)->where('domain', $request->domain)->first()) {
            $request->session()->flash('alert-error', 'This domain is already taken on this server');
            return redirect('/aliases');
        }
        $checks = Alias::where('domain', $request->domain)->with('application')->get();
        foreach($checks as $check) {
            if($check->application->server_id = $application->server_id) {
                $request->session()->flash('alert-error', 'This domain is already taken on this server');
                return redirect('/aliases');
            }
        }
        $aliascode = sha1(uniqid().$request->domain.microtime().$request->application_id);
        Alias::create([
            'aliascode'     => $aliascode,
            'domain'        => $request->domain,
            'application_id'=> $request->application_id
        ]);
        $ssh = New SSH($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/aliases');
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/alias-add.sh -d '.$request->domain.' -a '.$application->appcode.' -r '.$this->url->to('/'));
        if(strpos($response, '###CIPI###') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server scripts.');
            return redirect('/aliases');
        }
        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server scripts.');
            return redirect('/aliases');
        }
        $request->session()->flash('alert-success', 'Alias '.$request->domain.' has been added!');
        return redirect('/aliases');
    }

    public function destroy(Request $request) {
        $this->validate($request, [
            'aliascode' => 'required',
        ]);
        $alias = Alias::where('aliascode', $request->aliascode)->with('application')->firstOrFail();
        $ssh = New SSH($alias->application->server->ip, $alias->application->server->port);
        if(!$ssh->login($alias->application->server->username, $alias->application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/aliases');
        }
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$alias->application->server->password.' | sudo -S sudo sh /cipi/alias-del.sh -d '.$alias->domain);
        $request->session()->flash('alert-success', 'Alias '.$alias->domain.' has been removed!');
        $alias->delete();
        return redirect('/aliases');
    }

    public static function sslcheck($domain) {
        $ssl_check = @fsockopen('ssl://' . $domain, 443, $errno, $errstr, 30);
        $res = !! $ssl_check;
        if($ssl_check) { fclose($ssl_check); }
        return $res;
    }

    public function ssl($aliascode) {
        $alias = Alias::where('aliascode', $aliascode)->with('application')->firstOrFail();
        $ssh = New SSH($alias->application->server->ip, $alias->application->server->port);
        if(!$ssh->login($alias->application->server->username, $alias->application->server->password)) {
            return abort(403);
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$alias->application->server->password.' | sudo -S sudo sh /cipi/ssl.sh -d '.$alias->domain.' -c '.$alias->domain);
        if(strpos($response, '###CIPI###') === false) {
            abort(500);
        }
        $response = explode('###CIPI###', $response);
        if($response[1] == "Ok\n" && $this->sslcheck($alias->domain)) {
            return 'OK';
        } else {
            return abort(500);
        }
    }

}
