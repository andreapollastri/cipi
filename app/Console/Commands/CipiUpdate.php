<?php

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Command;
use phpseclib3\Net\SSH2;

class CipiUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cipi:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Cipi';

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
        // 2021-12-18 patch
        $servers = Server::where('build', '<', '202112181')->get();

        foreach ($servers as $server) {
            $ssh = new SSH2($server->ip, 22);
            $ssh->login('cipi', $server->password);
            $ssh->setTimeout(360);
            $ssh->exec('echo '.$server->password.' | sudo -S sudo wget '.config('app.url').'/sh/client-patch/202112181');
            $ssh->exec('echo '.$server->password.' | sudo -S sudo dos2unix 202112181');
            $ssh->exec('echo '.$server->password.' | sudo -S sudo bash 202112181');
            $ssh->exec('echo '.$server->password.' | sudo -S sudo unlink 202112181');
            $ssh->exec('exit');

            $server->build = '202112181';
            $server->save();
        }

        $server = Server::where('default', 1)->first();

        $ssh = new SSH2($server->ip, 22);
        $ssh->login('cipi', $server->password);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$server->password.' | sudo -s cd /var/www/html/utility/cipi-update && sh run.sh');
        $ssh->exec('exit');

        return 0;
    }
}
