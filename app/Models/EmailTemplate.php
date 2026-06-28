<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasUuid;

    protected $fillable = ['key', 'name', 'subject', 'body', 'variables', 'is_active'];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** Replace {{variable}} placeholders with actual values (HTML-escapes each value). */
    public function render(array $data): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', e($value), $body);
        }
        return $body;
    }

    /** Replace {{variable}} placeholders without escaping — use when values are trusted HTML. */
    public function renderRaw(array $data): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }
        return $body;
    }

    public function renderSubject(array $data): string
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        return $subject;
    }

    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }
}
