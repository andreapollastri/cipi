<?php

namespace App\Console\Commands;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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

        // 2021-12-09 patch
        $servers = Server::where('build', '<', '202112091')->get();

        foreach ($servers as $server) {
            $ssh = new SSH2($server->ip, 22);
            $ssh->login('cipi', $server->password);
            $ssh->setTimeout(360);
            $ssh->exec('echo '.$server->password.' | sudo -S sudo wget '.config('app.url').'/sh/client-patch/202112091');
            $ssh->exec('echo '.$server->password.' | sudo -S sudo dos2unix 202112091');
            $ssh->exec('echo '.$server->password.' | sudo -S sudo bash 202112091');
            $ssh->exec('echo '.$server->password.' | sudo -S sudo unlink 202112091');
            $ssh->exec('exit');

            $server->build = '202112091';
            $server->save();
        }

         // 2021-12-10 and 2021-12-09 patches
         $servers = Server::where('build', '<', '202112101')->get();

         foreach ($servers as $server) {
             $ssh = new SSH2($server->ip, 22);
             $ssh->login('cipi', $server->password);
             $ssh->setTimeout(360);
             $ssh->exec('echo '.$server->password.' | sudo -S sudo wget '.config('app.url').'/sh/client-patch/202112101');
             $ssh->exec('echo '.$server->password.' | sudo -S sudo dos2unix 202112101');
             $ssh->exec('echo '.$server->password.' | sudo -S sudo bash 202112101');
             $ssh->exec('echo '.$server->password.' | sudo -S sudo unlink 202112101');
             $ssh->exec('exit');

             $server->build = '202112101';
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
