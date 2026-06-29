<?php

namespace App\Filament\Admin\Pages;

use App\Settings\PlatformSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Settings extends Page
{
    use InteractsWithFormActions;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationLabel(): string { return __('admin.nav_settings'); }
    public static function getNavigationGroup(): ?string { return __('admin.nav_group_configuration'); }
    public function getTitle(): string { return __('admin.nav_settings'); }
    protected static string  $view            = 'filament.admin.pages.settings';

    public ?array $data = [];

    /** Masked previews of secret fields — safe to send to the browser. */
    public array $secretHints = [];

    // Fields whose values must never be sent to the browser HTML
    protected const SECRET_FIELDS = [
        'openai_api_key',
        'google_client_secret',
        'razorpay_secret',
        'stripe_secret',
        'stripe_webhook_secret',
        'paypal_client_secret',
        'aws_secret',
        'mail_password',
    ];

    public function mount(): void
    {
        // Always load fresh values from DB — forget cache and force a new instance
        \Illuminate\Support\Facades\Cache::flush();
        app()->forgetInstance(PlatformSettings::class);

        $s          = app(PlatformSettings::class);
        $reflection = new \ReflectionClass(PlatformSettings::class);
        $this->data = [];

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            if ($prop->getDeclaringClass()->getName() !== PlatformSettings::class) {
                continue;
            }

            $key  = $prop->getName();
            $type = $prop->getType()?->getName();

            if (in_array($key, self::SECRET_FIELDS, true)) {
                $this->data[$key] = null;
                $this->secretHints[$key] = $this->maskSecret((string) ($s->{$key} ?? ''));
                continue;
            }

            $value = $s->{$key};

            // FileUpload fields: Filament 3 stores state internally as an array of paths.
            // Pass an array with the existing path, or null when empty.
            if (in_array($key, ['app_logo', 'app_favicon', 'og_image', 'certificate_logo'], true)) {
                $this->data[$key] = ($value !== null && $value !== '') ? [$value] : null;
                continue;
            }

            // JSON Repeater fields: stored as JSON string in DB.
            // Filament 3 Repeater requires each item to have a UUID string key —
            // including nested Repeaters (footer_columns → items).
            if (in_array($key, ['hiw_steps', 'footer_columns'], true)) {
                $items = is_string($value) ? (json_decode($value, true) ?: []) : ($value ?? []);
                if ($key === 'footer_columns') {
                    // UUID-key the inner items array of each column too
                    $items = array_map(function (array $col) {
                        $col['items'] = collect($col['items'] ?? [])
                            ->mapWithKeys(fn ($link) => [(string) \Illuminate\Support\Str::uuid() => $link])
                            ->all();
                        return $col;
                    }, $items);
                }
                $this->data[$key] = collect($items)
                    ->mapWithKeys(fn ($item) => [(string) \Illuminate\Support\Str::uuid() => $item])
                    ->all();
                continue;
            }

            // Nullable strings: keep null rather than coercing to ''
            $isNullable = $prop->getType()?->allowsNull() ?? false;

            $this->data[$key] = match ($type) {
                'string' => $isNullable ? ($value ?: null) : (string) ($value ?? ''),
                'int'    => (int)     ($value ?? 0),
                'float'  => (float)   ($value ?? 0.0),
                'bool'   => (bool)    ($value ?? false),
                default  => $value,
            };
        }
    }

    /** Returns a masked tail preview: e.g. *****ab12, or translated "Not set". */
    protected function maskSecret(string $value): string
    {
        if ($value === '') return __('admin.settings_secret_not_set');
        $tail = substr($value, -4);
        return str_repeat('*', min(5, max(0, strlen($value) - 4))) . $tail;
    }

    /** Hint string shown under each secret field. */
    protected function secretHint(string $field): string
    {
        $preview = $this->secretHints[$field] ?? __('admin.settings_secret_not_set');
        if ($preview === __('admin.settings_secret_not_set')) {
            return __('admin.settings_secret_blank_hint');
        }
        return __('admin.settings_secret_keep_hint') . $preview;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->persistTabInQueryString('tab')
                    ->tabs([

                        // ── General ───────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_general'))
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\TextInput::make('app_name')
                                    ->label(__('admin.settings_field_app_name'))
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('app_logo')
                                    ->label(__('admin.settings_field_app_logo'))
                                    ->image()
                                    ->maxSize(2048)
                                    ->directory('logos')
                                    ->helperText(__('admin.settings_helper_app_logo')),
                                Forms\Components\FileUpload::make('app_favicon')
                                    ->label(__('admin.settings_field_favicon'))
                                    ->image()
                                    ->maxSize(512)
                                    ->directory('logos')
                                    ->helperText(__('admin.settings_helper_favicon')),
                                Forms\Components\FileUpload::make('certificate_logo')
                                    ->label(__('admin.settings_field_cert_logo'))
                                    ->image()
                                    ->maxSize(1024)
                                    ->directory('logos')
                                    ->helperText(__('admin.settings_helper_cert_logo'))
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('default_currency')
                                    ->label(__('admin.settings_field_currency_code'))
                                    ->options(\App\Support\CurrencyList::selectOptions())
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $symbol = \App\Support\CurrencyList::symbol($state);
                                        if ($symbol) {
                                            $set('currency_symbol', $symbol);
                                        }
                                    })
                                    ->helperText(__('admin.settings_helper_currency_code')),
                                Forms\Components\TextInput::make('currency_symbol')
                                    ->label(__('admin.settings_field_currency_symbol'))
                                    ->helperText(__('admin.settings_helper_currency_symbol'))
                                    ->maxLength(5),
                            ])->columns(2),

                        // ── Theme ─────────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_theme'))
                            ->icon('heroicon-o-paint-brush')
                            ->schema([

                                // ── Active preset tracker (hidden, UI-only) ───
                                Forms\Components\Hidden::make('active_preset'),

                                // ── Preset themes ─────────────────────────────
                                Forms\Components\Section::make(__('admin.settings_section_preset_themes'))
                                    ->description(__('admin.settings_desc_preset'))
                                    ->schema([
                                        Forms\Components\View::make('filament.admin.components.preset-theme-picker')
                                            ->columnSpanFull(),
                                    ])->columnSpanFull(),

                                // ── Colors ────────────────────────────────────
                                Forms\Components\Section::make(__('admin.settings_section_colors'))
                                    ->schema([
                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label(__('admin.settings_field_primary_color'))
                                            ->helperText(__('admin.settings_helper_primary_color')),
                                        Forms\Components\ColorPicker::make('accent_color')
                                            ->label(__('admin.settings_field_accent_color'))
                                            ->helperText(__('admin.settings_helper_accent_color')),
                                    ])->columns(2)->columnSpanFull(),

                                // ── Typography ────────────────────────────────
                                Forms\Components\Section::make(__('admin.settings_section_typography'))
                                    ->description(__('admin.settings_desc_fonts'))
                                    ->schema([
                                        Forms\Components\Select::make('font_display')
                                            ->label(__('admin.settings_field_font_display'))
                                            ->helperText(__('admin.settings_helper_font_display'))
                                            ->options([
                                                'Plus Jakarta Sans' => 'Plus Jakarta Sans (default)',
                                                'Sora'              => 'Sora — Modern geometric',
                                                'Space Grotesk'     => 'Space Grotesk — Techy',
                                                'Poppins'           => 'Poppins — Friendly rounded',
                                                'Nunito'            => 'Nunito — Soft & playful',
                                                'Merriweather'      => 'Merriweather — Classic serif',
                                                'Playfair Display'  => 'Playfair Display — Elegant serif',
                                                'Outfit'            => 'Outfit — Clean minimal',
                                            ])
                                            ->native(false),
                                        Forms\Components\Select::make('font_primary')
                                            ->label(__('admin.settings_field_font_body'))
                                            ->helperText(__('admin.settings_helper_font_body'))
                                            ->options([
                                                'Inter'          => 'Inter (default)',
                                                'DM Sans'        => 'DM Sans — Clean & modern',
                                                'IBM Plex Sans'  => 'IBM Plex Sans — Technical',
                                                'Source Sans 3'  => 'Source Sans 3 — Readable',
                                                'Nunito'         => 'Nunito — Soft & friendly',
                                                'Poppins'        => 'Poppins — Rounded',
                                                'Lato'           => 'Lato — Professional',
                                                'Rubik'          => 'Rubik — Contemporary',
                                            ])
                                            ->native(false),
                                        Forms\Components\Select::make('font_size_base')
                                            ->label(__('admin.settings_field_font_size'))
                                            ->helperText(__('admin.settings_helper_font_size'))
                                            ->options([
                                                '14px' => '14px — Compact',
                                                '15px' => '15px — Slightly compact',
                                                '16px' => '16px — Default (recommended)',
                                                '17px' => '17px — Slightly larger',
                                                '18px' => '18px — Large / accessibility',
                                            ])
                                            ->native(false),
                                    ])->columns(3)->columnSpanFull(),

                                Forms\Components\Placeholder::make('theme_note')
                                    ->label('')
                                    ->content(__('admin.settings_theme_note'))
                                    ->columnSpanFull(),
                            ])->columns(1),

                        // ── Homepage ──────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_homepage'))
                            ->icon('heroicon-o-home')
                            ->schema([

                                Forms\Components\Section::make(__('admin.settings_section_hero'))
                                    ->description(__('admin.settings_desc_hero'))
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_title')
                                            ->label(__('admin.settings_field_hero_title'))
                                            ->maxLength(120)
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('hero_subtitle')
                                            ->label(__('admin.settings_field_hero_subtitle'))
                                            ->rows(2)
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('hero_cta_text')
                                            ->label(__('admin.settings_field_hero_cta_text'))
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('hero_cta_url')
                                            ->label(__('admin.settings_field_hero_cta_url'))
                                            ->placeholder(__('admin.settings_ph_quizzes_url'))
                                            ->url()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('hero_secondary_text')
                                            ->label(__('admin.settings_field_hero_secondary'))
                                            ->maxLength(50)
                                            ->helperText(__('admin.settings_helper_hero_secondary')),
                                    ])->columns(2),

                                Forms\Components\Section::make(__('admin.settings_section_hiw'))
                                    ->description(__('admin.settings_desc_hiw'))
                                    ->schema([
                                        Forms\Components\TextInput::make('hiw_title')
                                            ->label(__('admin.settings_field_section_title'))
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('hiw_subtitle')
                                            ->label(__('admin.settings_field_section_subtitle'))
                                            ->maxLength(200),
                                        Forms\Components\Repeater::make('hiw_steps')
                                            ->label(__('admin.settings_field_steps'))
                                            ->schema([
                                                Forms\Components\TextInput::make('num_label')
                                                    ->label(__('admin.settings_field_step_label'))
                                                    ->maxLength(40)
                                                    ->required(),
                                                Forms\Components\TextInput::make('title')
                                                    ->label(__('admin.settings_field_step_title'))
                                                    ->maxLength(60)
                                                    ->required(),
                                                Forms\Components\Textarea::make('desc')
                                                    ->label(__('admin.settings_field_step_desc'))
                                                    ->rows(2)
                                                    ->maxLength(200)
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->minItems(1)
                                            ->maxItems(6)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Forms\Components\Section::make(__('admin.settings_section_featured'))
                                    ->schema([
                                        Forms\Components\TextInput::make('featured_title')
                                            ->label(__('admin.settings_field_section_title'))
                                            ->maxLength(80)
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make(__('admin.settings_section_creator_cta'))
                                    ->description(__('admin.settings_desc_creator_cta'))
                                    ->schema([
                                        Forms\Components\TextInput::make('lecturer_cta_title')
                                            ->label(__('admin.settings_field_title'))
                                            ->maxLength(80),
                                        Forms\Components\TextInput::make('lecturer_cta_sub')
                                            ->label(__('admin.settings_field_subtitle'))
                                            ->maxLength(120),
                                        Forms\Components\TextInput::make('lecturer_cta_btn')
                                            ->label(__('admin.settings_field_btn_text'))
                                            ->maxLength(40),
                                        Forms\Components\TextInput::make('lecturer_cta_url')
                                            ->label(__('admin.settings_field_btn_url'))
                                            ->placeholder(__('admin.settings_ph_creators_url'))
                                            ->url()
                                            ->maxLength(255),
                                    ])->columns(2),

                                Forms\Components\Section::make(__('admin.settings_section_stats'))
                                    ->description(__('admin.settings_desc_stats'))
                                    ->schema([
                                        Forms\Components\TextInput::make('homepage_stat_1_value')
                                            ->label(__('admin.settings_field_stat_1_value'))
                                            ->placeholder(__('admin.settings_ph_stat_1_value'))
                                            ->maxLength(30),
                                        Forms\Components\TextInput::make('homepage_stat_1_label')
                                            ->label(__('admin.settings_field_stat_1_label'))
                                            ->placeholder(__('admin.settings_ph_stat_1_label'))
                                            ->maxLength(60),
                                        Forms\Components\TextInput::make('homepage_stat_2_value')
                                            ->label(__('admin.settings_field_stat_2_value'))
                                            ->placeholder(__('admin.settings_ph_stat_2_value'))
                                            ->maxLength(30),
                                        Forms\Components\TextInput::make('homepage_stat_2_label')
                                            ->label(__('admin.settings_field_stat_2_label'))
                                            ->placeholder(__('admin.settings_ph_stat_2_label'))
                                            ->maxLength(60),
                                        Forms\Components\TextInput::make('homepage_stat_3_value')
                                            ->label(__('admin.settings_field_stat_3_value'))
                                            ->placeholder(__('admin.settings_ph_stat_3_value'))
                                            ->maxLength(30),
                                        Forms\Components\TextInput::make('homepage_stat_3_label')
                                            ->label(__('admin.settings_field_stat_3_label'))
                                            ->placeholder(__('admin.settings_ph_stat_3_label'))
                                            ->maxLength(60),
                                    ])->columns(2),
                            ]),

                        // ── Footer ────────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_footer'))
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->schema([

                                Forms\Components\Section::make(__('admin.settings_section_footer_brand'))
                                    ->schema([
                                        Forms\Components\Textarea::make('footer_tagline')
                                            ->label(__('admin.settings_field_tagline'))
                                            ->rows(2)
                                            ->maxLength(300)
                                            ->helperText(__('admin.settings_helper_tagline'))
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('footer_show_newsletter')
                                            ->label(__('admin.settings_field_show_newsletter'))
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make(__('admin.settings_section_footer_columns'))
                                    ->description(__('admin.settings_desc_footer_columns'))
                                    ->schema([
                                        Forms\Components\Repeater::make('footer_columns')
                                            ->label(__('admin.settings_field_columns'))
                                            ->schema([
                                                Forms\Components\TextInput::make('title')
                                                    ->label(__('admin.settings_field_col_heading'))
                                                    ->required()
                                                    ->maxLength(50),
                                                Forms\Components\Repeater::make('items')
                                                    ->label(__('admin.settings_field_links'))
                                                    ->schema([
                                                        Forms\Components\TextInput::make('label')
                                                            ->label(__('admin.settings_field_link_text'))
                                                            ->required()
                                                            ->maxLength(60),
                                                        Forms\Components\TextInput::make('url')
                                                            ->label(__('admin.settings_field_url'))
                                                            ->required()
                                                            ->maxLength(255),
                                                    ])
                                                    ->columns(2)
                                                    ->minItems(1)
                                                    ->maxItems(10)
                                                    ->reorderable()
                                                    ->columnSpanFull(),
                                            ])
                                            ->maxItems(4)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Column')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Registration & Auth ───────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_registration'))
                            ->icon('heroicon-o-user-plus')
                            ->schema([
                                Forms\Components\Toggle::make('allow_registration')
                                    ->label(__('admin.settings_field_allow_reg'))
                                    ->helperText(__('admin.settings_helper_allow_reg')),
                                Forms\Components\Toggle::make('lecturer_registration_open')
                                    ->label(__('admin.settings_field_creator_reg'))
                                    ->helperText(__('admin.settings_helper_creator_reg')),
                                Forms\Components\Toggle::make('require_email_verification')
                                    ->label(__('admin.settings_field_email_verify'))
                                    ->helperText(__('admin.settings_helper_email_verify')),
                                Forms\Components\Toggle::make('allow_social_login')
                                    ->label(__('admin.settings_field_google_login'))
                                    ->helperText(__('admin.settings_helper_google_login'))
                                    ->live(),
                                Forms\Components\Section::make(__('admin.settings_section_google_oauth'))
                                    ->schema([
                                        Forms\Components\TextInput::make('google_client_id')
                                            ->label(__('admin.settings_field_client_id')),
                                        Forms\Components\TextInput::make('google_client_secret')
                                            ->label(__('admin.settings_field_client_secret'))
                                            ->password()->revealable()->dehydrated(fn ($state) => filled($state))
                                            ->hint(fn() => $this->secretHint('google_client_secret')),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Forms\Get $get) => $get('allow_social_login')),
                            ])->columns(2),

                        // ── AI / OpenAI ───────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_ai'))
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Forms\Components\Section::make(__('admin.settings_section_openai'))
                                    ->schema([
                                        Forms\Components\TextInput::make('openai_api_key')
                                            ->label(__('admin.settings_field_api_key'))
                                            ->password()
                                            ->revealable()
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->hint(fn() => $this->secretHint('openai_api_key'))
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('openai_organization')
                                            ->label(__('admin.settings_field_org_id'))
                                            ->helperText(__('admin.settings_helper_org_id')),
                                        Forms\Components\Select::make('openai_model')
                                            ->label(__('admin.settings_field_model'))
                                            ->options([
                                                'gpt-4o'      => 'GPT-4o (recommended)',
                                                'gpt-4o-mini' => 'GPT-4o Mini (cheaper)',
                                                'gpt-4-turbo' => 'GPT-4 Turbo',
                                                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (legacy)',
                                            ])
                                            ->required(),
                                    ])->columns(2),

                            ]),

                        // ── Storage ───────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_storage'))
                            ->icon('heroicon-o-cloud-arrow-up')
                            ->schema([
                                Forms\Components\Select::make('filesystem_disk')
                                    ->label(__('admin.settings_field_storage_driver'))
                                    ->options(['local' => 'Local (server disk)', 's3' => 'Amazon S3'])
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\Section::make(__('admin.settings_section_s3'))
                                    ->schema([
                                        Forms\Components\TextInput::make('aws_key')
                                            ->label(__('admin.settings_field_aws_key')),
                                        Forms\Components\TextInput::make('aws_secret')
                                            ->label(__('admin.settings_field_aws_secret'))
                                            ->password()->revealable()->dehydrated(fn ($state) => filled($state))
                                            ->hint(fn() => $this->secretHint('aws_secret')),
                                        Forms\Components\TextInput::make('aws_region')
                                            ->label(__('admin.settings_field_region'))
                                            ->placeholder(__('admin.settings_ph_aws_region')),
                                        Forms\Components\TextInput::make('aws_bucket')
                                            ->label(__('admin.settings_field_bucket')),
                                        Forms\Components\TextInput::make('aws_url')
                                            ->label(__('admin.settings_field_cdn_url'))
                                            ->helperText(__('admin.settings_helper_cdn_url'))
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Forms\Get $get) => $get('filesystem_disk') === 's3'),
                            ]),

                        // ── Email ─────────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_email'))
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Forms\Components\TextInput::make('mail_host')->label(__('admin.settings_field_smtp_host')),
                                Forms\Components\TextInput::make('mail_port')->label(__('admin.settings_field_smtp_port'))->numeric(),
                                Forms\Components\Select::make('mail_encryption')
                                    ->label(__('admin.settings_field_encryption'))
                                    ->options(['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None']),
                                Forms\Components\TextInput::make('mail_username')->label(__('admin.settings_field_username')),
                                Forms\Components\TextInput::make('mail_password')->label(__('admin.settings_field_password'))->password()->revealable()->dehydrated(fn ($state) => filled($state))
                                    ->hint(fn() => $this->secretHint('mail_password')),
                                Forms\Components\TextInput::make('mail_from_address')->label(__('admin.settings_field_from_address'))->email(),
                                Forms\Components\TextInput::make('mail_from_name')->label(__('admin.settings_field_from_name')),
                            ])->columns(2),

                        // ── SEO & Social ──────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_seo'))
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Forms\Components\Section::make(__('admin.settings_section_seo'))
                                    ->schema([
                                        Forms\Components\TextInput::make('seo_title')
                                            ->label(__('admin.settings_field_seo_title'))
                                            ->placeholder(__('admin.settings_ph_seo_title'))
                                            ->maxLength(70)
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('seo_description')
                                            ->label(__('admin.settings_field_meta_desc'))
                                            ->rows(2)
                                            ->maxLength(160)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('seo_keywords')
                                            ->label(__('admin.settings_field_meta_keywords'))
                                            ->helperText(__('admin.settings_helper_meta_keywords'))
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('google_analytics_id')
                                            ->label(__('admin.settings_field_ga_id'))
                                            ->placeholder(__('admin.settings_ph_ga_id')),
                                        Forms\Components\FileUpload::make('og_image')
                                            ->label(__('admin.settings_field_og_image'))
                                            ->image()
                                            ->directory('seo')
                                            ->helperText(__('admin.settings_helper_og_image')),
                                    ])->columns(2),

                                Forms\Components\Section::make(__('admin.settings_section_social'))
                                    ->schema([
                                        Forms\Components\TextInput::make('social_facebook')->label(__('admin.settings_field_social_fb'))->url(),
                                        Forms\Components\TextInput::make('social_twitter')->label(__('admin.settings_field_social_tw'))->url(),
                                        Forms\Components\TextInput::make('social_instagram')->label(__('admin.settings_field_social_ig'))->url(),
                                        Forms\Components\TextInput::make('social_youtube')->label(__('admin.settings_field_social_yt'))->url(),
                                        Forms\Components\TextInput::make('social_linkedin')->label(__('admin.settings_field_social_li'))->url(),
                                    ])->columns(2),
                            ]),

                        // ── Languages ────────────────────────────────────────
                        Forms\Components\Tabs\Tab::make('Languages')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Forms\Components\Section::make('Language Switcher Visibility')
                                    ->description('Control where the language switcher is shown across the platform.')
                                    ->schema([
                                        Forms\Components\Toggle::make('show_switcher_admin')
                                            ->label('Show in Admin panel')
                                            ->helperText('Display the language switcher in the /admin topbar.'),
                                        Forms\Components\Toggle::make('show_switcher_lecturer')
                                            ->label('Show in Lecturer panel')
                                            ->helperText('Display the language switcher in the /lecturer topbar.'),
                                        Forms\Components\Toggle::make('show_switcher_front')
                                            ->label('Show on Customer / Frontend')
                                            ->helperText('Display the language switcher in the customer-facing navigation.'),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),

                                Forms\Components\Section::make('Active Locales')
                                    ->description('Choose which languages are available in the switcher. English is always included.')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('enabled_locales')
                                            ->label('')
                                            ->options(function () {
                                                $configLocales = config('app.available_locales', []);
                                                $extraLocales  = rescue(fn () => app(\App\Settings\PlatformSettings::class)->extra_locales, [], false) ?? [];
                                                $hiddenLocales = rescue(fn () => app(\App\Settings\PlatformSettings::class)->hidden_locales, [], false) ?? [];
                                                $all = array_merge($configLocales, $extraLocales);
                                                foreach ($hiddenLocales as $h) { unset($all[$h]); }
                                                return collect($all)->map(fn ($name) => $name)->toArray();
                                            })
                                            ->columns(4)
                                            ->columnSpanFull()
                                            ->gridDirection('row'),
                                    ]),
                            ]),

                        // ── Maintenance ─────────────────────────────────────
                        Forms\Components\Tabs\Tab::make(__('admin.settings_tab_system'))
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Forms\Components\Section::make(__('admin.settings_section_cron'))
                                    ->description(__('admin.settings_desc_cron'))
                                    ->schema([
                                        Forms\Components\Placeholder::make('cron_command')
                                            ->label(__('admin.settings_field_cron_label'))
                                            ->content(new \Illuminate\Support\HtmlString(
                                                '<code style="display:block;background:#1e1b4b;color:#a5b4fc;padding:12px 16px;border-radius:8px;font-size:13px;font-family:monospace;user-select:all;">* * * * * cd ' . base_path() . ' &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>'
                                            ))
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('cron_jobs_list')
                                            ->label(__('admin.settings_field_cron_jobs'))
                                            ->content(function (): \Illuminate\Support\HtmlString {
                                                $items = [
                                                    'cron_job_renewal'       => __('admin.settings_cron_job_renewal'),
                                                    'cron_job_expiry'        => __('admin.settings_cron_job_expiry'),
                                                    'cron_job_grace'         => __('admin.settings_cron_job_grace'),
                                                    'cron_job_digest'        => __('admin.settings_cron_job_digest'),
                                                    'cron_job_stats'         => __('admin.settings_cron_job_stats'),
                                                    'cron_job_cleanup'       => __('admin.settings_cron_job_cleanup'),
                                                ];
                                                $li = '';
                                                foreach ($items as $item) {
                                                    $li .= '<li>' . $item . '</li>';
                                                }
                                                return new \Illuminate\Support\HtmlString(
                                                    '<ul style="margin:0;padding-left:20px;font-size:13px;line-height:2;color:#374151;">' . $li . '</ul>'
                                                    . '<p style="margin-top:10px;font-size:12px;color:#6B7280;">' . __('admin.settings_cron_note') . '</p>'
                                                );
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible(),

                                Forms\Components\Section::make(__('admin.settings_section_maintenance'))
                                    ->schema([
                                        Forms\Components\Toggle::make('maintenance_mode')
                                            ->label(__('admin.settings_field_maintenance'))
                                            ->helperText(__('admin.settings_helper_maintenance'))
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('maintenance_message')
                                            ->label(__('admin.settings_field_maintenance_msg'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                            ]),

                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Use a fresh instance so we write to DB, not a stale cached object
        app()->forgetInstance(PlatformSettings::class);
        $settings = app(PlatformSettings::class);

        $reflection = new \ReflectionClass(PlatformSettings::class);

        foreach ($data as $key => $value) {
            if (! property_exists($settings, $key)) {
                continue;
            }

            // Secret fields with ->dehydrated(fn=>filled) are excluded from $data
            // when left blank — so they never reach here. But belt-and-suspenders:
            // if one slips through as empty, preserve the existing DB value.
            if (in_array($key, self::SECRET_FIELDS, true) && ! filled($value)) {
                continue;
            }

            $prop       = $reflection->getProperty($key);
            $type       = $prop->getType()?->getName();
            $isNullable = $prop->getType()?->allowsNull() ?? false;

            // FileUpload returns an array or null — extract the single path string
            if (in_array($key, ['app_logo', 'app_favicon', 'og_image', 'certificate_logo'], true)) {
                if (is_array($value)) {
                    $value = !empty($value) ? (string) reset($value) : null;
                }
                $settings->{$key} = $value ?: null;
                continue;
            }

            // Repeater fields arrive as UUID-keyed arrays — strip UUID keys, store as plain PHP array
            // (PlatformSettings::$hiw_steps and $footer_columns are typed `array`, NOT string)
            if ($key === 'hiw_steps') {
                $settings->{$key} = array_values($value ?? []);
                continue;
            }
            if ($key === 'footer_columns') {
                // Also strip UUID keys from nested items arrays
                $cols = array_values($value ?? []);
                foreach ($cols as &$col) {
                    $col['items'] = array_values($col['items'] ?? []);
                }
                unset($col);
                $settings->{$key} = $cols;
                continue;
            }
            // Skip internal UI-only fields that don't map to PlatformSettings properties
            if ($key === 'active_preset') {
                continue;
            }

            $settings->{$key} = match ($type) {
                'string'  => $isNullable ? ($value ?: null) : (string) ($value ?? ''),
                'int'     => (int)     ($value ?? 0),
                'float'   => (float)   ($value ?? 0.0),
                'bool'    => (bool)    ($value ?? false),
                default   => $value,
            };
        }

        $settings->save();

        // Clear the Spatie Settings cache so every subsequent request sees the new values
        \Illuminate\Support\Facades\Cache::forget('settings.' . \App\Settings\PlatformSettings::cacheKey());
        \Illuminate\Support\Facades\Cache::flush();

        // Apply OpenAI key to runtime config so AI works without restart
        if (! empty($settings->openai_api_key)) {
            \Illuminate\Support\Facades\Config::set('openai.api_key', $settings->openai_api_key);
            if (! empty($settings->openai_organization)) {
                \Illuminate\Support\Facades\Config::set('openai.organization', $settings->openai_organization);
            }
        }

        Notification::make()->title(__('admin.settings_saved'))->success()->send();
    }

    public function sendTestEmail(): void
    {
        $raw  = $this->data;
        $s    = app(PlatformSettings::class);
        $host = $raw['mail_host'] ?? $s->mail_host;
        $port = $raw['mail_port'] ?? $s->mail_port;
        $enc  = $raw['mail_encryption'] ?? $s->mail_encryption;
        $user = $raw['mail_username'] ?? $s->mail_username;
        // mail_password is a secret field — always read from DB, not form state
        $pass = $s->mail_password;
        $from = $raw['mail_from_address'] ?? $s->mail_from_address;
        $name = $raw['mail_from_name'] ?? $s->mail_from_name ?: config('app.name');

        if (empty($host) || empty($from)) {
            Notification::make()->title(__('admin.settings_smtp_missing'))->danger()->send();
            return;
        }

        $originalMailConfig = config('mail');

        try {
            Config::set([
                'mail.default'                 => 'smtp',
                'mail.mailers.smtp.host'       => $host,
                'mail.mailers.smtp.port'       => (int) $port,
                'mail.mailers.smtp.encryption' => $enc ?: null,
                'mail.mailers.smtp.username'   => $user,
                'mail.mailers.smtp.password'   => $pass,
                'mail.from.address'            => $from,
                'mail.from.name'               => $name,
            ]);

            Mail::purge('smtp');

            Mail::mailer('smtp')->raw(
                'This is a test email from ' . config('app.name') . '. Your SMTP settings are working correctly.',
                fn ($m) => $m->to(auth()->user()->email)->from($from, $name)->subject('SMTP Test')
            );

            Notification::make()->title(__('admin.settings_smtp_test_sent', ['email' => auth()->user()->email]))->success()->send();
        } catch (TransportExceptionInterface $e) {
            Notification::make()->title(__('admin.settings_smtp_error') . ': ' . $e->getMessage())->danger()->send();
        } catch (\Exception $e) {
            Notification::make()->title(__('admin.settings_mail_error') . ': ' . $e->getMessage())->danger()->send();
        } finally {
            Config::set('mail', $originalMailConfig);
            Mail::purge('smtp');
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(__('admin.settings_btn_save'))
                ->submit('save'),
        ];
    }
}
