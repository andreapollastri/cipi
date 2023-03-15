<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class PanelDomainAddSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server)
    {
        $this->server = $server;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ssh = new SSH2($this->server->ip, 22);
        $ssh->login('cipi', $this->server->password);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo wget '.config('app.url').'/conf/panel -O /etc/nginx/sites-available/panel.conf');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo dos2unix /etc/nginx/sites-available/panel.conf');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo ln -s /etc/nginx/sites-available/panel.conf /etc/nginx/sites-enabled/panel.conf');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');
    }
}
