<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Application;
use App\Server;
use App\Alias;
use phpseclib\Net\SSH2 as SSH;
use PDF;

class ApplicationsController extends Controller {

    public function index() {
        $aliases = Alias::with('server')->with('application')->get();
        return view('aliases', compact('aliases'));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'domain' => 'required',
            'application_id' => 'required'
        ]);
        $application = Application::find($request->application_id);
        if(!$application) {
            abort(403);
        }
        if(Application::where('server_id', $request->server)->where('domain', $request->domain)->first()) {
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
        $application = Alias::create([
            'domain'        => $request->domain,
            'application_id'=> $request->server_id
        ]);
        $ssh = New SSH($application->server->ip, $application->server->port);
        if(!$ssh->login($application->server->username, $application->server->password)) {
            $request->session()->flash('alert-error', 'There was a problem with server connection.');
            return redirect('/aliases');
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$application->password.' | sudo -S sudo sh /cipi/alias-add.sh -d '.$request->domain.' -a '.$application->appcode);
        $response = explode('###CIPI###', $response);
        if(strpos($response[1], 'Ok') === false) {
            $request->session()->flash('alert-error', 'There was a problem with server scripts.');
            return redirect('/applications');
        }
        $request->session()->flash('alert-success', 'Alias '.$request->domain.' has been added!');
        return redirect('/aliases');
    }

    public function destroy(Request $request) {
        $this->validate($request, [
            'id' => 'required',
        ]);
        $alias = Alias::find($request->id);
        if(!$alias) {
            return abort(403);
        }
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
        $alias = Alias::find($id);
        if(!$alias) {
            return abort(403);
        }
        $ssh = New SSH($alias->application->server->ip, $alias->application->server->port);
        if(!$ssh->login($alias->application->server->username, $alias->application->server->password)) {
            return abort(403);
        }
        $ssh->setTimeout(360);
        $response = $ssh->exec('echo '.$alias->application->server->password.' | sudo -S sudo sh /cipi/ssl.sh -d '.$alias->domain);
        $response = explode('###CIPI###', $response);
        if($response[1] == "Ok\n" && $this->sslcheck($alias->domain)) {
            return 'OK';
        } else {
            return abort(500);
        }
    }

}
