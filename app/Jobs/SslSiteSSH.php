<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SslSiteSSH implements ShouldQueue
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

        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo fuser -k 80/tcp');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo fuser -k 443/tcp');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo ufw disable');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo certbot --nginx -d '.$this->site->domain.' --non-interactive --agree-tos --register-unsafely-without-email');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec("echo ".$this->site->server->password." | sudo -S sudo sed -i 's/443 ssl/443 ssl http2/g' /etc/nginx/sites-enabled/".$this->site->username.".conf");
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo ufw --force enable');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        foreach ($this->site->aliases as $alias) {
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo fuser -k 80/tcp');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo fuser -k 443/tcp');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo ufw disable');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo certbot --nginx -d '.$alias->domain.' --non-interactive --agree-tos --register-unsafely-without-email');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
            $ssh->exec("echo ".$this->site->server->password." | sudo -S sudo sed -i 's/443 ssl/443 ssl http2/g' /etc/nginx/sites-enabled/".$alias->domain.".conf");
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo ufw --force enable');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        }
        $ssh->exec('exit');
    }
}
