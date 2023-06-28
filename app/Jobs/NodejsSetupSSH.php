<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class NodejsSetupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $site;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ssh = new SSH2($this->site->server->ip, 22);
        $ssh->login('cipi', $this->site->server->password);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink newnodejs');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo wget -O newnodejs  '.config('app.url').'/sh/start_nodejs');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo dos2unix newnodejs');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo bash newnodejs -u '.$this->site->username.' -p '.str_pad($this->site->id,4,0,STR_PAD_RIGHT).' -r '.$this->site->node_script);
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink newnodejs');
        $ssh->exec('exit');
    }
}
