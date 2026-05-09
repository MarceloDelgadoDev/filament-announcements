<?php

namespace Marcelodelgado\Announcements\Filament\Resources;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Marcelodelgado\Announcements\Enums\AnnouncementType;
use Marcelodelgado\Announcements\Filament\Resources\AnnouncementResource\Pages;
use Marcelodelgado\Announcements\Models\Announcement;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'announcements';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    public static function getNavigationLabel(): string
    {
        return __('announcements::filament.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('announcements::filament.navigation.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('announcements::filament.navigation.plural');
    }

    protected static function passesPackagePermissionCheck(): bool
    {
        $check = config('announcements.permission_check');

        if ($check === null) {
            return true;
        }

        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if (is_string($check)) {
            return Gate::forUser($user)->allows($check);
        }

        if ($check instanceof \Closure) {
            return (bool) $check($user);
        }

        return false;
    }

    public static function canViewAny(): bool
    {
        return static::passesPackagePermissionCheck() && parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::passesPackagePermissionCheck() && parent::canCreate();
    }

    public static function canEdit(Model $record): bool
    {
        return static::passesPackagePermissionCheck() && parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::passesPackagePermissionCheck() && parent::canDelete($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::passesPackagePermissionCheck() && parent::canDeleteAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('announcements::filament.form.title.label'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('announcements::filament.form.title.helper')),
                        Select::make('type')
                            ->label(__('announcements::filament.form.type.label'))
                            ->options(collect(AnnouncementType::cases())->mapWithKeys(fn (AnnouncementType $type) => [$type->value => $type->label()]))
                            ->required()
                            ->native(false)
                            ->helperText(__('announcements::filament.form.type.helper')),
                    ]),
                Grid::make(1)
                    ->schema([
                        Textarea::make('body')
                            ->label(__('announcements::filament.form.body.label'))
                            ->required()
                            ->rows(4)
                            ->helperText(__('announcements::filament.form.body.helper')),
                    ]),
                Grid::make(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('announcements::filament.form.is_active.label'))
                            ->default(true)
                            ->helperText(__('announcements::filament.form.is_active.helper')),
                        Toggle::make('is_dismissible')
                            ->label(__('announcements::filament.form.is_dismissible.label'))
                            ->default(true)
                            ->helperText(__('announcements::filament.form.is_dismissible.helper')),
                    ]),
                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label(__('announcements::filament.form.starts_at.label'))
                            ->seconds(false)
                            ->helperText(__('announcements::filament.form.starts_at.helper')),
                        DateTimePicker::make('expires_at')
                            ->label(__('announcements::filament.form.expires_at.label'))
                            ->seconds(false)
                            ->helperText(__('announcements::filament.form.expires_at.helper')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('announcements::filament.form.title.label'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('type')
                    ->label(__('announcements::filament.form.type.label'))
                    ->badge()
                    ->formatStateUsing(fn (AnnouncementType $state): string => $state->label())
                    ->color(fn (AnnouncementType $state): string => match ($state) {
                        AnnouncementType::Danger => 'danger',
                        AnnouncementType::Warning => 'warning',
                        AnnouncementType::Info => 'info',
                        AnnouncementType::Success => 'success',
                    }),
                ToggleColumn::make('is_active')
                    ->label(__('announcements::filament.table.column_active')),
                TextColumn::make('starts_at')
                    ->label(__('announcements::filament.form.starts_at.label'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label(__('announcements::filament.form.expires_at.label'))
                    ->dateTime()
                    ->suffix(function (Column $column): ?string {
                        /** @var Announcement|null $record */
                        $record = $column->getRecord();

                        return $record instanceof Announcement && $record->isExpiredByDate()
                            ? __('announcements::filament.table.expired')
                            : null;
                    })
                    ->color(function (Column $column): ?string {
                        /** @var Announcement|null $record */
                        $record = $column->getRecord();

                        return $record instanceof Announcement && $record->isExpiredByDate()
                            ? 'danger'
                            : null;
                    }),
                TextColumn::make('created_at')
                    ->label(__('announcements::filament.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('announcements::filament.form.type.label'))
                    ->options(collect(AnnouncementType::cases())->mapWithKeys(fn (AnnouncementType $type) => [$type->value => $type->label()])),
                Filter::make('inactive_manual')
                    ->label(__('announcements::filament.table.filter_inactive_manual'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false)),
                Filter::make('expired_by_date')
                    ->label(__('announcements::filament.table.filter_expired_by_date'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('expires_at')->where('expires_at', '<=', now())),
                Filter::make('scheduled_future')
                    ->label(__('announcements::filament.table.filter_scheduled_future'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('starts_at')->where('starts_at', '>', now())),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('announcements::filament.table.bulk_activate'))
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label(__('announcements::filament.table.bulk_deactivate'))
                        ->icon(Heroicon::OutlinedXCircle)
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
