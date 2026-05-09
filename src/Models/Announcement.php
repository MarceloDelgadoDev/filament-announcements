<?php

namespace Marcelodelgado\Announcements\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property \Marcelodelgado\Announcements\Enums\AnnouncementType $type
 * @property bool $is_active
 * @property bool $is_dismissible
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 */
class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'type',
        'is_active',
        'is_dismissible',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => \Marcelodelgado\Announcements\Enums\AnnouncementType::class,
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            'announcement_user'
        )->withPivot('dismissed_at');
    }

    public function scopeVisibleForDashboard(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE type WHEN 'danger' THEN 1 WHEN 'warning' THEN 2 WHEN 'info' THEN 3 WHEN 'success' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at');
    }

    public function isExpiredByDate(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
