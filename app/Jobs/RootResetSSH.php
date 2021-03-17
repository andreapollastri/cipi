<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class RootResetSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;
    protected $newpassword;
    protected $oldpassword;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server, $newpassword, $oldpassword)
    {
        $this->server = $server;
        $this->newpassword = $newpassword;
        $this->oldpassword = $oldpassword;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ssh = new SSH2($this->server->ip, 22);
        $ssh->login('cipi', $this->oldpassword);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$this->oldpassword.' | sudo -S sudo unlink rootreset');
        $ssh->exec('echo '.$this->oldpassword.' | sudo -S sudo wget '.config('app.url').'/sh/servers/rootreset');
        $ssh->exec('echo '.$this->oldpassword.' | sudo -S sudo dos2unix rootreset');
        $ssh->exec('echo '.$this->oldpassword.' | sudo -S sudo bash rootreset -p '.$this->newpassword);
        $ssh->exec('echo '.$this->newpassword.' | sudo -S sudo unlink rootreset');
        $ssh->exec('exit');
    }
}
