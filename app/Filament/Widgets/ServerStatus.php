<?php

namespace App\Filament\Widgets;

use App\Models\Stat as StatModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServerStatus extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $stats = StatModel::latest()->first();
        $chart = StatModel::limit(120)->orderBy('created_at', 'desc')->get();

        return [
            Stat::make(__('Server'), config('panel.serverName'))
                ->icon('fas-server')
                ->description(__('Copy name to clipboard'))
                ->descriptionIcon('far-copy')
                ->extraAttributes([
                    'onclick' => "javascript:navigator.clipboard.writeText('".config('panel.serverName')."');",
                    'style' => 'cursor: pointer;',
                ]),
            Stat::make(__('IP'), config('panel.serverIp'))
                ->icon('fas-network-wired')
                ->description(__('Copy IP to clipboard'))
                ->descriptionIcon('far-copy')
                ->extraAttributes([
                    'onclick' => "javascript:navigator.clipboard.writeText('".config('panel.serverIp')."');",
                    'style' => 'cursor: pointer;',
                ]),
            Stat::make(__('Version'), config('panel.serverVersion'))
                ->icon('fas-code-branch')
                ->description(__('Cipi Panel Version')),
            Stat::make('CPU', (isset($stats->cpu)) ? $stats->cpu.'%' : '0'.'%')
                ->chart($chart->pluck('cpu')->reverse()->toArray())
                ->description(__('CPU Real Time Usage'))
                ->descriptionColor('default')
                ->icon('fas-microchip')
                ->color('primary'),
            Stat::make('RAM', (isset($stats->ram)) ? $stats->ram.'%' : '0'.'%')
                ->chart($chart->pluck('ram')->reverse()->toArray())
                ->description(__('RAM Real Time Usage'))
                ->descriptionColor('default')
                ->icon('fas-memory')
                ->color('primary'),
            Stat::make('HDD', (isset($stats->ram)) ? $stats->ram.'%' : '0'.'%')
                ->chart($chart->pluck('hdd')->reverse()->toArray())
                ->descriptionColor('default')
                ->description(__('HDD Real Time Usage'))
                ->icon('far-hard-drive')
                ->color('primary'),
        ];
    }
}
