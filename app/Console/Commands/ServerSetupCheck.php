<?php

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use phpseclib3\Net\SSH2;

class ServerSetupCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servers:setupcheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Servers Setup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $servers = Server::where('status', 0)->get();

        foreach ($servers as $server) {
            try {
                $remote = Http::get('http://'.$server->ip.'/ping_'.$server->server_id.'.php');

                if ($remote->status() == 200) {
                    try {
                        $server->github_key = file_get_contents('http://'.$server->ip.'/ghkey_'.$server->server_id.'.php');

                        $ssh = new SSH2($server->ip, 22);
                        $ssh->setTimeout(360);
                        $ssh->exec('echo '.$server->password.' | sudo -s sudo unlink /var/www/html/ghkey_'.$server->server_id.'.php');
                        $ssh->exec('exit');
                    } catch (\Throwable $th) {
                        //
                    }

                    try {
                        $server->build = file_get_contents('http://'.$server->ip.'/build_'.$server->server_id.'.php');
                    } catch (\Throwable $th) {
                        $server->build = 0;
                    }

                    $server->status = 1;
                    $server->save();
                }
            } catch (\Throwable $th) {
                //
            }
        }

        return 0;
    }
}
