<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EditSitePhpSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;
    protected $oldphp;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site, $oldphp)
    {
        $this->site = $site;
        $this->oldphp = $oldphp;
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
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i "php'.$this->oldphp.'-fpm" "php'.$this->site->php.'-fpm" /etc/nginx/sites-available/'.$this->site->username.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i "php'.$this->oldphp.'-fpm" "php'.$this->site->php.'-fpm" /etc/nginx/sites-enabled/'.$this->site->username.'.conf');
        foreach ($this->site->aliases as $alias) {
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i "php'.$this->oldphp.'-fpm" "php'.$this->site->php.'-fpm" /etc/nginx/sites-available/'.$alias->domain.'.conf');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i "php'.$this->oldphp.'-fpm" "php'.$this->site->php.'-fpm" /etc/nginx/sites-enabled/'.$alias->domain.'.conf');
        }
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo mv /etc/php/'.$this->oldphp.'/fpm/pool.d/'.$this->site->username.'.conf /etc/php/'.$this->site->php.'/fpm/pool.d/'.$this->site->username.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl "listen = /run/php/php'.$this->oldphp.'-fpm-'.$this->site->username.'.sock" "listen = /run/php/php'.$this->site->php.'-fpm-'.$this->site->username.'.sock" /etc/php/'.$this->site->php.'/fpm/pool.d/'.$this->site->username.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo service php'.$this->oldphp.'-fpm restart');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo service php'.$this->site->php.'-fpm restart');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');
    }
}
