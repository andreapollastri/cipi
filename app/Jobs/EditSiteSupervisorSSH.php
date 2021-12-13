<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EditSiteSupervisorSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;
    protected $oldsupervisor;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site, $oldsupervisor)
    {
        $this->site = $site;
        $this->oldsupervisor = $oldsupervisor;
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink /etc/supervisor/conf.d/'.$this->site->username);
        if ($this->site->supervisor) {
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo wget '.config('app.url').'/conf/supervisor -O /etc/supervisor/conf.d/'.$this->site->username.'.conf');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i "???USER???" "'.$this->site->username.'" /etc/supervisor/conf.d/'.$this->site->username.'.conf');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i "???SCRIPT???" "'.$this->site->supervisor.'" /etc/supervisor/conf.d/'.$this->site->username.'.conf');
        }
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo supervisorctl reread');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo supervisorctl update');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo supervisorctl start all');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo service supervisor restart');
        $ssh->exec('exit');
    }
}
