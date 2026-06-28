<?php

namespace App\Services\AI;

class QuizGeneratorPromptBuilder
{
    public function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a professional quiz question generator. Your only output is valid JSON matching the schema provided. Never include prose, markdown code fences, or any text outside the JSON object.
PROMPT;
    }

    public function buildUserPrompt(array $options): string
    {
        $count      = $options['count'] ?? 10;
        $topic      = $options['prompt'];
        $difficulty = $options['difficulty'] ?? 'medium';
        $types      = $this->resolveTypes($options['type'] ?? 'mixed');
        $optCount   = $options['options_count'] ?? 4;
        $marksPerQ  = $options['marks_per_question'] ?? 1;
        $negMarks   = $options['negative_marking'] ?? false;
        $negValue   = $negMarks ? round($marksPerQ * 0.25, 2) : 0;
        $languageCode = $options['language'] ?? 'en';
        $language     = $this->resolveLanguageName($languageCode);

        $typeInstructions = $this->typeInstructions($types, $optCount);

        return <<<PROMPT
Generate exactly {$count} quiz questions about: "{$topic}"

Requirements:
- Difficulty: {$difficulty}
- Output language: {$language} — ALL question text, option text, explanations, and blank_answers MUST be written in {$language}. Do not use English unless {$language} is English.
- Question types to include: {$types}
- {$typeInstructions}
- marks per question: {$marksPerQ}
- negative_marks per wrong answer: {$negValue}
- All questions must be factually accurate and unambiguous.

Return ONLY this JSON structure (no other text):
{
  "questions": [
    {
      "type": "mcq_single | mcq_multiple | fill_blank | true_false",
      "content": "Question text here",
      "explanation": "Why the correct answer is correct",
      "marks": {$marksPerQ},
      "negative_marks": {$negValue},
      "options": [
        {"content": "Option text", "is_correct": false}
      ],
      "blank_answers": ["answer1"]
    }
  ]
}

Notes:
- For mcq_single: exactly {$optCount} options, exactly 1 correct.
- For mcq_multiple: exactly {$optCount} options, 2+ correct.
- For true_false: options must be exactly [{"content":"True","is_correct":X},{"content":"False","is_correct":Y}].
- For fill_blank: options array is empty, blank_answers lists all accepted answers.
- blank_answers is only required for fill_blank type; omit for others.
PROMPT;
    }

    private function resolveLanguageName(string $code): string
    {
        return match ($code) {
            'hi'  => 'Hindi (हिन्दी)',
            'es'  => 'Spanish (Español)',
            'fr'  => 'French (Français)',
            'de'  => 'German (Deutsch)',
            'ar'  => 'Arabic (العربية)',
            'pt'  => 'Portuguese (Português)',
            'zh'  => 'Chinese Simplified (中文)',
            'ja'  => 'Japanese (日本語)',
            'nl'  => 'Dutch (Nederlands)',
            'sv'  => 'Swedish (Svenska)',
            'da'  => 'Danish (Dansk)',
            'no'  => 'Norwegian (Norsk)',
            default => 'English',
        };
    }

    private function resolveTypes(string $type): string
    {
        return match ($type) {
            'mcq_single'   => 'mcq_single',
            'mcq_multiple' => 'mcq_multiple',
            'fill_blank'   => 'fill_blank',
            'true_false'   => 'true_false',
            default        => 'mcq_single, true_false, fill_blank',
        };
    }

    private function typeInstructions(string $types, int $optCount): string
    {
        return "For MCQ questions use {$optCount} options.";
    }
}
