<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SiteUserPwdSSH implements ShouldQueue
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink sitepass');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo wget '.config('app.url').'/sh/sitepass');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo dos2unix sitepass');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo bash sitepass  -u '.$this->site->username.' -p '.$this->site->password);
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink sitepass');
        $ssh->exec('exit');
    }
}
