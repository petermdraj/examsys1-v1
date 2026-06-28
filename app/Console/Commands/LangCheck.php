<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LangCheck extends Command
{
    protected $signature   = 'lang:check {locale? : Locale to check (default: all non-en locales)} {--show-missing : Show individual missing keys}';
    protected $description = 'Check translation completeness by recursively diffing keys against lang/en/';

    public function handle(): int
    {
        $enPath = lang_path('en');

        if (! is_dir($enPath)) {
            $this->error('No lang/en/ directory found.');
            return 1;
        }

        $available = config('app.available_locales', []);
        $localesToCheck = array_filter(array_keys($available), fn($l) => $l !== 'en');

        if ($targetLocale = $this->argument('locale')) {
            $localesToCheck = [$targetLocale];
        }

        $enFiles = glob($enPath . '/*.php');
        $allClean = true;

        foreach ($localesToCheck as $locale) {
            $this->newLine();
            $localeName = $available[$locale] ?? $locale;
            $this->info("=== Checking: {$locale} ({$localeName}) ===");

            $localePath  = lang_path($locale);
            $totalEn     = 0;
            $totalHit    = 0;
            $tableRows   = [];

            foreach ($enFiles as $enFile) {
                $filename    = basename($enFile);
                $localeFile  = $localePath . '/' . $filename;
                $enKeys      = $this->flattenKeys(require $enFile);
                $totalEn    += count($enKeys);

                if (! file_exists($localeFile)) {
                    $allClean  = false;
                    $tableRows[] = [$filename, count($enKeys), 0, count($enKeys), '0%', '❌ FILE MISSING'];
                    continue;
                }

                $localeKeys  = $this->flattenKeys(require $localeFile);
                $missing     = array_diff($enKeys, $localeKeys);
                $translated  = count($enKeys) - count($missing);
                $totalHit   += $translated;
                $pct         = count($enKeys) > 0 ? round($translated / count($enKeys) * 100) : 100;
                $status      = $pct === 100 ? '✅' : ($pct >= 80 ? '⚠️' : '❌');

                if (count($missing) > 0) {
                    $allClean = false;
                    if ($this->option('show-missing')) {
                        $this->warn("  Missing in {$filename}:");
                        foreach ($missing as $k) {
                            $this->line("    - {$k}");
                        }
                    }
                }

                $tableRows[] = [$filename, count($enKeys), $translated, count($missing), "{$pct}%", $status];
            }

            $this->table(['File', 'EN Keys', 'Translated', 'Missing', '% Complete', ''], $tableRows);

            $overallPct = $totalEn > 0 ? round($totalHit / $totalEn * 100) : 100;
            $this->info("Overall {$locale}: {$totalHit}/{$totalEn} keys ({$overallPct}%)");
        }

        return $allClean ? 0 : 1;
    }

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
}
