<?php

namespace App\Jobs;

use App\Helpers\EnvHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateServerName implements ShouldQueue
{
    use Queueable;

    public string $name;

    /**
     * Create a new job instance.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        EnvHelper::update('CIPI_SERVER_NAME', 'panel.serverName', $this->name);
    }
}
