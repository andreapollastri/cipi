<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class NewAliasSSH implements ShouldQueue
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo wget '.config('app.url').'/conf/alias/'.$this->alias->alias_id.' -O /etc/nginx/sites-available/'.$this->alias->domain.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo dos2unix /etc/nginx/sites-available/'.$this->alias->domain.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo ln -s /etc/nginx/sites-available/'.$this->alias->domain.'.conf /etc/nginx/sites-enabled/'.$this->alias->domain.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo service php'.$this->site->php.'-fpm restart');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');
    }
}
