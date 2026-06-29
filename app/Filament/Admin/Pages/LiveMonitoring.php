<?php

namespace App\Filament\Admin\Pages;

use App\Models\Quiz;
use App\Services\Exam\AttemptInterventionService;
use App\Services\Exam\AttemptMonitoringService;
use App\Services\Exam\AttemptRiskAnalyzer;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LiveMonitoring extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.live-monitoring';

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_live_monitoring');
    }

    public function getTitle(): string
    {
        return __('admin.nav_live_monitoring');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('admin.monitor_live_exams') ?? false;
    }

    public ?string $filterQuizId = 'all';

    public ?string $filterRisk = 'all';

    public ?string $filterStatus = 'all';

    public string $search = '';

    public array $kpis = [];

    public $sessions = [];

    public ?string $lastRefreshed = null;

    public function mount(): void
    {
        $this->refreshSessions();
    }

    public function refreshSessions(): void
    {
        $monitoring = app(AttemptMonitoringService::class);
        $analyzer = app(AttemptRiskAnalyzer::class);

        $active = $monitoring->getLiveSessions(['status' => 'all']);

        foreach ($active as $attempt) {
            if ($attempt->status === 'in_progress') {
                $analyzer->analyze($attempt);
            }
        }

        $this->kpis = $monitoring->getLiveKpis();
        $this->sessions = $monitoring->getLiveSessions([
            'quiz_id'    => $this->filterQuizId,
            'risk_level' => $this->filterRisk,
            'status'     => $this->filterStatus,
            'search'     => $this->search,
        ]);
        $this->lastRefreshed = now()->format('h:i:s A');
    }

    public function updatedFilterQuizId(): void
    {
        $this->refreshSessions();
    }

    public function updatedFilterRisk(): void
    {
        $this->refreshSessions();
    }

    public function updatedFilterStatus(): void
    {
        $this->refreshSessions();
    }

    public function updatedSearch(): void
    {
        $this->refreshSessions();
    }

    public function pauseAttempt(string $attemptId): void
    {
        $attempt = $this->findAttempt($attemptId);
        app(AttemptInterventionService::class)->pause($attempt);

        Notification::make()->title(__('admin.live_paused_success'))->success()->send();
        $this->refreshSessions();
    }

    public function resumeAttempt(string $attemptId): void
    {
        $attempt = $this->findAttempt($attemptId);
        app(AttemptInterventionService::class)->resume($attempt);

        Notification::make()->title(__('admin.live_resumed_success'))->success()->send();
        $this->refreshSessions();
    }

    public function terminateAttempt(string $attemptId): void
    {
        $attempt = $this->findAttempt($attemptId);
        app(AttemptInterventionService::class)->terminate($attempt);

        Notification::make()->title(__('admin.live_terminated_success'))->success()->send();
        $this->refreshSessions();
    }

    public function reopenAttempt(string $attemptId): void
    {
        $attempt = $this->findAttempt($attemptId);
        app(AttemptInterventionService::class)->reopen($attempt);

        Notification::make()->title(__('admin.live_reopened_success'))->success()->send();
        $this->refreshSessions();
    }

    public function getQuizzesProperty()
    {
        return Quiz::orderBy('title')->get(['id', 'title']);
    }

    protected function findAttempt(string $id): \App\Models\Attempt
    {
        return \App\Models\Attempt::with(['user', 'quiz'])->findOrFail($id);
    }
}
