<?php

namespace App\Filament\Admin\Pages;

use App\Mail\AdminBulkAnnouncementMail;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class BulkNotifications extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.admin.pages.bulk-notifications';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_configuration');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_bulk_notifications');
    }

    public function getTitle(): string
    {
        return __('admin.nav_bulk_notifications');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.send_bulk_notifications') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'audience' => 'active_students',
            'subject'  => '',
            'body'     => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('audience')
                    ->label(__('admin.bulk_audience'))
                    ->options([
                        'active_students' => __('admin.bulk_audience_active_students'),
                        'all_students'    => __('admin.bulk_audience_all_students'),
                        'lecturers'       => __('admin.bulk_audience_lecturers'),
                    ])
                    ->required(),
                TextInput::make('subject')
                    ->label(__('admin.bulk_subject'))
                    ->required()
                    ->maxLength(200),
                Textarea::make('body')
                    ->label(__('admin.bulk_body'))
                    ->required()
                    ->rows(8)
                    ->maxLength(5000),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $query = User::query()->where('is_active', true);

        match ($data['audience']) {
            'all_students'    => $query->where('role', 'student'),
            'active_students' => $query->where('role', 'student'),
            'lecturers'       => $query->whereIn('role', ['lecturer', 'admin', 'super_admin']),
            default           => $query->where('role', 'student'),
        };

        $recipients = $query->get();
        $sent = 0;

        foreach ($recipients as $user) {
            Mail::to($user)->queue(new AdminBulkAnnouncementMail(
                $user,
                $data['subject'],
                $data['body'],
            ));
            $sent++;
        }

        Notification::make()
            ->title(__('admin.bulk_sent_success', ['count' => $sent]))
            ->success()
            ->send();

        $this->form->fill([
            'audience' => $data['audience'],
            'subject'  => '',
            'body'     => '',
        ]);
    }
}
