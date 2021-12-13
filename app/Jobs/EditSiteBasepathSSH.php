<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EditSiteBasepathSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;
    protected $oldbasepath;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site, $oldbasepath)
    {
        $this->site = $site;
        $this->oldbasepath = $oldbasepath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->oldbasepath) {
            $oldbasepath = '/home/'.$this->site->username.'/web/'.$this->oldbasepath;
        } else {
            $oldbasepath = '/home/'.$this->site->username.'/web';
        }

        if ($this->site->basepath) {
            $basepath = '/home/'.$this->site->username.'/web/'.$this->site->basepath;
        } else {
            $basepath = '/home/'.$this->site->username.'/web';
        }

        $ssh = new SSH2($this->site->server->ip, 22);
        $ssh->login('cipi', $this->site->server->password);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i -w "root '.$oldbasepath.'" "root '.$basepath.'" /etc/nginx/sites-available/'.$this->site->username.'.conf');
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');
    }
}
