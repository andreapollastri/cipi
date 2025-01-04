<?php

namespace App\Filament\Plugins;

use App\Helpers\PasswordHelper;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class BreezyCorePlugin
{
    public static function boot()
    {
        return BreezyCore::make()
            ->myProfile(
                shouldRegisterUserMenu: true,
                shouldRegisterNavigation: false,
                navigationGroup: null,
                hasAvatars: false,
                slug: 'profile'
            )
            ->passwordUpdateRules(
                rules: [
                    PasswordHelper::rules(),
                ],
                requiresCurrentPassword: true,
            )
            ->enableTwoFactorAuthentication(
                force: false,
            )
            ->enableSanctumTokens(
                permissions: [
                    'view',
                    'create',
                    'update',
                    'delete',
                ]
            );
    }
}
