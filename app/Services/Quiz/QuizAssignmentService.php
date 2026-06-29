<?php

namespace App\Services\Quiz;

use App\Models\Quiz;
use App\Models\QuizAssignment;
use App\Models\QuizEnrollment;
use App\Models\StudentBatch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizAssignmentService
{
    public function assignToBatch(Quiz $quiz, User $lecturer, StudentBatch $batch): QuizAssignment
    {
        return DB::transaction(function () use ($quiz, $lecturer, $batch) {
            $assignment = QuizAssignment::firstOrCreate(
                [
                    'quiz_id'          => $quiz->id,
                    'student_batch_id' => $batch->id,
                ],
                [
                    'lecturer_id' => $lecturer->id,
                    'assigned_at' => now(),
                ]
            );

            $batch->students()->each(fn (User $student) => $this->ensureEnrollment($quiz, $student));

            return $assignment;
        });
    }

    public function assignToStudent(Quiz $quiz, User $lecturer, User $student): QuizAssignment
    {
        if ($student->role !== 'student') {
            throw new \InvalidArgumentException('Assignments can only target student accounts.');
        }

        return DB::transaction(function () use ($quiz, $lecturer, $student) {
            $assignment = QuizAssignment::firstOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'user_id' => $student->id,
                ],
                [
                    'lecturer_id' => $lecturer->id,
                    'assigned_at' => now(),
                ]
            );

            $this->ensureEnrollment($quiz, $student);

            return $assignment;
        });
    }

    public function isAssignedToStudent(Quiz $quiz, User $student): bool
    {
        if ($student->role !== 'student') {
            return true;
        }

        if (QuizAssignment::where('quiz_id', $quiz->id)->where('user_id', $student->id)->exists()) {
            return true;
        }

        if ($student->student_batch_id) {
            return QuizAssignment::where('quiz_id', $quiz->id)
                ->where('student_batch_id', $student->student_batch_id)
                ->exists();
        }

        return false;
    }

    public function assignedQuizIdsFor(User $student): Collection
    {
        if ($student->role !== 'student') {
            return collect();
        }

        $individual = QuizAssignment::where('user_id', $student->id)->pluck('quiz_id');

        $batch = $student->student_batch_id
            ? QuizAssignment::where('student_batch_id', $student->student_batch_id)->pluck('quiz_id')
            : collect();

        return $individual->merge($batch)->unique()->values();
    }

    public function scopeAssignedToStudent($query, User $student)
    {
        if ($student->role !== 'student') {
            return $query;
        }

        $ids = $this->assignedQuizIdsFor($student);

        return $ids->isEmpty() ? $query->whereRaw('0 = 1') : $query->whereIn('id', $ids);
    }

    public function syncEnrollmentsForBatch(StudentBatch $batch): void
    {
        $quizIds = QuizAssignment::where('student_batch_id', $batch->id)->pluck('quiz_id');

        foreach ($quizIds as $quizId) {
            $quiz = Quiz::find($quizId);
            if (! $quiz) {
                continue;
            }

            $batch->students()->each(fn (User $student) => $this->ensureEnrollment($quiz, $student));
        }
    }

    public function syncEnrollmentsForStudent(User $student): void
    {
        if ($student->role !== 'student' || ! $student->student_batch_id) {
            return;
        }

        $quizIds = QuizAssignment::where('student_batch_id', $student->student_batch_id)->pluck('quiz_id');

        foreach ($quizIds as $quizId) {
            $quiz = Quiz::find($quizId);
            if ($quiz) {
                $this->ensureEnrollment($quiz, $student);
            }
        }
    }

    private function ensureEnrollment(Quiz $quiz, User $student): void
    {
        QuizEnrollment::firstOrCreate(
            ['quiz_id' => $quiz->id, 'user_id' => $student->id],
            ['enrolled_at' => now(), 'source' => 'assigned']
        );
    }
}
