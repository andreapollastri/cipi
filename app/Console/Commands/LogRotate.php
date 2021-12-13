<?php

namespace App\Console\Commands;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use Illuminate\Console\Command;

class LogRotate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cipi:logrotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Site Log Rotation';

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
        $servers = Server::all();

        foreach ($servers as $server) {
            foreach ($server->sites as $site) {
                $ssh = new SSH2($server->ip, 22);
                $ssh->login('cipi', $server->password);
                $ssh->setTimeout(360);
                $ssh->exec('echo '.$server->password.' | sudo -S sudo unlink /home/'.$site->username.'/log/access_bk_'.date('N').'.log');
                $ssh->exec('echo '.$server->password.' | sudo -S sudo mv /home/'.$site->username.'/log/access.log /home/'.$site->username.'/log/access_bk_'.date('N').'.log');
                $ssh->exec('echo '.$server->password.' | sudo -S sudo unlink /home/'.$site->username.'/log/error_bk_'.date('N').'.log');
                $ssh->exec('echo '.$server->password.' | sudo -S sudo mv /home/'.$site->username.'/log/error.log /home/'.$site->username.'/log/error_bk_'.date('N').'.log');
                $ssh->exec('exit');
            }
        }

        return 0;
    }
}
