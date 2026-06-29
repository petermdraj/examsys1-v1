<x-filament-widgets::widget class="cc-widget">
    <div class="cc-kpi-grid">
        <a href="{{ $liveUrl }}" class="cc-card cc-kpi-card hover:shadow-md transition-shadow">
            <div>
                <div class="cc-kpi-value">{{ str_pad((string) $liveCount, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="cc-kpi-label">{{ __('admin.cc_live_assessments') }}</div>
                <div class="cc-kpi-desc">{{ $liveLabel }}</div>
            </div>
            <div class="cc-kpi-icon cc-kpi-icon--green">
                <x-heroicon-o-signal class="h-5 w-5" />
            </div>
        </a>

        <div class="cc-card cc-kpi-card">
            <div>
                <div class="cc-kpi-value">{{ $totalStudents }}</div>
                <div class="cc-kpi-label">{{ __('admin.cc_registered_students') }}</div>
                <div class="cc-kpi-desc">{{ __('admin.cc_student_growth', ['pct' => $growthPercentage]) }}</div>
            </div>
            <div class="cc-kpi-icon cc-kpi-icon--blue">
                <x-heroicon-o-users class="h-5 w-5" />
            </div>
        </div>

        <div class="cc-card cc-kpi-card">
            <div>
                <div class="cc-kpi-value">{{ $passRate }}%</div>
                <div class="cc-kpi-label">{{ __('admin.cc_pass_rate') }}</div>
                <div class="cc-kpi-desc">{{ __('admin.cc_pass_rate_desc') }}</div>
            </div>
            <div class="cc-kpi-icon cc-kpi-icon--amber">
                <x-heroicon-o-academic-cap class="h-5 w-5" />
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
