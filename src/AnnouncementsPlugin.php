<?php

namespace Marcelodelgado\Announcements;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Marcelodelgado\Announcements\Filament\Resources\AnnouncementResource;
use Marcelodelgado\Announcements\Filament\Widgets\AnnouncementsWidget;

class AnnouncementsPlugin implements Plugin
{
    protected string $pollingInterval = '60s';

    public static function make(): static
    {
        return new static;
    }

    public static function get(): static
    {
        /** @var static */
        return Filament::getCurrentPanel()->getPlugin('announcements');
    }

    public function getId(): string
    {
        return 'announcements';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([AnnouncementResource::class])
            ->widgets([AnnouncementsWidget::class]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function pollingInterval(string $interval): static
    {
        $this->pollingInterval = $interval;

        return $this;
    }

    public function getPollingInterval(): string
    {
        return $this->pollingInterval;
    }
}
