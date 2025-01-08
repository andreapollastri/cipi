<?php

namespace App\Filament\Pages;

use App\Cipi\Configuration;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return __('Dashboard');
    }

    public function getColumns(): int
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(
                [
                    Action::make('edit-server-name')
                        ->label('Server Name')
                        ->icon('heroicon-o-server-stack')
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
                            Configuration::updateServerName($data['serverName']);

                            Notification::make()
                                ->title('Server name updated successfully.')
                                ->success()
                                ->send();
                        }),
                    Action::make('edit-server-ip')
                        ->label('Server IP')
                        ->icon('heroicon-o-wifi'),
                    Action::make('edit-server-password')
                        ->label('Server Password')
                        ->icon('heroicon-o-key'),
                    Action::make('edit-panel-url')
                        ->label('Panel URL')
                        ->icon('heroicon-o-globe-alt'),
                ],
            )->icon('fas-circle-chevron-down'),
        ];
    }
}
