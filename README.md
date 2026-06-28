# Quizora — AI-Powered Quiz & Exam Platform

**Quizora** is a self-hosted SaaS platform for creating, publishing, and selling AI-generated quizzes. Built with Laravel 13 + FilamentPHP 3, it ships with an IBPS-style exam engine, dual payment gateways (Razorpay + Stripe), and a streaming AI question generator.

---

## Key Features

- **AI quiz generation** — GPT-4o generates MCQ, true/false, fill-in-the-blank, and short-answer questions in real time via Server-Sent Events streaming
- **IBPS-style exam UI** — numbered question palette, colour-coded status (answered / skipped / marked for review), per-section timer, negative marking
- **Creator monetisation** — creators set a price, platform takes a configurable commission; payouts via bank/UPI/PayPal
- **Dual payment gateways** — Razorpay (first-class for IN/MENA/LatAm) and Stripe
- **Subscription plans** — admin defines Free / Pro / Business tiers with AI credit quotas and commission rates
- **Certificates** — auto-generated PDF with QR verification on quiz pass
- **Multi-role platform** — Admin, Creator, and Customer portals via three separate Filament panels
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
php artisan queue:work redis --queue=payments,emails,payouts,default --tries=3

# 6. Run the dev server (local only)
php artisan serve
```

Or use the **visual installer** at `/install` after copying `.env.example → .env` and setting `APP_URL`.

---

## Directory Structure

```
app/
├── Filament/Admin/              /admin panel (super_admin role)
├── Filament/Creator/            /creator panel (creator role)
├── Http/Controllers/Customer/   Customer-facing Blade + Livewire
├── Services/AI/                 GPT-4o generation + credit management
├── Services/Exam/               Attempt lifecycle, scoring, certificates
├── Services/Payment/            Razorpay, Stripe, commission split
└── Models/                      UUID-keyed Eloquent models

resources/views/
├── layouts/app.blade.php        Customer portal shell
├── layouts/exam.blade.php       Full-screen exam (no nav)
├── customer/                    Page views (home, quiz, attempt, result)
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
| `RAZORPAY_KEY` / `RAZORPAY_SECRET` | Razorpay payments |
| `STRIPE_KEY` / `STRIPE_SECRET` | Stripe payments |
| `MAIL_*` | Transactional email (SMTP) |

---

## Running Queues (Production)

```bash
# Supervisor recommended — handles all job queues
php artisan queue:work redis \
  --queue=payments,emails,payouts,default \
  --tries=3 \
  --backoff=30 \
  --sleep=3
```

---

## Scheduled Commands

Add to crontab:

```cron
* * * * * cd /path/to/quizora && php artisan schedule:run >> /dev/null 2>&1
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 / PHP 8.3 |
| Admin panels | FilamentPHP 3 |
| AI | OpenAI GPT-4o via `openai-php/laravel` |
| Payments | Razorpay, Stripe |
| Queue / Cache | Redis |
| PDF | barryvdh/laravel-dompdf |
| Permissions | spatie/laravel-permission |
| Frontend | TailwindCSS v3, Alpine.js, Livewire 3 |

---

## License

This project is open source software licensed under the [MIT License](LICENSE).
