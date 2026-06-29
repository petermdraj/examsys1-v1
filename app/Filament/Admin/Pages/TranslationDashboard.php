<?php

namespace App\Filament\Admin\Pages;

use App\Settings\PlatformSettings;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TranslationDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-language';
    protected static ?string $navigationLabel = 'Translations';
    protected static ?int    $navigationSort  = 99;
    protected static string  $view            = 'filament.admin.pages.translation-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.manage_settings') ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_group_configuration');
    }

    public array  $stats          = [];
    public string $selectedLocale = '';

    // Add language modal
    public bool   $showAddModal   = false;
    public string $newLocaleCode  = '';
    public string $newLocaleName  = '';
    public string $newLocaleFlag  = '';
    public bool   $newLocaleRtl   = false;
    public string $localePickerSearch = '';

    /** Master list of known locales: code => [name, flag, rtl?] */
    public static function knownLocales(): array
    {
        return [
            'af'    => ['name' => 'Afrikaans',              'flag' => '🇿🇦', 'rtl' => false],
            'sq'    => ['name' => 'Shqip',                  'flag' => '🇦🇱', 'rtl' => false],
            'am'    => ['name' => 'አማርኛ',                   'flag' => '🇪🇹', 'rtl' => false],
            'ar'    => ['name' => 'العربية',                 'flag' => '🇸🇦', 'rtl' => true ],
            'hy'    => ['name' => 'Հայերեն',                'flag' => '🇦🇲', 'rtl' => false],
            'az'    => ['name' => 'Azərbaycan',             'flag' => '🇦🇿', 'rtl' => false],
            'eu'    => ['name' => 'Euskara',                'flag' => '🇪🇸', 'rtl' => false],
            'be'    => ['name' => 'Беларуская',             'flag' => '🇧🇾', 'rtl' => false],
            'bn'    => ['name' => 'বাংলা',                   'flag' => '🇧🇩', 'rtl' => false],
            'bs'    => ['name' => 'Bosanski',               'flag' => '🇧🇦', 'rtl' => false],
            'bg'    => ['name' => 'Български',              'flag' => '🇧🇬', 'rtl' => false],
            'ca'    => ['name' => 'Català',                 'flag' => '🇪🇸', 'rtl' => false],
            'zh'    => ['name' => '中文 (简体)',              'flag' => '🇨🇳', 'rtl' => false],
            'zh_TW' => ['name' => '中文 (繁體)',              'flag' => '🇹🇼', 'rtl' => false],
            'hr'    => ['name' => 'Hrvatski',               'flag' => '🇭🇷', 'rtl' => false],
            'cs'    => ['name' => 'Čeština',                'flag' => '🇨🇿', 'rtl' => false],
            'da'    => ['name' => 'Dansk',                  'flag' => '🇩🇰', 'rtl' => false],
            'nl'    => ['name' => 'Nederlands',             'flag' => '🇳🇱', 'rtl' => false],
            'en'    => ['name' => 'English',                'flag' => '🇬🇧', 'rtl' => false],
            'et'    => ['name' => 'Eesti',                  'flag' => '🇪🇪', 'rtl' => false],
            'fi'    => ['name' => 'Suomi',                  'flag' => '🇫🇮', 'rtl' => false],
            'fr'    => ['name' => 'Français',               'flag' => '🇫🇷', 'rtl' => false],
            'gl'    => ['name' => 'Galego',                 'flag' => '🇪🇸', 'rtl' => false],
            'ka'    => ['name' => 'ქართული',                'flag' => '🇬🇪', 'rtl' => false],
            'de'    => ['name' => 'Deutsch',                'flag' => '🇩🇪', 'rtl' => false],
            'el'    => ['name' => 'Ελληνικά',               'flag' => '🇬🇷', 'rtl' => false],
            'gu'    => ['name' => 'ગુજરાતી',                 'flag' => '🇮🇳', 'rtl' => false],
            'ht'    => ['name' => 'Kreyòl ayisyen',         'flag' => '🇭🇹', 'rtl' => false],
            'ha'    => ['name' => 'Hausa',                  'flag' => '🇳🇬', 'rtl' => false],
            'he'    => ['name' => 'עברית',                  'flag' => '🇮🇱', 'rtl' => true ],
            'hi'    => ['name' => 'हिन्दी',                  'flag' => '🇮🇳', 'rtl' => false],
            'hu'    => ['name' => 'Magyar',                 'flag' => '🇭🇺', 'rtl' => false],
            'is'    => ['name' => 'Íslenska',               'flag' => '🇮🇸', 'rtl' => false],
            'id'    => ['name' => 'Bahasa Indonesia',       'flag' => '🇮🇩', 'rtl' => false],
            'ga'    => ['name' => 'Gaeilge',                'flag' => '🇮🇪', 'rtl' => false],
            'it'    => ['name' => 'Italiano',               'flag' => '🇮🇹', 'rtl' => false],
            'ja'    => ['name' => '日本語',                  'flag' => '🇯🇵', 'rtl' => false],
            'kn'    => ['name' => 'ಕನ್ನಡ',                  'flag' => '🇮🇳', 'rtl' => false],
            'kk'    => ['name' => 'Қазақша',               'flag' => '🇰🇿', 'rtl' => false],
            'km'    => ['name' => 'ខ្មែរ',                   'flag' => '🇰🇭', 'rtl' => false],
            'ko'    => ['name' => '한국어',                  'flag' => '🇰🇷', 'rtl' => false],
            'ku'    => ['name' => 'Kurdî',                  'flag' => '🇮🇶', 'rtl' => true ],
            'ky'    => ['name' => 'Кыргызча',              'flag' => '🇰🇬', 'rtl' => false],
            'lo'    => ['name' => 'ລາວ',                    'flag' => '🇱🇦', 'rtl' => false],
            'lv'    => ['name' => 'Latviešu',               'flag' => '🇱🇻', 'rtl' => false],
            'lt'    => ['name' => 'Lietuvių',               'flag' => '🇱🇹', 'rtl' => false],
            'lb'    => ['name' => 'Lëtzebuergesch',         'flag' => '🇱🇺', 'rtl' => false],
            'mk'    => ['name' => 'Македонски',             'flag' => '🇲🇰', 'rtl' => false],
            'mg'    => ['name' => 'Malagasy',               'flag' => '🇲🇬', 'rtl' => false],
            'ms'    => ['name' => 'Bahasa Melayu',          'flag' => '🇲🇾', 'rtl' => false],
            'ml'    => ['name' => 'മലയാളം',                 'flag' => '🇮🇳', 'rtl' => false],
            'mt'    => ['name' => 'Malti',                  'flag' => '🇲🇹', 'rtl' => false],
            'mi'    => ['name' => 'Te Reo Māori',           'flag' => '🇳🇿', 'rtl' => false],
            'mr'    => ['name' => 'मराठी',                   'flag' => '🇮🇳', 'rtl' => false],
            'mn'    => ['name' => 'Монгол',                 'flag' => '🇲🇳', 'rtl' => false],
            'my'    => ['name' => 'မြန်မာ',                  'flag' => '🇲🇲', 'rtl' => false],
            'ne'    => ['name' => 'नेपाली',                  'flag' => '🇳🇵', 'rtl' => false],
            'no'    => ['name' => 'Norsk',                  'flag' => '🇳🇴', 'rtl' => false],
            'or'    => ['name' => 'ଓଡ଼ିଆ',                   'flag' => '🇮🇳', 'rtl' => false],
            'ps'    => ['name' => 'پښتو',                   'flag' => '🇦🇫', 'rtl' => true ],
            'fa'    => ['name' => 'فارسی',                  'flag' => '🇮🇷', 'rtl' => true ],
            'pl'    => ['name' => 'Polski',                 'flag' => '🇵🇱', 'rtl' => false],
            'pt'    => ['name' => 'Português',              'flag' => '🇵🇹', 'rtl' => false],
            'pt_BR' => ['name' => 'Português (Brasil)',     'flag' => '🇧🇷', 'rtl' => false],
            'pa'    => ['name' => 'ਪੰਜਾਬੀ',                  'flag' => '🇮🇳', 'rtl' => false],
            'ro'    => ['name' => 'Română',                 'flag' => '🇷🇴', 'rtl' => false],
            'ru'    => ['name' => 'Русский',                'flag' => '🇷🇺', 'rtl' => false],
            'sm'    => ['name' => 'Samoan',                 'flag' => '🇼🇸', 'rtl' => false],
            'sr'    => ['name' => 'Српски',                 'flag' => '🇷🇸', 'rtl' => false],
            'si'    => ['name' => 'සිංහල',                  'flag' => '🇱🇰', 'rtl' => false],
            'sk'    => ['name' => 'Slovenčina',             'flag' => '🇸🇰', 'rtl' => false],
            'sl'    => ['name' => 'Slovenščina',            'flag' => '🇸🇮', 'rtl' => false],
            'so'    => ['name' => 'Soomaali',               'flag' => '🇸🇴', 'rtl' => false],
            'es'    => ['name' => 'Español',                'flag' => '🇪🇸', 'rtl' => false],
            'su'    => ['name' => 'Basa Sunda',             'flag' => '🇮🇩', 'rtl' => false],
            'sw'    => ['name' => 'Kiswahili',              'flag' => '🇰🇪', 'rtl' => false],
            'sv'    => ['name' => 'Svenska',                'flag' => '🇸🇪', 'rtl' => false],
            'tl'    => ['name' => 'Filipino',               'flag' => '🇵🇭', 'rtl' => false],
            'tg'    => ['name' => 'Тоҷикӣ',                'flag' => '🇹🇯', 'rtl' => false],
            'ta'    => ['name' => 'தமிழ்',                   'flag' => '🇮🇳', 'rtl' => false],
            'tt'    => ['name' => 'Татарча',                'flag' => '🇷🇺', 'rtl' => false],
            'te'    => ['name' => 'తెలుగు',                  'flag' => '🇮🇳', 'rtl' => false],
            'th'    => ['name' => 'ภาษาไทย',               'flag' => '🇹🇭', 'rtl' => false],
            'tr'    => ['name' => 'Türkçe',                 'flag' => '🇹🇷', 'rtl' => false],
            'tk'    => ['name' => 'Türkmen',                'flag' => '🇹🇲', 'rtl' => false],
            'uk'    => ['name' => 'Українська',             'flag' => '🇺🇦', 'rtl' => false],
            'ur'    => ['name' => 'اردو',                   'flag' => '🇵🇰', 'rtl' => true ],
            'ug'    => ['name' => 'ئۇيغۇرچە',               'flag' => '🇨🇳', 'rtl' => true ],
            'uz'    => ['name' => "O'zbek",                 'flag' => '🇺🇿', 'rtl' => false],
            'vi'    => ['name' => 'Tiếng Việt',             'flag' => '🇻🇳', 'rtl' => false],
            'cy'    => ['name' => 'Cymraeg',                'flag' => '🏴󠁧󠁢󠁷󠁬󠁳󠁿', 'rtl' => false],
            'xh'    => ['name' => 'isiXhosa',               'flag' => '🇿🇦', 'rtl' => false],
            'yi'    => ['name' => 'ייִדיש',                  'flag' => '🇮🇱', 'rtl' => true ],
            'yo'    => ['name' => 'Yorùbá',                 'flag' => '🇳🇬', 'rtl' => false],
            'zu'    => ['name' => 'isiZulu',                'flag' => '🇿🇦', 'rtl' => false],
        ];
    }

    /** Return filtered known locales for the picker (excludes already-added) */
    public function getFilteredLocales(): array
    {
        $settings      = rescue(fn () => app(PlatformSettings::class), null, false);
        $existing      = array_merge(
            array_keys(config('app.available_locales', [])),
            array_keys($settings?->extra_locales ?? [])
        );
        $hidden        = $settings?->hidden_locales ?? [];
        // Re-include hidden so admin can re-add them
        $existing      = array_values(array_diff($existing, $hidden));

        $q     = strtolower(trim($this->localePickerSearch));
        $all   = static::knownLocales();
        $result = [];

        foreach ($all as $code => $info) {
            if (in_array($code, $existing)) continue;
            if ($q && ! str_contains(strtolower($code), $q) && ! str_contains(strtolower($info['name']), $q)) continue;
            $result[$code] = $info;
        }

        return $result;
    }

    // Delete confirmation
    public string $confirmDeleteLocale = '';

    // Inline translation editor
    public ?string $editingFile    = null;   // e.g. 'quiz.php'
    public array   $editingKeys    = [];     // flat ['key.sub' => 'translated value']
    public bool    $editorDirty    = false;
    public string  $editorSearch   = '';

    public function mount(): void
    {
        $this->stats = $this->loadStats();
        $this->selectedLocale = array_key_first($this->stats) ?? '';
    }

    public function selectLocale(string $locale): void
    {
        $this->selectedLocale      = $locale;
        $this->confirmDeleteLocale = '';
        $this->editingFile         = null;
        $this->editingKeys         = [];
        $this->editorDirty         = false;
        $this->editorSearch        = '';
    }

    // ── Editor ───────────────────────────────────────────────────────────────

    public function openEditor(string $file): void
    {
        if (! $this->selectedLocale) return;

        $this->editingFile  = $file;
        $this->editorSearch = '';
        $this->editorDirty  = false;

        $localePath = lang_path($this->selectedLocale . '/' . $file);

        if (file_exists($localePath)) {
            $this->editingKeys = $this->flattenWithValues(require $localePath);
        } else {
            // Fallback: use English keys as placeholders
            $enPath = lang_path('en/' . $file);
            $this->editingKeys = file_exists($enPath)
                ? $this->flattenWithValues(require $enPath)
                : [];
        }
    }

    public function closeEditor(): void
    {
        $this->editingFile  = null;
        $this->editingKeys  = [];
        $this->editorDirty  = false;
        $this->editorSearch = '';
    }

    public function updateKey(string $key, string $value): void
    {
        $this->editingKeys[$key] = $value;
        $this->editorDirty       = true;
    }

    public function saveTranslations(): void
    {
        if (! $this->editingFile || ! $this->selectedLocale) return;

        $localePath = lang_path($this->selectedLocale);
        if (! File::isDirectory($localePath)) {
            File::makeDirectory($localePath, 0755, true);
        }

        $filePath = $localePath . '/' . $this->editingFile;

        // Rebuild nested array from flat keys
        $nested = $this->unflattenKeys($this->editingKeys);

        // Write as PHP array file
        $content = "<?php\n\nreturn " . $this->arrayToPhpString($nested) . ";\n";
        File::put($filePath, $content);

        // Bust stats cache
        Cache::forget('lang.completeness');
        $this->stats      = $this->loadStats();
        $this->editorDirty = false;

        Notification::make()
            ->title("Saved {$this->editingFile} for [{$this->selectedLocale}] successfully.")
            ->success()
            ->send();
    }

    // ── Toggle locale enable/disable ─────────────────────────────────────────

    public function toggleLocale(string $locale): void
    {
        $settings = app(PlatformSettings::class);
        $enabled  = $settings->enabled_locales;

        if ($locale === 'en') {
            Notification::make()->title('English cannot be disabled.')->warning()->send();
            return;
        }

        if (in_array($locale, $enabled)) {
            $enabled = array_values(array_filter($enabled, fn ($l) => $l !== $locale));
        } else {
            $enabled[] = $locale;
        }

        app()->forgetInstance(PlatformSettings::class);
        $settings = app(PlatformSettings::class);
        $settings->enabled_locales = array_values($enabled);
        $settings->save();

        Cache::forget('laravel-settings.platform');

        Notification::make()
            ->title(in_array($locale, $enabled) ? "'{$locale}' enabled in switcher." : "'{$locale}' disabled from switcher.")
            ->success()
            ->send();
    }

    // ── Add language ─────────────────────────────────────────────────────────

    public function openAddModal(): void
    {
        $this->newLocaleCode      = '';
        $this->newLocaleName      = '';
        $this->newLocaleFlag      = '';
        $this->newLocaleRtl       = false;
        $this->localePickerSearch = '';
        $this->showAddModal       = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function selectKnownLocale(string $code): void
    {
        $known = static::knownLocales();
        if (! isset($known[$code])) return;

        $this->newLocaleCode = $code;
        $this->newLocaleName = $known[$code]['name'];
        $this->newLocaleFlag = $known[$code]['flag'];
        $this->newLocaleRtl  = $known[$code]['rtl'];
    }

    public function addLocale(): void
    {
        $code = strtolower(trim($this->newLocaleCode));
        $name = trim($this->newLocaleName);

        if (! preg_match('/^[a-z]{2,8}$/', $code)) {
            Notification::make()->title('Invalid locale code. Use 2–8 lowercase letters (e.g. es, pt).')->danger()->send();
            return;
        }
        if (empty($name)) {
            Notification::make()->title('Native language name is required.')->danger()->send();
            return;
        }

        $settings      = app(PlatformSettings::class);
        $builtIn       = array_keys(config('app.available_locales', []));
        $extraKeys     = array_keys($settings->extra_locales ?? []);
        $hiddenLocales = $settings->hidden_locales ?? [];
        $existingKeys  = array_merge($builtIn, $extraKeys);

        if (in_array($code, $existingKeys) && ! in_array($code, $hiddenLocales)) {
            Notification::make()->title("Locale '{$code}' already exists.")->warning()->send();
            return;
        }

        // Create stub lang directory + copy English files
        $enPath     = lang_path('en');
        $targetPath = lang_path($code);

        if (! File::isDirectory($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        foreach (File::files($enPath) as $enFile) {
            $dest = $targetPath . '/' . $enFile->getFilename();
            if (! File::exists($dest)) {
                File::copy($enFile->getPathname(), $dest);
            }
        }

        app()->forgetInstance(PlatformSettings::class);
        $settings = app(PlatformSettings::class);

        $extra        = $settings->extra_locales ?? [];
        $extra[$code] = $name;
        $settings->extra_locales = $extra;

        // Save custom flag if provided
        if ($this->newLocaleFlag) {
            $flags        = $settings->extra_locale_flags ?? [];
            $flags[$code] = $this->newLocaleFlag;
            $settings->extra_locale_flags = $flags;
        }

        // Save RTL setting
        $rtl = $settings->rtl_locales ?? [];
        if ($this->newLocaleRtl && ! in_array($code, $rtl)) {
            $rtl[] = $code;
        } else {
            $rtl = array_values(array_filter($rtl, fn ($l) => $l !== $code));
        }
        $settings->rtl_locales = $rtl;

        // Auto-enable in switcher
        $enabled = $settings->enabled_locales ?? [];
        if (! in_array($code, $enabled)) {
            $enabled[] = $code;
        }
        $settings->enabled_locales = array_values($enabled);

        $hidden = array_values(array_filter($settings->hidden_locales ?? [], fn ($l) => $l !== $code));
        $settings->hidden_locales = $hidden;

        $settings->save();
        Cache::forget('laravel-settings.platform');
        Cache::forget('lang.completeness');

        $this->showAddModal   = false;
        $this->newLocaleCode  = '';
        $this->newLocaleName  = '';
        $this->stats          = $this->loadStats();
        $this->selectedLocale = $code;

        Notification::make()
            ->title("Language '{$name}' ({$code}) added. Stub files created from English.")
            ->success()
            ->send();
    }

    // ── Delete language ───────────────────────────────────────────────────────

    public function confirmDelete(string $locale): void
    {
        $this->confirmDeleteLocale = ($this->confirmDeleteLocale === $locale) ? '' : $locale;
    }

    public function deleteLocale(string $locale): void
    {
        if ($locale === 'en') {
            Notification::make()->title('English cannot be deleted.')->danger()->send();
            return;
        }

        app()->forgetInstance(PlatformSettings::class);
        $settings = app(PlatformSettings::class);

        $extra = $settings->extra_locales ?? [];
        unset($extra[$locale]);
        $settings->extra_locales = $extra;

        $hidden = $settings->hidden_locales ?? [];
        if (! in_array($locale, $hidden)) {
            $hidden[] = $locale;
        }
        $settings->hidden_locales = array_values($hidden);

        $enabled = array_values(array_filter($settings->enabled_locales ?? [], fn ($l) => $l !== $locale));
        $settings->enabled_locales = $enabled;

        $settings->save();

        $langDir = lang_path($locale);
        if (File::isDirectory($langDir)) {
            File::deleteDirectory($langDir);
        }

        Cache::forget('laravel-settings.platform');
        Cache::forget('lang.completeness');

        $this->confirmDeleteLocale = '';
        $this->editingFile         = null;
        $this->editingKeys         = [];
        $this->stats               = $this->loadStats();

        if ($this->selectedLocale === $locale) {
            $this->selectedLocale = array_key_first($this->stats) ?? '';
        }

        Notification::make()
            ->title("Language '{$locale}' has been removed.")
            ->success()
            ->send();
    }

    // ── Refresh ───────────────────────────────────────────────────────────────

    public function refresh(): void
    {
        Cache::forget('lang.completeness');
        $this->stats = $this->loadStats();
        if (! isset($this->stats[$this->selectedLocale])) {
            $this->selectedLocale = array_key_first($this->stats) ?? '';
        }

        Notification::make()
            ->title('Translation stats refreshed.')
            ->success()
            ->send();
    }

    // ── Stats loader ──────────────────────────────────────────────────────────

    private function loadStats(): array
    {
        return Cache::remember('lang.completeness', 300, function () {
            $enPath  = lang_path('en');
            $enFiles = glob($enPath . '/*.php') ?: [];

            $settings      = rescue(fn () => app(PlatformSettings::class), null, false);
            $configLocales = config('app.available_locales', []);
            $extraLocales  = $settings?->extra_locales ?? [];
            $hiddenLocales = $settings?->hidden_locales ?? [];

            $available = array_merge($configLocales, $extraLocales);
            foreach ($hiddenLocales as $h) {
                unset($available[$h]);
            }

            $results = [];

            foreach (array_keys($available) as $locale) {
                if ($locale === 'en') continue;

                $localePath  = lang_path($locale);
                $fileResults = [];
                $totalEn     = 0;
                $totalHit    = 0;

                foreach ($enFiles as $enFile) {
                    $filename   = basename($enFile);
                    $localeFile = $localePath . '/' . $filename;

                    $enKeys  = $this->flattenKeys(require $enFile);
                    $totalEn += count($enKeys);

                    if (! file_exists($localeFile)) {
                        $fileResults[] = ['file' => $filename, 'en_count' => count($enKeys), 'translated' => 0, 'missing' => count($enKeys), 'pct' => 0];
                        continue;
                    }

                    $localeKeys = $this->flattenKeys(require $localeFile);
                    $missing    = count(array_diff($enKeys, $localeKeys));
                    $translated = count($enKeys) - $missing;
                    $totalHit  += $translated;
                    $pct        = count($enKeys) > 0 ? round($translated / count($enKeys) * 100) : 100;

                    $fileResults[] = ['file' => $filename, 'en_count' => count($enKeys), 'translated' => $translated, 'missing' => $missing, 'pct' => $pct];
                }

                $overallPct = $totalEn > 0 ? round($totalHit / $totalEn * 100) : 100;

                $results[$locale] = [
                    'locale'      => $locale,
                    'name'        => $available[$locale],
                    'files'       => $fileResults,
                    'total_en'    => $totalEn,
                    'total_hit'   => $totalHit,
                    'overall_pct' => $overallPct,
                    'is_custom'   => isset($extraLocales[$locale]),
                ];
            }

            return $results;
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Returns flat list of keys (for counting) */
    private function flattenKeys(array $array, string $prefix = ''): array
    {
        $keys = [];
        foreach ($array as $key => $value) {
            $full = $prefix ? "{$prefix}.{$key}" : (string) $key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->flattenKeys($value, $full));
            } else {
                $keys[] = $full;
            }
        }
        return $keys;
    }

    /** Returns flat assoc array of key => value (for editor) */
    private function flattenWithValues(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $full = $prefix ? "{$prefix}.{$key}" : (string) $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenWithValues($value, $full));
            } else {
                $result[$full] = (string) $value;
            }
        }
        return $result;
    }

    /** Rebuilds nested array from flat dot-notation keys */
    private function unflattenKeys(array $flat): array
    {
        $result = [];
        foreach ($flat as $key => $value) {
            $parts = explode('.', $key);
            $ref   = &$result;
            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $ref[$part] = $value;
                } else {
                    if (! isset($ref[$part]) || ! is_array($ref[$part])) {
                        $ref[$part] = [];
                    }
                    $ref = &$ref[$part];
                }
            }
            unset($ref);
        }
        return $result;
    }

    /** Converts PHP array to formatted string for writing to file */
    private function arrayToPhpString(array $array, int $indent = 1): string
    {
        $pad  = str_repeat('    ', $indent);
        $pad0 = str_repeat('    ', $indent - 1);
        $lines = [];
        foreach ($array as $key => $value) {
            $k = is_int($key) ? $key : "'" . addslashes($key) . "'";
            if (is_array($value)) {
                $lines[] = "{$pad}{$k} => " . $this->arrayToPhpString($value, $indent + 1) . ",";
            } else {
                $v = "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
                $lines[] = "{$pad}{$k} => {$v},";
            }
        }
        return "[\n" . implode("\n", $lines) . "\n{$pad0}]";
    }
}
