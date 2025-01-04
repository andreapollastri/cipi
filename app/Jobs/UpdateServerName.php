<?php

namespace App\Jobs;

use App\Cipi\EnvUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateServerName implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $name
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle($name): void
    {
        EnvUpdate::run('CIPI_SERVER_NAME', 'cipi.server_name', $name);
    }
}
