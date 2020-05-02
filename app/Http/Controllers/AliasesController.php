<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Application;
use App\Alias;
use phpseclib\Net\SSH2 as SSH;

class AliasesController extends Controller {

    public function index() {
        $aliases = Alias::orderBy('domain')->orderBy('application_id')->with('application')->get();
        return view('aliases', compact('aliases'));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'domain' => 'required',
            'application_id' => 'required'
        ]);
        $application = Application::find($request->application_id)->with('server')->with('aliases')->firstOrFail();
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
        Alias::create([
            'domain'        => $request->domain,
            'application_id'=> $request->application_id
        ]);
        $ssh = New SSH($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/aliases');
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$application->server->password.' | sudo -S sudo sh /cipi/alias-add.sh -d '.$request->domain.' -a '.$application->appcode);
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
            'id' => 'required|exists:aliases,id',
        ]);
        $alias = Alias::find($request->id)->with('application')->firstOrFail();
        $ssh = New SSH($alias->application->server->ip, $alias->application->server->port);
        if(!$ssh->login($alias->application->server->username, $alias->application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/aliases');
        }
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$alias->application->server->password.' | sudo -S sudo sh /cipi/alias-del.sh -d '.$alias->domain);
        $alias->delete();
        $request->session()->flash('alert-success', 'Alias has been removed!');
        return redirect('/aliases');
    }

    public static function sslcheck($domain) {
        $ssl_check = @fsockopen('ssl://' . $domain, 443, $errno, $errstr, 30);
        $res = !! $ssl_check;
        if($ssl_check) { fclose($ssl_check); }
        return $res;
    }

    public function ssl($id) {
        $alias = Alias::find($id)->with('application')->firstOrFail();
        $ssh = New SSH($alias->application->server->ip, $alias->application->server->port);
        if(!$ssh->login($alias->application->server->username, $alias->application->server->password)) {
            return abort(403);
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$alias->application->server->password.' | sudo -S sudo sh /cipi/ssl.sh -d '.$alias->domain);
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
