<?php

namespace App\Jobs;

use App\Helpers\EnvHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateServerName implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $name
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        EnvHelper::update('PANEL_SERVER_NAME', 'panel.serverName', $this->name);
    }
}
