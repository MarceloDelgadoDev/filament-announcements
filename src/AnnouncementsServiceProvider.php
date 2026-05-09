<?php

namespace Marcelodelgado\Announcements;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AnnouncementsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'announcements';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigrations([
                'create_announcements_table',
                'create_announcement_user_table',
            ])
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        //
    }
}
