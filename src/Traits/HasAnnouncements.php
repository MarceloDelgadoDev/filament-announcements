<?php

namespace Marcelodelgado\Announcements\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Marcelodelgado\Announcements\Models\Announcement;

trait HasAnnouncements
{
    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')
            ->withPivot('dismissed_at');
    }
}
