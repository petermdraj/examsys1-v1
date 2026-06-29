<?php

namespace App\Livewire\Installer;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use PDO;

class InstallerWizard extends Component
{
    // ── Step routing ────────────────────────────────────────────────────────
    private const STEP_ROUTES = [
        1 => 'installer',
        2 => 'installer.database',
        3 => 'installer.app',
        4 => 'installer.ready',
    ];

    public int $step       = 1;
    public int $totalSteps = 4;

    // Step 1 — Requirements
    public array $requirements = [];

    // Step 2 — Database
    public string  $dbHost     = '127.0.0.1';
    public string  $dbPort     = '3306';
    public string  $dbName     = 'examsys1';
    public string  $dbUser     = 'root';
    public string  $dbPassword = '';
    public ?string $dbError    = null;
    public bool    $dbTested   = false;

    // Step 3 — App
    public string $appName       = 'ExamSys';
    public string $appUrl        = '';
    public string $adminName     = 'Admin';
    public string $adminEmail    = 'admin@example.com';
    public string $adminPassword = '';

    // Demo data (step 3)
    public bool   $installDemo = false;
    public string $demoType    = 'competition'; // competition | school | professional

    // Step 4 — Install
    public int    $currentStep  = 0;   // 0 = not started; 1-9 = step in progress
    public bool   $installed    = false;
    public bool   $installError = false;
    public string $installErrMsg = '';

    // ── Fields persisted to/from session ────────────────────────────────────
    private const SESSION_KEY = 'examsys_installer';

    private const PERSISTED_FIELDS = [
        'dbHost', 'dbPort', 'dbName', 'dbUser', 'dbPassword', 'dbTested',
        'appName', 'appUrl', 'adminName', 'adminEmail', 'adminPassword',
        'installDemo', 'demoType',
    ];

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        // Determine step from current route name
        $routeName  = Route::currentRouteName();
        $this->step = (int) (array_search($routeName, self::STEP_ROUTES) ?: 1);

        $this->appUrl = url('/');

        // Restore previously entered values from session
        $saved = session(self::SESSION_KEY, []);
        foreach (self::PERSISTED_FIELDS as $field) {
            if (array_key_exists($field, $saved)) {
                $this->$field = $saved[$field];
            }
        }

