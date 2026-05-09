<?php

namespace Marcelodelgado\Announcements\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Marcelodelgado\Announcements\AnnouncementsPlugin;
use Marcelodelgado\Announcements\Models\Announcement;

class AnnouncementsWidget extends Widget
{
    use CanPoll;

    protected static ?int $sort = -4;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected string $view = 'announcements::widgets.announcements-widget';

    public function mount(): void
    {
        $panel = Filament::getCurrentPanel();

        if ($panel && $panel->hasPlugin('announcements')) {
            $plugin = $panel->getPlugin('announcements');

            if ($plugin instanceof AnnouncementsPlugin) {
                $this->pollingInterval = $plugin->getPollingInterval();

                return;
            }
        }

        $this->pollingInterval = config('announcements.polling_interval', '60s');
    }

    /**
     * @return Collection<int, Announcement>
     */
    public function getAnnouncements(): Collection
    {
        $userId = auth()->id();

        return Announcement::query()
            ->visibleForDashboard()
            ->orderedForDisplay()
            ->whereDoesntHave('users', function ($query) use ($userId): void {
                $query->where('users.id', $userId)
                    ->whereNotNull('announcement_user.dismissed_at');
            })
            ->get();
    }

    public function dismiss(int $announcementId): void
    {
        $announcement = Announcement::query()->find($announcementId);

        if (! $announcement || ! $announcement->is_dismissible) {
            return;
        }

        DB::table('announcement_user')->updateOrInsert(
            [
                'announcement_id' => $announcementId,
                'user_id' => auth()->id(),
            ],
            ['dismissed_at' => now()],
        );
    }
}
