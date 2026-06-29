<?php

namespace App\Services\Exam;

use App\Models\Attempt;
use App\Models\Quiz;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExamGradeReportService
{
    public const GRADE_STATUSES = ['completed', 'terminated', 'timed_out'];

    public function buildRows(Quiz $quiz, ?string $batchId = null, string $attemptMode = 'best'): Collection
    {
        $attempts = $this->fetchAttempts($quiz, $batchId);
        $filtered = $this->applyAttemptMode($attempts, $attemptMode);

        return $filtered
            ->sortByDesc(fn (Attempt $a) => (float) $a->percentage)
            ->values()
            ->map(function (Attempt $attempt, int $index) {
                return [
                    'rank'           => $index + 1,
                    'student'        => $attempt->user->name,
                    'email'          => $attempt->user->email,
                    'batch'          => $attempt->user->studentBatch?->name ?? '—',
                    'attempt_number' => $attempt->attempt_number,
                    'score'          => $attempt->score,
                    'total_marks'    => $attempt->total_marks,
                    'percentage'     => $attempt->percentage,
                    'is_passed'      => $attempt->is_passed,
                    'result'         => $attempt->is_passed
                        ? __('admin.grade_result_pass')
                        : __('admin.grade_result_fail'),
                    'submitted_at'   => $attempt->submitted_at,
                    'time_minutes' => $attempt->time_taken_seconds
                        ? round($attempt->time_taken_seconds / 60, 1)
                        : null,
                ];
            });
    }

    public function exportExcel(Quiz $quiz, ?string $batchId = null, string $attemptMode = 'best'): ?string
    {
        $rows = $this->buildRows($quiz, $batchId, $attemptMode);

        if ($rows->isEmpty()) {
            return null;
        }

        $quiz->loadMissing('lecturer');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', __('admin.grade_export_quiz'));
        $sheet->setCellValue('B1', $quiz->title);
        $sheet->setCellValue('A2', __('admin.grade_export_lecturer'));
        $sheet->setCellValue('B2', $quiz->lecturer?->name ?? '—');
        $sheet->setCellValue('A3', __('admin.grade_export_pass_threshold'));
        $sheet->setCellValue('B3', ($quiz->pass_percentage ?? 0) . '%');
        $sheet->setCellValue('A4', __('admin.grade_export_generated'));
        $sheet->setCellValue('B4', now()->format('d M Y, H:i'));

        $headers = [
            __('admin.grade_col_rank'),
            __('admin.grade_col_student'),
            __('admin.grade_col_email'),
            __('admin.grade_col_batch'),
            __('admin.grade_col_attempt'),
            __('admin.grade_col_score'),
            __('admin.grade_col_percentage'),
            __('admin.grade_col_result'),
            __('admin.grade_col_submitted'),
            __('admin.grade_col_time'),
        ];
        $sheet->fromArray([$headers], null, 'A6');

        $row = 7;
        foreach ($rows as $r) {
            $sheet->fromArray([[
                $r['rank'],
                $r['student'],
                $r['email'],
                $r['batch'],
                $r['attempt_number'],
                $r['score'] . ' / ' . $r['total_marks'],
                $r['percentage'] . '%',
                $r['result'],
                $r['submitted_at']?->format('d M Y, H:i') ?? '—',
                $r['time_minutes'] !== null ? $r['time_minutes'] . ' min' : '—',
            ]], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $path = tempnam(sys_get_temp_dir(), 'grades_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    public function exportPdf(Quiz $quiz, ?string $batchId = null, string $attemptMode = 'best')
    {
        $rows = $this->buildRows($quiz, $batchId, $attemptMode);
        $quiz->loadMissing('lecturer');

        $pdf = Pdf::loadView('pdf.grade-report', [
            'quiz'        => $quiz,
            'rows'        => $rows,
            'generatedAt' => now(),
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'grade-report-' . Str::slug($quiz->title) . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    protected function fetchAttempts(Quiz $quiz, ?string $batchId): Collection
    {
        $query = Attempt::query()
            ->where('quiz_id', $quiz->id)
            ->whereIn('status', self::GRADE_STATUSES)
            ->with(['user.studentBatch'])
            ->orderByDesc('submitted_at');

        if ($batchId) {
            $query->whereHas('user', fn ($q) => $q->where('student_batch_id', $batchId));
        }

        return $query->get();
    }

    protected function applyAttemptMode(Collection $attempts, string $attemptMode): Collection
    {
        if ($attemptMode === 'all') {
            return $attempts;
        }

        return $attempts
            ->groupBy('user_id')
            ->map(function (Collection $userAttempts) use ($attemptMode) {
                if ($attemptMode === 'latest') {
                    return $userAttempts->sortByDesc('submitted_at')->first();
                }

                return $userAttempts->sort(function (Attempt $a, Attempt $b) {
                    $pct = (float) $b->percentage <=> (float) $a->percentage;
                    if ($pct !== 0) {
                        return $pct;
                    }

                    return ($b->submitted_at?->timestamp ?? 0) <=> ($a->submitted_at?->timestamp ?? 0);
                })->first();
            })
            ->filter()
            ->values();
    }
}