        $this->checkRequirements();
    }

    // ── Navigation ──────────────────────────────────────────────────────────

    public function next(): void
    {
        $rules = $this->rulesForStep($this->step);
        if ($rules) {
            $this->validate($rules);
        }

        if ($this->step === 2 && !$this->dbTested) {
            $this->testDatabase();
            if ($this->dbError) {
                return;
            }
        }

        $this->persistToSession();

        $nextRoute = self::STEP_ROUTES[$this->step + 1] ?? null;
        if ($nextRoute) {
            $this->redirect(route($nextRoute));
        }
    }

    public function prev(): void
    {
        $this->persistToSession();

        $prevRoute = self::STEP_ROUTES[$this->step - 1] ?? null;
        if ($prevRoute) {
            $this->redirect(route($prevRoute));
        }
    }

    // ── Database test ────────────────────────────────────────────────────────

    public function testDatabase(): void
    {
        $this->dbError  = null;
        $this->dbTested = false;

        try {
            $dsn = "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$this->dbName}";
            new PDO($dsn, $this->dbUser, $this->dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $this->dbTested = true;
            $this->persistToSession();
        } catch (\PDOException $e) {
            $this->dbError = $e->getMessage();
        }
    }

    public function toggleDemo(): void
    {
        $this->installDemo = !$this->installDemo;
    }

    public function setDemoType(string $type): void
    {
        $this->demoType = $type;
    }

    // ── Install (driven by wire:poll — one step per Livewire request) ────────

    /**
     * Begin the installation — wire:click triggers this once.
     * Sets currentStep to 1, which causes wire:poll to start calling advanceStep().
     */
    public function startInstall(): void
    {
        // Clear any stale config/route/view caches from a previous failed install
        // attempt BEFORE the first poll fires. Doing it here (in the same request
        // that renders the progress pane) means subsequent wire:poll requests all
        // bootstrap identically — no mid-install APP_KEY re-parse that breaks
        // Livewire's HMAC checksum.
        foreach ([
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/routes-v7.php'),
            base_path('bootstrap/cache/events.php'),
        ] as $cacheFile) {
            if (file_exists($cacheFile)) {
                @unlink($cacheFile);
            }
        }

        $this->currentStep   = 1;
        $this->installError  = false;
        $this->installErrMsg = '';
    }

    /**
     * Called by wire:poll every 800 ms while the progress pane is shown.
     * Runs exactly ONE step per call and increments currentStep so the
     * next poll picks up the next step. Stops automatically when done/error.
     *
     * Steps:
     *  1 – Write .env + refresh in-memory DB config (no config:clear — that
     *      causes APP_KEY to be re-parsed between requests, breaking Livewire HMAC)
     *  2 – migrate
     *  3 – db:seed (DatabaseSeeder)
     *  4 – create admin user
     *  5 – storage:link
     *  6 – demo seeder (skipped if installDemo === false)
     *  7 – finalise (write installed flag, cache routes + views)
     */
    public function advanceStep(): void
    {
        // Guard: only run when actively installing and no error
        if ($this->currentStep < 1 || $this->installed || $this->installError) {
            return;
        }

        // Step 6 is conditional — skip it if demo data was not requested
        if ($this->currentStep === 6 && ! $this->installDemo) {
            $this->currentStep = 7;
            return;
        }

        // Step 7 is the last; if we're past it, we're done
        if ($this->currentStep > 7) {
            return;
        }

        try {
            match ($this->currentStep) {
                1 => $this->writeEnv(),
                2 => Artisan::call('migrate', ['--force' => true]),
                3 => Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]),
                4 => $this->createAdminUser(),
                5 => Artisan::call('storage:link', ['--force' => true]),
                6 => $this->runDemoSeeder(),
                7 => $this->finaliseInstall(),
            };

            $this->currentStep++;
        } catch (\Throwable $e) {
            $this->installError  = true;
            $this->installErrMsg = $e->getMessage();
        }
    }

    private function runDemoSeeder(): void
    {
        $seederClass = match ($this->demoType) {
            'school'       => \Database\Seeders\Demo\SchoolPortalSeeder::class,
            'professional' => \Database\Seeders\Demo\ProfessionalTestSeeder::class,
            default        => \Database\Seeders\Demo\CompetitionExamSeeder::class,
        };

        app($seederClass)->run();
    }

    private function finaliseInstall(): void
    {
        // Write the installed flag FIRST so AppServiceProvider stops overriding
        // cache/session drivers on subsequent requests.
        File::put(storage_path('installed'), now()->toIso8601String());
        session()->forget(self::SESSION_KEY);

        // Cache routes and views for performance — intentionally NOT running
        // config:cache here because it writes bootstrap/cache/config.php which
        // can cause Livewire HMAC checksum failures if any poll requests are
        // still in-flight. Users can run `php artisan optimize` after install.
        try { Artisan::call('route:cache'); } catch (\Throwable) {}
        try { Artisan::call('view:cache');  } catch (\Throwable) {}

        $this->installed = true;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function checkRequirements(): void
    {
        $this->requirements = [
            ['label' => 'PHP >= 8.2',          'pass' => version_compare(PHP_VERSION, '8.2', '>=')],
            ['label' => 'PDO Extension',        'pass' => extension_loaded('pdo')],
            ['label' => 'MySQL PDO Driver',     'pass' => extension_loaded('pdo_mysql')],
            ['label' => 'OpenSSL Extension',    'pass' => extension_loaded('openssl')],
            ['label' => 'Mbstring Extension',   'pass' => extension_loaded('mbstring')],
            ['label' => 'Tokenizer Extension',  'pass' => extension_loaded('tokenizer')],
            ['label' => 'JSON Extension',       'pass' => extension_loaded('json')],
            ['label' => 'cURL Extension',       'pass' => extension_loaded('curl')],
            ['label' => 'GD Extension',         'pass' => extension_loaded('gd')],
            ['label' => 'storage/ writable',    'pass' => is_writable(storage_path())],
            ['label' => '.env writable',        'pass' => is_writable(base_path('.env')) || !file_exists(base_path('.env'))],
            ['label' => 'Redis / Predis',       'pass' => extension_loaded('redis') || class_exists('\\Predis\\Client'),
             'warn' => true, 'note' => 'Required for AI streaming and queue workers.'],
            ['label' => 'proc_open() enabled',  'pass' => function_exists('proc_open'),
             'warn' => true, 'note' => 'Required for background queue workers.'],
        ];
    }

    private function persistToSession(): void
    {
        $data = [];
        foreach (self::PERSISTED_FIELDS as $field) {
            $data[$field] = $this->$field;
        }
        session([self::SESSION_KEY => $data]);
    }

    private function writeEnv(): void
    {
        $envPath     = base_path('.env');
        $examplePath = base_path('.env.example');
        $env = file_exists($envPath) ? file_get_contents($envPath) : file_get_contents($examplePath);

        $replacements = [
            'APP_NAME'         => '"' . $this->appName . '"',
            'APP_URL'          => $this->appUrl,
            'DB_HOST'          => $this->dbHost,
            'DB_PORT'          => $this->dbPort,
            'DB_DATABASE'      => $this->dbName,
            'DB_USERNAME'      => $this->dbUser,
            'DB_PASSWORD'      => $this->dbPassword,
            'DB_CONNECTION'    => 'mysql',
            'QUEUE_CONNECTION' => 'database',
            'CACHE_STORE'      => 'database',
        ];

        foreach ($replacements as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $env);

        // Refresh the in-memory DB config immediately so that Artisan::call('migrate')
        // in the next step uses the correct credentials — even if a stale config cache
        // existed from a previous (failed) install attempt.
        // NOTE: We intentionally do NOT run config:clear here. Deleting the config
        // cache file causes subsequent wire:poll requests to re-bootstrap with a
        // subtly different APP_KEY parse path, which breaks Livewire's HMAC checksum.
        \Illuminate\Support\Facades\Config::set('database.connections.mysql', [
            'driver'    => 'mysql',
            'host'      => $this->dbHost,
            'port'      => $this->dbPort,
            'database'  => $this->dbName,
            'username'  => $this->dbUser,
            'password'  => $this->dbPassword,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
    }

    private function createAdminUser(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => $this->adminEmail],
            [
                'name'                      => $this->adminName,
                'password'                  => Hash::make($this->adminPassword),
                'role'                      => 'super_admin',
                'is_active'                 => true,
                'ai_credits_free_remaining' => 10,
                'email_verified_at'         => now(),
            ]
        );
    }

    private function rulesForStep(int $step): array
    {
        return match ($step) {
            2 => ['dbHost' => 'required', 'dbName' => 'required', 'dbUser' => 'required'],
            3 => [
                'appName'       => 'required',
                'appUrl'        => 'required|url',
                'adminEmail'    => 'required|email',
                'adminPassword' => 'required|min:8',
            ],
            default => [],
        };
    }

    public function render()
    {
        return view('installer.wizard')->layout('installer.layout');
    }
}
