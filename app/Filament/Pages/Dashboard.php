<?php

namespace App\Filament\Pages;

use App\Helpers\Scripts;
use Filament\Actions\Action;
use Filament\Notifications\Actions\ActionGroup;
use Filament\Notifications\Notification;
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
                Action::make('edit-server-name')
                    ->label('Edit Server Name')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (): array => [
                        'serverName' => config('panel.serverName'),
                    ])
                    ->form([
                        \Filament\Forms\Components\TextInput::make('serverName')
                            ->label('Name')
                            ->hint('The name of the server, e.g. "Production Server", "Staging Server", etc.')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        Scripts::updateServerName($data['serverName']);

                        Notification::make('')
                            ->title('Server name updated successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('reset-server-password')
                    ->label('Reset Server Password')
                    ->icon('heroicon-o-key'),
            ]),
        ];
    }
}
