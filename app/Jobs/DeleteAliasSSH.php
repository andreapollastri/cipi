<?php

namespace App\Jobs;

use App\Models\Alias;
use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeleteAliasSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;
    protected $alias;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site, $alias)
    {
        $this->site = $site;
        $this->alias = $alias;
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink /etc/nginx/sites-enabled/'.$this->alias->domain.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo unlink /etc/nginx/sites-available/'.$this->alias->domain.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');

        $this->alias->delete();
    }
}
