<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class QuizGeneratorResponseParser
{
    public function parse(string $raw): array
    {
        $raw = trim($raw);

        // Strip markdown code fences if GPT wraps output
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! isset($data['questions'])) {
            Log::warning('QuizGeneratorResponseParser: invalid JSON', ['raw' => substr($raw, 0, 500)]);
            return [];
        }

        return array_map(fn($q) => $this->normaliseQuestion($q), $data['questions']);
    }

    private function normaliseQuestion(array $q): array
    {
        return [
            'type'           => $q['type'] ?? 'mcq_single',
            'content'        => $q['content'] ?? '',
            'explanation'    => $q['explanation'] ?? null,
            'marks'          => (float) ($q['marks'] ?? 1),
            'negative_marks' => (float) ($q['negative_marks'] ?? 0),
            'options'        => array_map(fn($o) => [
                'content'    => $o['content'] ?? '',
                'is_correct' => (bool) ($o['is_correct'] ?? false),
            ], $q['options'] ?? []),
            'blank_answers'  => $q['blank_answers'] ?? [],
        ];
    }
}
