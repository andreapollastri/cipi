<?php

namespace App\Console\Commands;

use App\Helpers\Scripts;
use App\Models\Stat;
use Illuminate\Console\Command;

class CheckServerStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:check-server-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check the server status and store values into database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking server status...');

        $cpu = Scripts::getCpuStatus();
        $ram = Scripts::getRamStatus();
        $hdd = Scripts::getHddStatus();

        $this->table(
            ['CPU', 'RAM', 'HDD'],
            [[$cpu.'%', $ram.'%', $hdd]]
        );

        Stat::create([
            'cpu' => $cpu,
            'ram' => $ram,
            'hdd' => $hdd,
        ]);

        $this->line('Data stored successfully.');

        $this->line('Deleting old records...');

        Stat::whereDate('created_at', '<=', now()->subDays(1))->delete();

        $this->info('Task completed.');
    }
}
