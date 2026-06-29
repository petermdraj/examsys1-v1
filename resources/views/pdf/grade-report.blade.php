<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.nav_grade_reports') }} — {{ $quiz->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h2 { margin: 0 0 4px; font-size: 18px; }
        .header p { margin: 2px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .pass { color: #059669; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .meta { margin-bottom: 12px; }
        .meta span { margin-right: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ __('admin.nav_grade_reports') }}</h2>
        <p>{{ $quiz->title }}</p>
        <p>{{ __('admin.grade_export_generated') }}: {{ $generatedAt->format('d M Y, H:i') }}</p>
    </div>

    <div class="meta">
        <span><strong>{{ __('admin.grade_export_lecturer') }}:</strong> {{ $quiz->lecturer?->name ?? '—' }}</span>
        <span><strong>{{ __('admin.grade_export_pass_threshold') }}:</strong> {{ $quiz->pass_percentage ?? 0 }}%</span>
        <span><strong>{{ __('admin.grade_preview_count', ['count' => $rows->count()]) }}</strong></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('admin.grade_col_rank') }}</th>
                <th>{{ __('admin.grade_col_student') }}</th>
                <th>{{ __('admin.grade_col_email') }}</th>
                <th>{{ __('admin.grade_col_batch') }}</th>
                <th>{{ __('admin.grade_col_attempt') }}</th>
                <th>{{ __('admin.grade_col_score') }}</th>
                <th>{{ __('admin.grade_col_percentage') }}</th>
                <th>{{ __('admin.grade_col_result') }}</th>
                <th>{{ __('admin.grade_col_submitted') }}</th>
                <th>{{ __('admin.grade_col_time') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['rank'] }}</td>
                    <td>{{ $row['student'] }}</td>
                    <td>{{ $row['email'] }}</td>
                    <td>{{ $row['batch'] }}</td>
                    <td>{{ $row['attempt_number'] }}</td>
                    <td>{{ $row['score'] }} / {{ $row['total_marks'] }}</td>
                    <td>{{ $row['percentage'] }}%</td>
                    <td class="{{ $row['is_passed'] ? 'pass' : 'fail' }}">{{ $row['result'] }}</td>
                    <td>{{ $row['submitted_at']?->format('d M Y, H:i') ?? '—' }}</td>
                    <td>{{ $row['time_minutes'] !== null ? $row['time_minutes'] . ' min' : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;color:#888;">{{ __('admin.grade_no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
