<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class SiteDbPwdSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;

    protected $oldpassword;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site, $oldpassword)
    {
        $this->site = $site;
        $this->oldpassword = $oldpassword;
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo mysqladmin -u '.$this->site->username.' -p'.$this->oldpassword.' password '.$this->site->database.'');
        $ssh->exec('exit');
    }
}
