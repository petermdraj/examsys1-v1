<?php

namespace App\Filament\Creator\Pages;

use App\Settings\PlatformSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class Profile extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-user-circle';
    public static function getNavigationLabel(): string { return __('creator.nav_my_profile'); }
    public static function getNavigationGroup(): ?string { return __('creator.nav_group_account'); }
    public function getTitle(): string { return __('creator.nav_my_profile'); }
    protected static ?int    $navigationSort  = 90;
    protected static string  $view            = 'filament.creator.pages.profile';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->data = [
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'country_code'    => $user->country_code,
            'timezone'        => $user->timezone,
            'bio'             => $user->bio,
            'avatar'            => $user->avatar ? [$user->avatar] : null,
            'certificate_logo'  => $user->certificate_logo ? [$user->certificate_logo] : null,
            'payout_gateway'  => $user->payout_gateway,
            'payout_details'  => $user->payout_details ?? [],
            'current_password'  => '',
            'new_password'      => '',
            'new_password_confirmation' => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Profile')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('creator.profile_tab_info'))
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\FileUpload::make('avatar')
                                    ->label(__('creator.profile_photo'))
                                    ->image()
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('1:1')
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->directory('avatars'),

                                Forms\Components\FileUpload::make('certificate_logo')
                                    ->label(__('creator.profile_certificate_logo'))
                                    ->image()
                                    ->maxSize(1024)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                                    ->directory('certificate-logos')
                                    ->helperText(__('creator.profile_cert_logo_helper'))
                                    ->visible(function () {
                                        $plan = auth()->user()->activeSubscription()->with('plan')->first()?->plan;
                                        return $plan?->allow_custom_certificate_logo ?? false;
                                    })
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('name')
                                    ->label(__('creator.profile_field_name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label(__('creator.profile_field_email'))
                                    ->email()
                                    ->disabled()
                                    ->helperText(__('creator.profile_email_helper')),

                                Forms\Components\TextInput::make('phone')
                                    ->label(__('creator.profile_field_phone'))
                                    ->tel()
                                    ->maxLength(20),

                                Forms\Components\Select::make('country_code')
                                    ->label(__('creator.profile_field_country'))
                                    ->options(\App\Enums\CountryCodes::options())
                                    ->searchable()
                                    ->nullable(),

                                Forms\Components\Select::make('timezone')
                                    ->label(__('creator.profile_field_timezone'))
                                    ->options(collect(timezone_identifiers_list())->mapWithKeys(fn($tz) => [$tz => $tz])->toArray())
                                    ->searchable()
                                    ->nullable(),

                                Forms\Components\Textarea::make('bio')
                                    ->label(__('creator.profile_field_bio'))
                                    ->maxLength(1000)
                                    ->rows(4)
                                    ->helperText(__('creator.profile_bio_helper'))
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make(__('creator.profile_tab_payout'))
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Select::make('payout_gateway')
                                    ->label(__('creator.payout_method'))
                                    ->options([
                                        'bank'   => __('creator.payout_bank_transfer'),
                                        'upi'    => __('creator.payout_upi'),
                                        'paypal' => __('creator.payout_paypal'),
                                    ])
                                    ->live()
                                    ->nullable(),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('payout_details.account_name')
                                            ->label(__('creator.payout_account_name'))
                                            ->visible(fn(Forms\Get $get) => $get('payout_gateway') === 'bank'),

                                        Forms\Components\TextInput::make('payout_details.account_number')
                                            ->label(__('creator.payout_account_number'))
                                            ->visible(fn(Forms\Get $get) => $get('payout_gateway') === 'bank'),

                                        Forms\Components\TextInput::make('payout_details.ifsc')
                                            ->label(__('creator.payout_ifsc'))
                                            ->visible(fn(Forms\Get $get) => $get('payout_gateway') === 'bank'),

                                        Forms\Components\TextInput::make('payout_details.upi_id')
                                            ->label(__('creator.payout_upi_id'))
                                            ->helperText(__('creator.payout_upi_helper'))
                                            ->visible(fn(Forms\Get $get) => $get('payout_gateway') === 'upi'),

                                        Forms\Components\TextInput::make('payout_details.paypal_email')
                                            ->label(__('creator.payout_paypal_email'))
                                            ->email()
                                            ->visible(fn(Forms\Get $get) => $get('payout_gateway') === 'paypal'),
                                    ])
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make(__('creator.profile_tab_password'))
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Forms\Components\TextInput::make('current_password')
                                    ->label(__('creator.profile_current_password'))
                                    ->password()
                                    ->revealable()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('new_password')
                                    ->label(__('creator.profile_new_password'))
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->confirmed(),

                                Forms\Components\TextInput::make('new_password_confirmation')
                                    ->label(__('creator.profile_confirm_password'))
                                    ->password()
                                    ->revealable(),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // Handle password change if requested
        if (!empty($data['new_password'])) {
            if (empty($data['current_password']) || !Hash::check($data['current_password'], $user->password)) {
                Notification::make()->title(__('creator.profile_wrong_password'))->danger()->send();
                $this->addError('data.current_password', __('creator.profile_wrong_password'));
                return;
            }
        }

        // Build update payload — include avatar only if it changed (avoid unnecessary write)
        $payload = [
            'name'           => $data['name'],
            'phone'          => $data['phone'] ?? null,
            'country_code'   => $data['country_code'] ?? null,
            'timezone'       => $data['timezone'] ?? null,
            'bio'            => $data['bio'] ?? null,
            'payout_gateway' => $data['payout_gateway'] ?? null,
            'payout_details' => $data['payout_details'] ?? null,
        ];

        // Avatar: FileUpload returns array; extract path
        if (!empty($data['avatar'])) {
            $avatarPath = is_array($data['avatar']) ? reset($data['avatar']) : $data['avatar'];
            if ($avatarPath && $avatarPath !== $user->avatar) {
                $payload['avatar'] = $avatarPath;
            }
        }

        // Certificate logo: only save if plan allows it
        $plan = $user->activeSubscription()->with('plan')->first()?->plan;
        if ($plan?->allow_custom_certificate_logo) {
            $certLogo = $data['certificate_logo'] ?? null;
            if (is_array($certLogo)) {
                $certLogo = !empty($certLogo) ? (string) reset($certLogo) : null;
            }
            $payload['certificate_logo'] = $certLogo ?: null;
        }

        $user->update($payload);

        // Update password if provided
        if (!empty($data['new_password'])) {
            $user->update(['password' => Hash::make($data['new_password'])]);

            // Clear password fields after change
            $this->data['current_password']           = '';
            $this->data['new_password']               = '';
            $this->data['new_password_confirmation']  = '';
        }

        Notification::make()->title(__('creator.profile_updated'))->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(__('creator.profile_save_btn'))
                ->submit('save'),
        ];
    }

}
