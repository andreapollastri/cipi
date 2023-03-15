<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class DeleteSiteSSH implements ShouldQueue
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink delsite');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo wget '.config('app.url').'/sh/delsite');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo dos2unix delsite');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo bash delsite -dbr '.$this->site->server->database.' -u '.$this->site->username.' -p '.$this->site->php);
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink delsite');
        if ($this->site->aliases) {
            foreach ($this->site->aliases as $alias) {
                $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink /etc/nginx/sites-enabled/'.$alias->domain.'.conf');
                $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink /etc/nginx/sites-available/'.$alias->domain.'.conf');
            }
        }
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');

        $this->site->delete();
    }
}
