<?php

namespace App\Filament\Widgets;

use App\Models\Stat as StatModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $ip = '123.123.123.123'; // TODO: Get server IP
        $name = 'Staging VPS'; // TODO: Get server name
        $sites = 23; // TODO: Get number of sites
        $stats = StatModel::latest()->first();
        $chart = StatModel::limit(120)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            Stat::make('IP', $ip)
                ->description('Server IP')
                ->descriptionIcon('heroicon-m-globe-alt'),
            Stat::make('Name', $name)
                ->description('Server name')
                ->descriptionIcon('heroicon-m-rocket-launch'),
            Stat::make('Sites', $sites)
                ->description('Hosted sites')
                ->descriptionIcon('heroicon-m-computer-desktop'),
            Stat::make('CPU', $stats->cpu.'%')
                ->description('CPU usage')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->chart($chart->pluck('cpu')->toArray()),
            Stat::make('RAM', $stats->ram.'%')
                ->description('RAM usage')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->chart($chart->pluck('ram')->toArray()),
            Stat::make('HDD', $stats->hdd)
                ->description('HDD usage')
                ->descriptionIcon('heroicon-m-server'),

        ];
    }
}
