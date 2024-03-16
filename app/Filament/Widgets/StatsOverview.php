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
        $stats = StatModel::latest()->first();

        $chart = StatModel::limit(120)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
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
