<?php

namespace Marcelodelgado\Announcements\Filament\Resources\AnnouncementResource\Pages;

use Marcelodelgado\Announcements\Filament\Resources\AnnouncementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
