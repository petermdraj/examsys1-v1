# ExamSys — AI-Powered Quiz & Exam Platform

**ExamSys** is a self-hosted educational quiz platform for creating, publishing, and attempting AI-generated quizzes. Built with Laravel 13 + FilamentPHP 3, it ships with an IBPS-style exam engine and a streaming AI question generator. Students are managed by administrators and assigned exams by lecturers.

---

## Key Features

- **AI quiz generation** — GPT-4o generates MCQ, true/false, fill-in-the-blank, and short-answer questions in real time via Server-Sent Events streaming
- **IBPS-style exam UI** — numbered question palette, colour-coded status (answered / skipped / marked for review), per-section timer, negative marking
- **Free access** — students enroll in any published quiz at no cost
- **Certificates** — auto-generated PDF with QR verification on quiz pass
- **Multi-role platform** — Admin (`/admin`), Lecturer (`/lecturer`), and Student portal via Filament + Blade
- **Visual installer** — six-step Livewire wizard with DB test, SMTP test, and seed
- **Full i18n** — all UI strings in `lang/` files, RTL-ready

---

## Requirements

| Component | Minimum |
|---|---|
| PHP | 8.3+ |
| Laravel | 13.x |
| MySQL | 8.0+ |
| Node.js | 20+ |
| Redis | 6+ (cache + queue) |
| OpenAI API key | Required for AI generation |

---

## Quick Start

```bash
# 1. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 2. Install and build JS assets
npm install && npm run build

# 3. Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# 4. Set your DB credentials in .env, then migrate + seed
php artisan migrate --seed

# 5. Start queues (Redis required)
php artisan queue:work redis --queue=emails,default --tries=3

# 6. Run the dev server (local only)
php artisan serve
```

Or use the **visual installer** at `/install` after copying `.env.example → .env` and setting `APP_URL`.

---

## Directory Structure

```
app/
├── Filament/Admin/              /admin panel (super_admin role)
├── Filament/Lecturer/           /lecturer panel (lecturer role)
├── Http/Controllers/Student/    Student-facing Blade + Livewire
├── Services/AI/                 GPT-4o generation + credit management
├── Services/Exam/               Attempt lifecycle, scoring, certificates
└── Models/                      UUID-keyed Eloquent models

resources/views/
├── layouts/app.blade.php        Student portal shell
├── layouts/exam.blade.php       Full-screen exam (no nav)
├── student/                    Page views (home, quiz, attempt, result)
└── livewire/                    Exam panel, search, AI generator
```

---

## Environment Variables

See `.env.example` for a fully annotated template. Required keys:

| Variable | Purpose |
|---|---|
| `DB_*` | MySQL connection |
| `REDIS_*` | Cache + queue |
| `OPENAI_API_KEY` | AI question generation |
| `MAIL_*` | Transactional email (SMTP) |

---

## Running Queues (Production)

```bash
# Supervisor recommended — handles all job queues
php artisan queue:work redis \
  --queue=emails,default \
  --tries=3 \
  --backoff=30 \
  --sleep=3
```

---

## Scheduled Commands

Add to crontab:

```cron
* * * * * cd /path/to/examsys1 && php artisan schedule:run >> /dev/null 2>&1
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 / PHP 8.3 |
| Admin panels | FilamentPHP 3 |
| AI | OpenAI GPT-4o via `openai-php/laravel` |
| Queue / Cache | Redis |
| PDF | barryvdh/laravel-dompdf |
| Permissions | spatie/laravel-permission |
| Frontend | TailwindCSS v3, Alpine.js, Livewire 3 |

---

## License

This project is open source software licensed under the [MIT License](LICENSE).
