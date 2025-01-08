<?php

namespace App\Filament;

use App\Filament\Plugins\BreezyCorePlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CipiPanelProvider extends PanelProvider
{
    protected string $id = 'cipi';

    protected string $path = 'cipi';

    protected string $favicon = '/logo.png';

    protected string $brandLogo = '/logo.png';

    protected string $brandLogoHeight = '2.8rem';

    protected string $brandName = 'Cipi Control Panel';

    protected string $font = 'Quicksand';

    protected bool $breadcrumbs = false;

    protected bool $topNavigation = false;

    protected bool $unsavedChangesAlerts = true;

    protected array $colors = [
        'primary' => Color::Indigo,
    ];

    protected array $authMiddleware = [
        Authenticate::class,
    ];

    protected array $middleware = [
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
    ];

    protected function getPlugins()
    {
        return [
            BreezyCorePlugin::boot(),
        ];
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->login()
            ->id($this->id)
            ->path($this->path)
            ->font($this->font)
            ->breadcrumbs($this->breadcrumbs)
            ->unsavedChangesAlerts($this->unsavedChangesAlerts)
            ->topNavigation($this->topNavigation)
            ->favicon($this->favicon)
            ->brandLogo($this->brandLogo)
            ->brandLogoHeight($this->brandLogoHeight)
            ->brandName($this->brandName)
            ->colors($this->colors)
            ->middleware($this->middleware)
            ->authMiddleware($this->authMiddleware)
            ->plugins($this->getPlugins());
    }
}
