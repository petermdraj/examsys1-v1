<?php

namespace Database\Seeders;

use App\Models\AiGenerationLog;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\CreatorPayout;
use App\Models\FillBlankAnswer;
use App\Models\Order;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data…');

        // Wipe previous demo data cleanly
        $this->wipe();

        $creators  = $this->seedCreators();
        $customers = $this->seedCustomers();
        $quizzes   = $this->seedQuizzes($creators);
        $this->seedOrders($quizzes, $customers);
        $this->seedAttempts($quizzes, $customers);
        $this->seedAiLogs($creators, $quizzes);
        $this->seedPayouts($creators);
        $this->seedSubscriptions($creators);

        $this->command->newLine();
        $this->command->info('✅ Demo seed complete. Test credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',   'admin@quizora.app', 'password'],
                ['Creator', 'priya@demo.quiz',   'password'],
                ['Creator', 'rahul@demo.quiz',   'password'],
                ['Creator', 'sofia@demo.quiz',   'password'],
                ['Customer','alice@demo.quiz',   'password'],
                ['Customer','bob@demo.quiz',     'password'],
                ['Customer','carol@demo.quiz',   'password'],
            ]
        );
    }

    // ──────────────────────────────────────────
    // Wipe
    // ──────────────────────────────────────────
    private function wipe(): void
    {
        // Only wipe demo-specific emails, not the real admin
        $demoEmails = [
            'priya@demo.quiz','rahul@demo.quiz','sofia@demo.quiz',
        ];
        for ($i = 1; $i <= 10; $i++) {
            $demoEmails[] = "student{$i}@demo.quiz";
        }
        foreach (['alice@demo.quiz','bob@demo.quiz','carol@demo.quiz',
                  'dave@demo.quiz','eve@demo.quiz','frank@demo.quiz',
                  'grace@demo.quiz','henry@demo.quiz','ivy@demo.quiz','jack@demo.quiz'] as $e) {
            $demoEmails[] = $e;
        }

        $userIds = User::whereIn('email', $demoEmails)->pluck('id');
        $quizIds = Quiz::whereIn('creator_id', $userIds)->pluck('id');

        DB::table('attempt_answers')->whereIn('attempt_id',
            DB::table('attempts')->whereIn('quiz_id', $quizIds)->pluck('id')
        )->delete();
        DB::table('certificates')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('attempts')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('quiz_enrollments')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('orders')->whereIn('quiz_id', $quizIds)->delete();

        // Delete questions/options for these quizzes
        $questionIds = DB::table('questions')->whereIn('quiz_id', $quizIds)->pluck('id');
        DB::table('fill_blank_answers')->whereIn('question_id', $questionIds)->delete();
        DB::table('question_options')->whereIn('question_id', $questionIds)->delete();
        DB::table('questions')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('quizzes')->whereIn('id', $quizIds)->delete();

        DB::table('ai_generation_logs')->whereIn('user_id', $userIds)->delete();
        DB::table('creator_payouts')->whereIn('creator_id', $userIds)->delete();
        DB::table('subscriptions')->whereIn('user_id', $userIds)->delete();
        User::withTrashed()->whereIn('email', $demoEmails)->forceDelete();
    }

    // ──────────────────────────────────────────
    // Creators
    // ──────────────────────────────────────────
    private function seedCreators(): array
    {
        $data = [
            ['name'=>'Priya Sharma',    'email'=>'priya@demo.quiz',   'wallet'=>1250.00, 'credits'=>8],
            ['name'=>'Rahul Mehta',     'email'=>'rahul@demo.quiz',   'wallet'=>800.00,  'credits'=>3],
            ['name'=>'Sofia Rodriguez', 'email'=>'sofia@demo.quiz',   'wallet'=>450.00,  'credits'=>10],
        ];
        return array_map(fn($d) => User::create([
            'name'                     => $d['name'],
            'email'                    => $d['email'],
            'password'                 => bcrypt('password'),
            'role'                     => 'creator',
            'is_active'                => true,
            'ai_credits_free_remaining' => $d['credits'],
            'ai_credits_used'          => 10 - $d['credits'],
            'wallet_balance'           => $d['wallet'],
            'email_verified_at'        => now(),
        ]), $data);
    }

    // ──────────────────────────────────────────
    // Customers (named, for easy testing)
    // ──────────────────────────────────────────
    private function seedCustomers(): array
    {
        $named = [
            ['name'=>'Alice Johnson', 'email'=>'alice@demo.quiz'],
            ['name'=>'Bob Smith',     'email'=>'bob@demo.quiz'],
            ['name'=>'Carol White',   'email'=>'carol@demo.quiz'],
            ['name'=>'Dave Brown',    'email'=>'dave@demo.quiz'],
            ['name'=>'Eve Davis',     'email'=>'eve@demo.quiz'],
            ['name'=>'Frank Miller',  'email'=>'frank@demo.quiz'],
            ['name'=>'Grace Wilson',  'email'=>'grace@demo.quiz'],
            ['name'=>'Henry Taylor',  'email'=>'henry@demo.quiz'],
            ['name'=>'Ivy Anderson',  'email'=>'ivy@demo.quiz'],
            ['name'=>'Jack Thomas',   'email'=>'jack@demo.quiz'],
        ];
        $customers = [];
        foreach ($named as $d) {
            $customers[] = User::create([
                'name'                     => $d['name'],
                'email'                    => $d['email'],
                'password'                 => bcrypt('password'),
                'role'                     => 'customer',
                'is_active'                => true,
                'ai_credits_free_remaining' => 0,
                'email_verified_at'        => now(),
            ]);
        }
        // Extra anonymous students
        for ($i = 1; $i <= 10; $i++) {
            $customers[] = User::create([
                'name'                     => "Student $i",
                'email'                    => "student{$i}@demo.quiz",
                'password'                 => bcrypt('password'),
                'role'                     => 'customer',
                'is_active'                => true,
                'ai_credits_free_remaining' => 0,
                'email_verified_at'        => now(),
            ]);
        }
        return $customers;
    }

    // ──────────────────────────────────────────
    // Quizzes + Questions
    // ──────────────────────────────────────────
    private function seedQuizzes(array $creators): array
    {
        $cats = Category::pluck('id', 'slug');
        $tech  = $cats['technology']       ?? Category::first()->id;
        $sci   = $cats['science']          ?? $tech;
        $math  = $cats['mathematics']      ?? $tech;
        $eng   = $cats['english-language'] ?? $tech;
        $hist  = $cats['history']          ?? $tech;

        $quizDefs = [
            ['title'=>'PHP & Laravel Fundamentals',        'cat'=>$tech,  'creator'=>$creators[0], 'price'=>0,   'duration'=>30, 'pass'=>60, 'neg'=>false, 'cert'=>true],
            ['title'=>'JavaScript ES6+ Mastery',           'cat'=>$tech,  'creator'=>$creators[0], 'price'=>199, 'duration'=>45, 'pass'=>65, 'neg'=>true,  'cert'=>true],
            ['title'=>'Human Body Systems',                'cat'=>$sci,   'creator'=>$creators[1], 'price'=>0,   'duration'=>20, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Basic Algebra & Equations',         'cat'=>$math,  'creator'=>$creators[1], 'price'=>99,  'duration'=>30, 'pass'=>70, 'neg'=>false, 'cert'=>true],
            ['title'=>'World History: Ancient Civilizations','cat'=>$hist, 'creator'=>$creators[2], 'price'=>0,   'duration'=>25, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'English Grammar & Usage',           'cat'=>$eng,   'creator'=>$creators[2], 'price'=>149, 'duration'=>30, 'pass'=>60, 'neg'=>false, 'cert'=>true],
            ['title'=>'Data Structures & Algorithms',      'cat'=>$tech,  'creator'=>$creators[0], 'price'=>299, 'duration'=>60, 'pass'=>65, 'neg'=>true,  'cert'=>true],
            ['title'=>'General Science — Class 10',        'cat'=>$sci,   'creator'=>$creators[1], 'price'=>0,   'duration'=>20, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Aptitude: Reasoning & Puzzles',     'cat'=>$math,  'creator'=>$creators[2], 'price'=>0,   'duration'=>20, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Computer Networks Essentials',      'cat'=>$tech,  'creator'=>$creators[0], 'price'=>199, 'duration'=>40, 'pass'=>65, 'neg'=>false, 'cert'=>true],
        ];

        $quizzes = [];
        $allQuestionBanks = [
            $this->phpQs(), $this->jsQs(), $this->bodyQs(),
            $this->algebraQs(), $this->historyQs(), $this->grammarQs(),
            $this->dsaQs(), $this->scienceQs(), $this->aptitudeQs(), $this->networkQs(),
        ];

        foreach ($quizDefs as $i => $def) {
            $quiz = Quiz::create([
                'creator_id'               => $def['creator']->id,
                'category_id'              => $def['cat'],
                'title'                    => $def['title'],
                'slug'                     => Str::slug($def['title']),
                'description'              => "A comprehensive quiz covering {$def['title']}. Test your knowledge and earn a certificate!",
                'status'                   => 'published',
                'visibility'               => 'public',
                'price'                    => $def['price'],
                'currency'                 => 'INR',
                'pass_percentage'          => $def['pass'],
                'duration_minutes'         => $def['duration'],
                'max_attempts'             => null,
                'negative_marking_enabled' => $def['neg'],
                'certificate_enabled'      => $def['cert'],
                'show_result_immediately'  => true,
                'allow_review_after_submit' => true,
                'shuffle_questions'        => false,
                'shuffle_options'          => false,
                'total_questions'          => 0,
                'total_marks'              => 0,
                'total_attempts'           => 0,
                'average_score'            => 0,
            ]);

            $bank = $allQuestionBanks[$i];
            foreach ($bank as $sortOrder => $q) {
                $question = Question::create([
                    'quiz_id'        => $quiz->id,
                    'type'           => $q['type'],
                    'content'        => $q['content'],
                    'explanation'    => $q['explanation'] ?? null,
                    'marks'          => 1,
                    'negative_marks' => $def['neg'] ? 0.25 : 0,
                    'sort_order'     => $sortOrder + 1,
                ]);
                foreach ($q['options'] ?? [] as $oi => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content'     => $opt['content'],
                        'is_correct'  => $opt['is_correct'],
                        'sort_order'  => $oi + 1,
                    ]);
                }
                foreach ($q['blank_answers'] ?? [] as $ans) {
                    FillBlankAnswer::create(['question_id' => $question->id, 'answer' => $ans]);
                }
            }

            $quiz->update(['total_questions' => 10, 'total_marks' => 10]);
            $quizzes[] = $quiz;
        }

        return $quizzes;
    }

    // ──────────────────────────────────────────
    // Orders (for paid quizzes)
    // ──────────────────────────────────────────
    private function seedOrders(array $quizzes, array $customers): void
    {
        $commissionRate = 20.0;
        $paidQuizzes = array_filter($quizzes, fn($q) => $q->price > 0);

        foreach ($paidQuizzes as $quiz) {
            // Give 4–7 customers paid access
            $buyers = collect($customers)->shuffle()->take(rand(4, 7));
            foreach ($buyers as $customer) {
                $commission = round($quiz->price * $commissionRate / 100, 2);
                $earning    = round($quiz->price - $commission, 2);
                $paidAt     = now()->subDays(rand(1, 45));

                $order = Order::create([
                    'user_id'             => $customer->id,
                    'quiz_id'             => $quiz->id,
                    'amount'              => $quiz->price,
                    'currency'            => 'INR',
                    'platform_commission' => $commission,
                    'creator_earning'     => $earning,
                    'status'              => 'paid',
                    'gateway'             => collect(['razorpay','stripe'])->random(),
                    'gateway_order_id'    => 'order_' . Str::random(14),
                    'gateway_payment_id'  => 'pay_' . Str::random(14),
                    'paid_at'             => $paidAt,
                    'created_at'          => $paidAt,
                    'updated_at'          => $paidAt,
                ]);

                QuizEnrollment::create([
                    'quiz_id'     => $quiz->id,
                    'user_id'     => $customer->id,
                    'enrolled_at' => $paidAt,
                    'source'      => 'purchased',
                    'order_id'    => $order->id,
                ]);
            }
        }
    }

    // ──────────────────────────────────────────
    // Attempts + Answers + Certificates
    // ──────────────────────────────────────────
    private function seedAttempts(array $quizzes, array $customers): void
    {
        foreach ($quizzes as $quiz) {
            $questions = $quiz->questions()->with('options', 'fillBlankAnswers')->get();

            // Determine who can attempt
            if ($quiz->isFree()) {
                $participants = collect($customers)->shuffle()->take(rand(10, 18));
            } else {
                // Only enrolled customers
                $enrolledIds  = QuizEnrollment::where('quiz_id', $quiz->id)->pluck('user_id');
                $participants = User::whereIn('id', $enrolledIds)->get();
            }

            // Also enroll free quiz participants
            if ($quiz->isFree()) {
                foreach ($participants as $customer) {
                    QuizEnrollment::firstOrCreate(
                        ['quiz_id' => $quiz->id, 'user_id' => $customer->id],
                        ['enrolled_at' => now()->subDays(rand(1, 60)), 'source' => 'free']
                    );
                }
            }

            $totalPct = 0;
            $count    = 0;

            foreach ($participants as $customer) {
                // Random score 30–100%
                $targetPct    = rand(30, 100);
                $targetCorrect = (int) round($questions->count() * $targetPct / 100);

                $startedAt   = now()->subDays(rand(0, 30))->subMinutes(rand(5, 55));
                $timeTaken   = rand(300, $quiz->duration_minutes * 60 - 60);
                $submittedAt = $startedAt->copy()->addSeconds($timeTaken);

                $enrollment = QuizEnrollment::where('quiz_id', $quiz->id)->where('user_id', $customer->id)->first();

                $attempt = Attempt::create([
                    'quiz_id'            => $quiz->id,
                    'user_id'            => $customer->id,
                    'enrollment_id'      => $enrollment->id,
                    'attempt_number'     => 1,
                    'status'             => 'completed',
                    'started_at'         => $startedAt,
                    'submitted_at'       => $submittedAt,
                    'time_taken_seconds' => $timeTaken,
                    'score'              => null, // set after answers
                    'total_marks'        => $questions->count(),
                    'percentage'         => null,
                    'is_passed'          => null,
                    'ip_address'         => fake()->ipv4(),
                    'user_agent'         => 'Mozilla/5.0 (demo)',
                    'created_at'         => $startedAt,
                    'updated_at'         => $submittedAt,
                ]);

                // Create answer records
                $score = 0;
                $shuffled = $questions->shuffle();
                foreach ($shuffled as $qi => $question) {
                    $isCorrect = $qi < $targetCorrect; // first N are correct
                    $answer    = $this->makeAnswer($question, $isCorrect);

                    AttemptAnswer::create([
                        'attempt_id'          => $attempt->id,
                        'question_id'         => $question->id,
                        'selected_options'    => $answer['selected_options'],
                        'text_answer'         => $answer['text_answer'],
                        'is_correct'          => $isCorrect,
                        'marks_earned'        => $isCorrect ? 1 : ($quiz->negative_marking_enabled && $answer['selected_options'] ? -0.25 : 0),
                        'is_marked_for_review' => rand(0, 5) === 0,
                        'time_spent_seconds'  => rand(10, 90),
                        'answered_at'         => $submittedAt,
                        'created_at'          => $submittedAt,
                        'updated_at'          => $submittedAt,
                    ]);

                    if ($isCorrect) {
                        $score += 1;
                    } elseif ($quiz->negative_marking_enabled && $answer['selected_options']) {
                        $score -= 0.25;
                    }
                }

                $score      = max(0, $score);
                $percentage = round($score / $questions->count() * 100, 2);
                $isPassed   = $percentage >= $quiz->pass_percentage;

                $attempt->update([
                    'score'      => $score,
                    'percentage' => $percentage,
                    'is_passed'  => $isPassed,
                ]);

                // Issue certificate if passed + enabled
                if ($isPassed && $quiz->certificate_enabled) {
                    Certificate::create([
                        'attempt_id' => $attempt->id,
                        'user_id'    => $customer->id,
                        'quiz_id'    => $quiz->id,
                        'uuid'       => (string) Str::uuid(),
                        'issued_at'  => $submittedAt,
                        'pdf_path'   => null,
                        'created_at' => $submittedAt,
                    ]);
                }

                $totalPct += $percentage;
                $count++;
            }

            // Update quiz stats
            $quiz->update([
                'total_attempts' => $count,
                'average_score'  => $count > 0 ? round($totalPct / $count, 2) : 0,
            ]);
        }
    }

    // Build realistic answer payload for a question
    private function makeAnswer(Question $question, bool $correct): array
    {
        $selected = null;
        $text     = null;

        if ($question->type === 'fill_blank') {
            if ($correct) {
                $ans  = $question->fillBlankAnswers->first();
                $text = $ans?->answer ?? 'test';
            } else {
                $text = 'wrong answer ' . rand(1, 99);
            }
        } elseif ($question->type === 'short_answer') {
            $text = $correct ? 'correct answer' : 'wrong answer';
        } else {
            $options = $question->options;
            if ($correct) {
                $correctOpts = $options->where('is_correct', true);
                if ($question->type === 'mcq_multiple') {
                    $selected = $correctOpts->pluck('id')->toArray();
                } else {
                    $selected = [$correctOpts->first()?->id];
                }
            } else {
                $wrongOpts = $options->where('is_correct', false);
                $selected  = $wrongOpts->isNotEmpty()
                    ? [$wrongOpts->random()->id]
                    : [$options->first()?->id];
            }
            $selected = array_filter($selected); // remove nulls
        }

        return ['selected_options' => $selected ?: null, 'text_answer' => $text];
    }

    // ──────────────────────────────────────────
    // AI Generation Logs
    // ──────────────────────────────────────────
    private function seedAiLogs(array $creators, array $quizzes): void
    {
        $prompts = [
            'PHP OOP concepts for intermediate developers',
            'JavaScript async/await and Promises',
            'Human body circulatory system',
            'Linear equations and graph plotting',
            'Ancient Greek philosophers',
        ];

        foreach ($creators as $ci => $creator) {
            for ($j = 0; $j < 4; $j++) {
                $quiz = $quizzes[($ci * 3 + $j) % count($quizzes)];
                AiGenerationLog::create([
                    'user_id'             => $creator->id,
                    'quiz_id'             => $quiz->id,
                    'prompt'              => $prompts[($ci + $j) % count($prompts)],
                    'options'             => ['count' => 10, 'type' => 'mixed', 'difficulty' => 'medium'],
                    'questions_generated' => 10,
                    'tokens_used'         => rand(800, 2000),
                    'charge_applied'      => 0,
                    'was_free'            => true,
                    'model'               => 'gpt-4o',
                    'status'              => rand(0, 9) > 0 ? 'success' : 'failed',
                    'error_message'       => null,
                    'created_at'          => now()->subDays(rand(0, 20)),
                ]);
            }
        }
    }

    // ──────────────────────────────────────────
    // Creator Payouts
    // ──────────────────────────────────────────
    private function seedPayouts(array $creators): void
    {
        $payouts = [
            ['creator' => $creators[0], 'amount' => 500,  'status' => 'paid',    'days' => 30],
            ['creator' => $creators[0], 'amount' => 750,  'status' => 'pending', 'days' => 3],
            ['creator' => $creators[1], 'amount' => 300,  'status' => 'paid',    'days' => 20],
            ['creator' => $creators[2], 'amount' => 200,  'status' => 'pending', 'days' => 5],
        ];

        foreach ($payouts as $p) {
            CreatorPayout::create([
                'creator_id'   => $p['creator']->id,
                'amount'       => $p['amount'],
                'status'       => $p['status'],
                'gateway'      => 'UPI: ' . $p['creator']->email,
                'note'         => 'Monthly payout request',
                'requested_at' => now()->subDays($p['days']),
                'processed_at' => $p['status'] === 'paid' ? now()->subDays($p['days'] - 2) : null,
            ]);
        }
    }

    // ──────────────────────────────────────────
    // Subscriptions
    // ──────────────────────────────────────────
    private function seedSubscriptions(array $creators): void
    {
        $proPlan      = Plan::where('slug', 'pro')->first();
        $businessPlan = Plan::where('slug', 'business')->first();

        if ($proPlan) {
            Subscription::create([
                'user_id'              => $creators[0]->id,
                'plan_id'              => $proPlan->id,
                'status'               => 'active',
                'billing_cycle'        => 'monthly',
                'current_period_start' => now()->startOfMonth(),
                'current_period_end'   => now()->endOfMonth(),
                'gateway'              => 'stripe',
                'gateway_subscription_id' => 'sub_' . Str::random(14),
            ]);
        }

        if ($businessPlan) {
            Subscription::create([
                'user_id'              => $creators[1]->id,
                'plan_id'              => $businessPlan->id,
                'status'               => 'active',
                'billing_cycle'        => 'yearly',
                'current_period_start' => now()->subMonths(2),
                'current_period_end'   => now()->addMonths(10),
                'gateway'              => 'razorpay',
                'gateway_subscription_id' => 'sub_' . Str::random(14),
            ]);
        }
    }

    // ──────────────────────────────────────────
    // Question banks (10 Qs each quiz)
    // ──────────────────────────────────────────
    private function phpQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which keyword prevents class inheritance in PHP?','explanation'=>'The `final` keyword prevents a class from being extended.','options'=>[['content'=>'abstract','is_correct'=>false],['content'=>'interface','is_correct'=>false],['content'=>'final','is_correct'=>true],['content'=>'static','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What does PSR stand for?','options'=>[['content'=>'PHP Standard Routine','is_correct'=>false],['content'=>'PHP Standards Recommendation','is_correct'=>true],['content'=>'PHP Script Runtime','is_correct'=>false],['content'=>'PHP System Reference','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'A PHP trait can implement an interface.','explanation'=>'Traits cannot implement interfaces.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'Which Laravel helper returns a URL for a named route?','options'=>[['content'=>'url()','is_correct'=>false],['content'=>'route()','is_correct'=>true],['content'=>'path()','is_correct'=>false],['content'=>'link()','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What is the default queue driver in a fresh Laravel install?','options'=>[['content'=>'redis','is_correct'=>false],['content'=>'beanstalkd','is_correct'=>false],['content'=>'sync','is_correct'=>true],['content'=>'database','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The PHP function to check if a variable is set and not null is ______.','blank_answers'=>['isset']],
        ['type'=>'mcq_single','content'=>'Which Artisan command creates a new Eloquent model?','options'=>[['content'=>'php artisan create:model','is_correct'=>false],['content'=>'php artisan make:model','is_correct'=>true],['content'=>'php artisan generate:model','is_correct'=>false],['content'=>'php artisan new:model','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Laravel Eloquent uses the Active Record pattern.','explanation'=>'Eloquent implements the Active Record ORM pattern.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which method is used to eager load relationships in Eloquent?','options'=>[['content'=>'load()','is_correct'=>false],['content'=>'with()','is_correct'=>true],['content'=>'join()','is_correct'=>false],['content'=>'attach()','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What does `->nullable()` do in a Laravel migration?','options'=>[['content'=>'Sets default to null','is_correct'=>false],['content'=>'Allows the column to store NULL','is_correct'=>true],['content'=>'Skips the column if empty','is_correct'=>false],['content'=>'Removes a column','is_correct'=>false]]],
    ]; }

    private function jsQs(): array { return [
        ['type'=>'mcq_single','content'=>'What does the spread operator (...) do in JavaScript?','options'=>[['content'=>'Multiplies array elements','is_correct'=>false],['content'=>'Expands iterable into individual elements','is_correct'=>true],['content'=>'Creates a deep copy','is_correct'=>false],['content'=>'Concatenates strings','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'`const` creates an immutable binding, not an immutable value.','explanation'=>'const prevents reassignment but the object can still be mutated.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which method returns a new array with elements passing a test?','options'=>[['content'=>'map()','is_correct'=>false],['content'=>'filter()','is_correct'=>true],['content'=>'find()','is_correct'=>false],['content'=>'some()','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The keyword to pause execution inside an async function is ______.','blank_answers'=>['await']],
        ['type'=>'mcq_single','content'=>'What is the output of `typeof null`?','options'=>[['content'=>'"null"','is_correct'=>false],['content'=>'"undefined"','is_correct'=>false],['content'=>'"object"','is_correct'=>true],['content'=>'"boolean"','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which ES6 feature allows extracting properties from objects?','options'=>[['content'=>'Template literals','is_correct'=>false],['content'=>'Arrow functions','is_correct'=>false],['content'=>'Destructuring assignment','is_correct'=>true],['content'=>'Generators','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Promises in JavaScript are always resolved synchronously.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'What does `Array.from({length:3},(_,i)=>i)` return?','options'=>[['content'=>'[1,2,3]','is_correct'=>false],['content'=>'[0,1,2]','is_correct'=>true],['content'=>'[undefined×3]','is_correct'=>false],['content'=>'Error','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which creates a shallow copy of an array?','options'=>[['content'=>'Array.clone()','is_correct'=>false],['content'=>'[...arr]','is_correct'=>true],['content'=>'JSON.parse(JSON.stringify(arr))','is_correct'=>false],['content'=>'arr.copy()','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What is a closure in JavaScript?','options'=>[['content'=>'A function with no parameters','is_correct'=>false],['content'=>'A function retaining access to its outer scope','is_correct'=>true],['content'=>'An IIFE','is_correct'=>false],['content'=>'A recursive function','is_correct'=>false]]],
    ]; }

    private function bodyQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which organ pumps blood throughout the body?','options'=>[['content'=>'Liver','is_correct'=>false],['content'=>'Heart','is_correct'=>true],['content'=>'Kidney','is_correct'=>false],['content'=>'Lungs','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'The human body has 206 bones in adulthood.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which blood cells carry oxygen?','options'=>[['content'=>'White blood cells','is_correct'=>false],['content'=>'Platelets','is_correct'=>false],['content'=>'Red blood cells','is_correct'=>true],['content'=>'Plasma','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The largest organ of the human body is the ______.','blank_answers'=>['skin']],
        ['type'=>'mcq_single','content'=>'Which part of the brain controls balance and coordination?','options'=>[['content'=>'Cerebrum','is_correct'=>false],['content'=>'Medulla','is_correct'=>false],['content'=>'Cerebellum','is_correct'=>true],['content'=>'Hypothalamus','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Where does protein digestion primarily begin?','options'=>[['content'=>'Mouth','is_correct'=>false],['content'=>'Stomach','is_correct'=>true],['content'=>'Small intestine','is_correct'=>false],['content'=>'Large intestine','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Insulin is produced by the liver.','explanation'=>'Insulin is produced by the pancreas.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'How many chambers does the human heart have?','options'=>[['content'=>'2','is_correct'=>false],['content'=>'3','is_correct'=>false],['content'=>'4','is_correct'=>true],['content'=>'6','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which vitamin is produced when skin is exposed to sunlight?','options'=>[['content'=>'Vitamin A','is_correct'=>false],['content'=>'Vitamin C','is_correct'=>false],['content'=>'Vitamin D','is_correct'=>true],['content'=>'Vitamin K','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The basic unit of the nervous system is called a ______.','blank_answers'=>['neuron','nerve cell']],
    ]; }

    private function algebraQs(): array { return [
        ['type'=>'mcq_single','content'=>'Solve for x: 2x + 5 = 13','options'=>[['content'=>'3','is_correct'=>false],['content'=>'4','is_correct'=>true],['content'=>'5','is_correct'=>false],['content'=>'6','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Every quadratic equation has two distinct real roots.','explanation'=>'It can have 0, 1, or 2 real roots.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'What is the slope of y = 3x − 7?','options'=>[['content'=>'−7','is_correct'=>false],['content'=>'3','is_correct'=>true],['content'=>'7','is_correct'=>false],['content'=>'1/3','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The positive square root of 25 is ______.','blank_answers'=>['5']],
        ['type'=>'mcq_single','content'=>'Which is a linear equation?','options'=>[['content'=>'y = x²','is_correct'=>false],['content'=>'y = 2x+1','is_correct'=>true],['content'=>'y = x³','is_correct'=>false],['content'=>'y = 1/x','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What is 3x when x = 4?','options'=>[['content'=>'7','is_correct'=>false],['content'=>'12','is_correct'=>true],['content'=>'9','is_correct'=>false],['content'=>'1','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'The equation 0x = 5 has no solution.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which property allows a(b+c) = ab+ac?','options'=>[['content'=>'Commutative','is_correct'=>false],['content'=>'Associative','is_correct'=>false],['content'=>'Distributive','is_correct'=>true],['content'=>'Identity','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'If 4x − 8 = 0, what is x?','options'=>[['content'=>'1','is_correct'=>false],['content'=>'2','is_correct'=>true],['content'=>'4','is_correct'=>false],['content'=>'8','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The y-intercept of y = 5x + 3 is ______.','blank_answers'=>['3']],
    ]; }

    private function historyQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which river was central to Ancient Egyptian civilization?','options'=>[['content'=>'Tigris','is_correct'=>false],['content'=>'Nile','is_correct'=>true],['content'=>'Euphrates','is_correct'=>false],['content'=>'Indus','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'The Roman Empire fell in 476 CE.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Who initiated construction of the Great Wall of China?','options'=>[['content'=>'Emperor Qin Shi Huang','is_correct'=>true],['content'=>'Genghis Khan','is_correct'=>false],['content'=>'Kublai Khan','is_correct'=>false],['content'=>'Emperor Wu','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The city-state of Athens is located in ______.','blank_answers'=>['Greece','greece']],
        ['type'=>'mcq_single','content'=>'Mesopotamia was located between which two rivers?','options'=>[['content'=>'Nile and Ganges','is_correct'=>false],['content'=>'Tigris and Euphrates','is_correct'=>true],['content'=>'Amazon and Mississippi','is_correct'=>false],['content'=>'Rhine and Danube','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What writing system did ancient Mesopotamians develop?','options'=>[['content'=>'Hieroglyphics','is_correct'=>false],['content'=>'Cuneiform','is_correct'=>true],['content'=>'Latin script','is_correct'=>false],['content'=>'Sanskrit','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Alexander the Great was born in Macedonia.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which wonder was located in Alexandria, Egypt?','options'=>[['content'=>'Colossus of Rhodes','is_correct'=>false],['content'=>'Hanging Gardens','is_correct'=>false],['content'=>'Lighthouse of Alexandria','is_correct'=>true],['content'=>'Temple of Artemis','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Julius Caesar was assassinated in which year BCE?','options'=>[['content'=>'55 BCE','is_correct'=>false],['content'=>'44 BCE','is_correct'=>true],['content'=>'27 BCE','is_correct'=>false],['content'=>'100 BCE','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Alexander the Great\'s teacher was ______.','blank_answers'=>['Aristotle','aristotle']],
    ]; }

    private function grammarQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which article precedes "hour"?','options'=>[['content'=>'a','is_correct'=>false],['content'=>'an','is_correct'=>true],['content'=>'the','is_correct'=>false],['content'=>'no article','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'"Data" is the plural of "datum".','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Choose the correct sentence:','options'=>[['content'=>'She don\'t like coffee.','is_correct'=>false],['content'=>'She doesn\'t likes coffee.','is_correct'=>false],['content'=>'She doesn\'t like coffee.','is_correct'=>true],['content'=>'She not like coffee.','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The past tense of "go" is ______.','blank_answers'=>['went']],
        ['type'=>'mcq_single','content'=>'Which sentence uses passive voice?','options'=>[['content'=>'The dog chased the cat.','is_correct'=>false],['content'=>'The cat was chased by the dog.','is_correct'=>true],['content'=>'The cat ran away.','is_correct'=>false],['content'=>'Dogs chase cats.','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Superlative form of "good"?','options'=>[['content'=>'Gooder','is_correct'=>false],['content'=>'Better','is_correct'=>false],['content'=>'Best','is_correct'=>true],['content'=>'Goodest','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'A conjunction joins words, phrases, or clauses.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which word is a preposition in "The book is on the table"?','options'=>[['content'=>'book','is_correct'=>false],['content'=>'is','is_correct'=>false],['content'=>'on','is_correct'=>true],['content'=>'table','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The opposite of "ancient" is ______.','blank_answers'=>['modern','new','contemporary']],
        ['type'=>'mcq_single','content'=>'Which is grammatically correct?','options'=>[['content'=>'Him and me went.','is_correct'=>false],['content'=>'He and I went.','is_correct'=>true],['content'=>'He and me went.','is_correct'=>false],['content'=>'Him and I went.','is_correct'=>false]]],
    ]; }

    private function dsaQs(): array { return [
        ['type'=>'mcq_single','content'=>'Time complexity of binary search?','options'=>[['content'=>'O(n)','is_correct'=>false],['content'=>'O(log n)','is_correct'=>true],['content'=>'O(n²)','is_correct'=>false],['content'=>'O(1)','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'A stack follows FIFO order.','explanation'=>'Stack is LIFO. Queue is FIFO.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'Which uses nodes with pointers to the next node?','options'=>[['content'=>'Array','is_correct'=>false],['content'=>'Linked List','is_correct'=>true],['content'=>'Hash Map','is_correct'=>false],['content'=>'Stack','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The divide-and-conquer sorting algorithm with O(n log n) average is ______.','blank_answers'=>['merge sort','mergesort']],
        ['type'=>'mcq_single','content'=>'A complete binary tree is:','options'=>[['content'=>'Every node has exactly 2 children','is_correct'=>false],['content'=>'All levels full except possibly last, filled left to right','is_correct'=>true],['content'=>'Left and right subtrees equal height','is_correct'=>false],['content'=>'No duplicate values','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Best algorithm for shortest path in an unweighted graph?','options'=>[['content'=>'DFS','is_correct'=>false],['content'=>'BFS','is_correct'=>true],['content'=>'Dijkstra','is_correct'=>false],['content'=>'Bellman-Ford','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Hash tables provide O(1) average-case lookup.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Space complexity of naive recursive Fibonacci?','options'=>[['content'=>'O(1)','is_correct'=>false],['content'=>'O(n)','is_correct'=>true],['content'=>'O(n²)','is_correct'=>false],['content'=>'O(log n)','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which traversal visits root → left → right?','options'=>[['content'=>'In-order','is_correct'=>false],['content'=>'Post-order','is_correct'=>false],['content'=>'Pre-order','is_correct'=>true],['content'=>'Level-order','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The data structure used to implement recursion internally is a ______.','blank_answers'=>['stack','call stack']],
    ]; }

    private function scienceQs(): array { return [
        ['type'=>'mcq_single','content'=>'Most abundant gas in Earth\'s atmosphere?','options'=>[['content'=>'Oxygen','is_correct'=>false],['content'=>'Carbon dioxide','is_correct'=>false],['content'=>'Nitrogen','is_correct'=>true],['content'=>'Argon','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Sound travels faster in water than in air.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Chemical symbol for Gold?','options'=>[['content'=>'Go','is_correct'=>false],['content'=>'Gd','is_correct'=>false],['content'=>'Au','is_correct'=>true],['content'=>'Ag','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Plants make food using sunlight through ______.','blank_answers'=>['photosynthesis']],
        ['type'=>'mcq_single','content'=>'Which law states F = ma?','options'=>[['content'=>'Newton\'s First Law','is_correct'=>false],['content'=>'Newton\'s Second Law','is_correct'=>true],['content'=>'Newton\'s Third Law','is_correct'=>false],['content'=>'Ohm\'s Law','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Unit of electric current?','options'=>[['content'=>'Volt','is_correct'=>false],['content'=>'Watt','is_correct'=>false],['content'=>'Ampere','is_correct'=>true],['content'=>'Ohm','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Acids have a pH value greater than 7.','explanation'=>'Acids: pH < 7. Bases: pH > 7.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'The Red Planet is?','options'=>[['content'=>'Venus','is_correct'=>false],['content'=>'Jupiter','is_correct'=>false],['content'=>'Mars','is_correct'=>true],['content'=>'Saturn','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The smallest unit of matter is an ______.','blank_answers'=>['atom']],
        ['type'=>'mcq_single','content'=>'Rock formed from cooling lava?','options'=>[['content'=>'Sedimentary','is_correct'=>false],['content'=>'Metamorphic','is_correct'=>false],['content'=>'Igneous','is_correct'=>true],['content'=>'Fossil rock','is_correct'=>false]]],
    ]; }

    private function aptitudeQs(): array { return [
        ['type'=>'mcq_single','content'=>'Next number: 2, 4, 8, 16, __','options'=>[['content'=>'24','is_correct'=>false],['content'=>'32','is_correct'=>true],['content'=>'30','is_correct'=>false],['content'=>'20','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'If A > B and B > C, then A > C.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Train at 60 km/h — distance in 2.5 hours?','options'=>[['content'=>'100 km','is_correct'=>false],['content'=>'120 km','is_correct'=>false],['content'=>'150 km','is_correct'=>true],['content'=>'130 km','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The even prime number is ______.','blank_answers'=>['2']],
        ['type'=>'mcq_single','content'=>'Which shape has no corners?','options'=>[['content'=>'Triangle','is_correct'=>false],['content'=>'Square','is_correct'=>false],['content'=>'Circle','is_correct'=>true],['content'=>'Rectangle','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'If today is Monday, what day is it after 100 days?','options'=>[['content'=>'Saturday','is_correct'=>false],['content'=>'Sunday','is_correct'=>false],['content'=>'Wednesday','is_correct'=>true],['content'=>'Thursday','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Sum of angles in any triangle is 180°.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What comes next: Z, Y, X, W, __','options'=>[['content'=>'U','is_correct'=>false],['content'=>'V','is_correct'=>true],['content'=>'A','is_correct'=>false],['content'=>'T','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Clock shows 3:00 — angle between hands?','options'=>[['content'=>'30°','is_correct'=>false],['content'=>'60°','is_correct'=>false],['content'=>'90°','is_correct'=>true],['content'=>'180°','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Number of seconds in one minute is ______.','blank_answers'=>['60']],
    ]; }

    private function networkQs(): array { return [
        ['type'=>'mcq_single','content'=>'How many layers does the OSI model have?','options'=>[['content'=>'5','is_correct'=>false],['content'=>'6','is_correct'=>false],['content'=>'7','is_correct'=>true],['content'=>'4','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'UDP is a connection-oriented protocol.','explanation'=>'UDP is connectionless; TCP is connection-oriented.','options'=>[['content'=>'True','is_correct'=>false],['content'=>'False','is_correct'=>true]]],
        ['type'=>'mcq_single','content'=>'Which protocol resolves domain names to IPs?','options'=>[['content'=>'DHCP','is_correct'=>false],['content'=>'FTP','is_correct'=>false],['content'=>'DNS','is_correct'=>true],['content'=>'ARP','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Default port for HTTPS is ______.','blank_answers'=>['443']],
        ['type'=>'mcq_single','content'=>'Which OSI layer handles MAC addresses?','options'=>[['content'=>'Network layer','is_correct'=>false],['content'=>'Data Link layer','is_correct'=>true],['content'=>'Transport layer','is_correct'=>false],['content'=>'Physical layer','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'What does IP stand for in TCP/IP?','options'=>[['content'=>'Internet Provider','is_correct'=>false],['content'=>'Internet Protocol','is_correct'=>true],['content'=>'Internal Process','is_correct'=>false],['content'=>'Intranet Protocol','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'255.255.255.0 subnet mask equals /24 CIDR.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'HTTP method to create a resource?','options'=>[['content'=>'GET','is_correct'=>false],['content'=>'DELETE','is_correct'=>false],['content'=>'POST','is_correct'=>true],['content'=>'HEAD','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Primary purpose of a firewall?','options'=>[['content'=>'Speeds up internet','is_correct'=>false],['content'=>'Monitors and controls network traffic','is_correct'=>true],['content'=>'Assigns IP addresses','is_correct'=>false],['content'=>'Stores web pages locally','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The loopback IP address is ______.','blank_answers'=>['127.0.0.1']],
    ]; }
}
