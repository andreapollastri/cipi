<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Notifications\Actions\ActionGroup;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('Restart nginx'),
                Action::make('Restart PHP-fpm'),
                Action::make('Restart Mysql'),
                Action::make('Restart Redis'),
                Action::make('Restart Supervisor'),
            ])
        ];
    }
}
