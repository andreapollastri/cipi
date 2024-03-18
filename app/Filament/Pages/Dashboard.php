<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Actions\ActionGroup;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('restart-nginx')
                    ->label('Restart nginx')
                    ->icon('heroicon-o-arrow-path'),
                Action::make('restart-php')
                    ->label('Restart PHP-FPM')
                    ->icon('heroicon-o-arrow-path'),
                Action::make('restart-mysql')
                    ->label('Restart MySql')
                    ->icon('heroicon-o-arrow-path'),
                Action::make('restart-redis')
                    ->label('Restart Redis')
                    ->icon('heroicon-o-arrow-path'),
                Action::make('restart-supervisor')
                    ->label('Restart Supervisor')
                    ->icon('heroicon-o-arrow-path'),
                Action::make('restart-supervisor')
                    ->label('Edit Server Name')
                    ->icon('heroicon-o-pencil-square'),
                Action::make('restart-supervisor')
                    ->label('Reset Server Password')
                    ->icon('heroicon-o-key'),
                    Action::make('restart-supervisor'),

            ]),
        ];
    }
}
