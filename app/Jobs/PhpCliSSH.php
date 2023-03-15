<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class PhpCliSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;

    protected $php;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server, $php)
    {
        $this->server = $server;
        $this->php = $php;
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
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo update-alternatives --set php /usr/bin/php'.$this->php);
        $ssh->exec('exit');
    }
}
