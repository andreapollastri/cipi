<?php

namespace App\Console\Commands;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ActiveSetupCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cipi:activesetupcount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Active Setup Count';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        sleep(rand(0, 9));

        // Useful to daily count how many Cipi Control Panel installations are active
        // around the world and show the total value into cipi.sh official website
        try {
            file_get_contents(config('cipi.activesetupcount'));
        } catch (\Throwable $th) {
            //
        }

        return 0;
    }
}
