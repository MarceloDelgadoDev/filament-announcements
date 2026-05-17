<?php

namespace Marcelodelgado\Announcements\Filament\Resources\AnnouncementResource\Pages;

use Marcelodelgado\Announcements\Filament\Resources\AnnouncementResource;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
