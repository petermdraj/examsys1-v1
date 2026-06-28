<?php

namespace Database\Seeders\Demo;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompetitionExamSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ────────────────────────────────────────────────────
        $cats = [];
        foreach ([
            ['name' => 'Standardized Tests',     'icon' => '📝', 'color' => '#4f46e5'],
            ['name' => 'Academic Aptitude',       'icon' => '🎓', 'color' => '#0891b2'],
            ['name' => 'Language Proficiency',    'icon' => '🌐', 'color' => '#16a34a'],
            ['name' => 'Civil Service & Law',     'icon' => '⚖️', 'color' => '#b45309'],
        ] as $c) {
            $slug = Str::slug($c['name']) . '-comp';
            $cats[$c['name']] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $c['name'], 'icon' => $c['icon'], 'color' => $c['color'], 'is_active' => true, 'sort_order' => 1]
            );
        }

        // Sub-categories
        $subs = [];
        $subDefs = [
            'Standardized Tests'  => ['SAT Prep', 'GRE Verbal & Quant', 'GMAT', 'ACT Science'],
            'Academic Aptitude'   => ['Logical Reasoning', 'Quantitative Aptitude', 'Data Interpretation', 'Critical Thinking'],
            'Language Proficiency'=> ['IELTS Academic', 'TOEFL iBT', 'English Grammar & Vocabulary', 'Reading Comprehension'],
            'Civil Service & Law' => ['US Civil Service', 'UK Civil Service', 'LSAT Logic Games', 'Constitutional Law Basics'],
        ];
        foreach ($subDefs as $parentName => $children) {
            foreach ($children as $child) {
                $slug = Str::slug($child) . '-comp';
                $subs[$child] = Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $child, 'parent_id' => $cats[$parentName]->id, 'is_active' => true, 'sort_order' => 1]
                );
            }
        }

        // ── Plans ────────────────────────────────────────────────────────
        $freePlan = \App\Models\Plan::where('slug', 'free')->first();
        $proPlan  = \App\Models\Plan::where('slug', 'pro')->first();

        // ── Creators ─────────────────────────────────────────────────────
        $creator1 = User::firstOrCreate(
            ['email' => 'emma.wilson@quizora.demo'],
            ['name' => 'Emma Wilson', 'password' => Hash::make('password'), 'role' => 'creator',
             'is_active' => true, 'ai_credits_free_remaining' => $freePlan?->ai_free_generations ?? 10, 'email_verified_at' => now()]
        );
        $creator1->assignRole('creator');
        if ($freePlan) {
            \App\Models\Subscription::updateOrCreate(['user_id' => $creator1->id], [
                'plan_id' => $freePlan->id, 'status' => 'active', 'billing_cycle' => 'yearly',
                'current_period_start' => now(), 'current_period_end' => now()->addYear(), 'gateway' => 'manual',
            ]);
        }

        $creator2 = User::firstOrCreate(
            ['email' => 'carlos.mendoza@quizora.demo'],
            ['name' => 'Carlos Mendoza', 'password' => Hash::make('password'), 'role' => 'creator',
             'is_active' => true, 'ai_credits_free_remaining' => $proPlan?->ai_free_generations ?? 100, 'email_verified_at' => now()]
        );
        $creator2->assignRole('creator');
        if ($proPlan) {
            \App\Models\Subscription::updateOrCreate(['user_id' => $creator2->id], [
                'plan_id' => $proPlan->id, 'status' => 'active', 'billing_cycle' => 'yearly',
                'current_period_start' => now(), 'current_period_end' => now()->addYear(), 'gateway' => 'manual',
            ]);
        }

        // ── Customers ────────────────────────────────────────────────────
        foreach ([
            ['Aisha Johnson',   'aisha@demo.com'],
            ['Liam Nguyen',     'liam@demo.com'],
            ['Sofia Andersson', 'sofia.a@demo.com'],
            ['Marcus Brown',    'marcus@demo.com'],
            ['Yuki Tanaka',     'yuki@demo.com'],
        ] as [$name, $email]) {
            $u = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'),
                 'role' => 'customer', 'is_active' => true, 'email_verified_at' => now()]
            );
            $u->assignRole('customer');
        }

        // ── Quizzes ──────────────────────────────────────────────────────
        $this->createQuiz($creator1, $subs['SAT Prep'], 'SAT Math — Algebra & Problem Solving',
            'Practice SAT-style math questions covering linear equations, systems of equations, ratios, percentages, and word problems. Ideal for high school students targeting a top SAT score.',
            $this->satMathQuestions(), 7.99);

        $this->createQuiz($creator1, $subs['GRE Verbal & Quant'], 'GRE Verbal Reasoning Practice',
            'High-difficulty GRE verbal questions: text completion, sentence equivalence, and reading comprehension — modelled on the ETS format for graduate school applicants.',
            $this->greVerbalQuestions(), 9.99);

        $this->createQuiz($creator2, $subs['GMAT'], 'GMAT Critical Reasoning & Data Sufficiency',
            'Targeted GMAT practice covering Critical Reasoning argument analysis and Data Sufficiency — the two most challenging GMAT question types for business school applicants.',
            $this->gmatQuestions(), 12.99);

        $this->createQuiz($creator2, $subs['Logical Reasoning'], 'Logical Reasoning — Patterns & Deduction',
            'Universal aptitude test preparation covering syllogisms, number series, coding-decoding, seating arrangements, and direction sense. Suitable for any competitive exam worldwide.',
            $this->logicalReasoningQuestions(), 0);

        $this->createQuiz($creator1, $subs['IELTS Academic'], 'IELTS Academic — Reading & Grammar',
            'Practice IELTS Academic reading passages and grammar questions. Covers inference, matching headings, sentence completion, and core grammar rules tested in IELTS.',
            $this->ieltsQuestions(), 7.99);

        $this->createQuiz($creator2, $subs['Quantitative Aptitude'], 'Quantitative Aptitude — Speed, Work & Profit',
            'International aptitude quiz covering speed-distance-time, work and wages, profit and loss, simple and compound interest. Used for placement tests and competitive exams globally.',
            $this->quantAptitudeQuestions(), 5.99);

        // ── Theme: Quizora (Default) — purple + gold ─────────────────────
        $settings = app(\App\Settings\PlatformSettings::class);
        $settings->primary_color   = '#6C2E63';
        $settings->accent_color    = '#E0A431';
        $settings->font_display    = 'Plus Jakarta Sans';
        $settings->font_primary    = 'Inter';
        $settings->font_size_base  = '16px';
        $settings->save();
    }

    private function createQuiz(User $creator, Category $category, string $title, string $desc, array $questions, float $price = 0): void
    {
        $quiz = Quiz::create([
            'creator_id'              => $creator->id,
            'category_id'             => $category->id,
            'title'                   => $title,
            'slug'                    => Str::slug($title) . '-' . Str::random(4),
            'description'             => $desc,
            'status'                  => 'published',
            'visibility'              => 'public',
            'price'                   => $price,
            'currency'                => 'USD',
            'duration_minutes'        => 30,
            'max_attempts'            => null,
            'pass_percentage'         => 60,
            'shuffle_questions'       => true,
            'shuffle_options'         => true,
            'show_result_immediately' => true,
            'negative_marking_enabled'=> true,
            'total_questions'         => count($questions),
            'total_marks'             => count($questions) * 2,
        ]);

        foreach ($questions as $i => $q) {
            $question = Question::create([
                'quiz_id'       => $quiz->id,
                'creator_id'    => $creator->id,
                'type'          => 'mcq_single',
                'content'       => $q['q'],
                'explanation'   => $q['exp'] ?? null,
                'marks'         => 2,
                'negative_marks'=> 0.5,
                'sort_order'    => $i + 1,
            ]);

            foreach ($q['options'] as $j => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content'     => $opt['text'],
                    'is_correct'  => $opt['correct'],
                    'sort_order'  => $j + 1,
                ]);
            }
        }
    }

    private function satMathQuestions(): array
    {
        return [
            ['q' => 'If 3x + 7 = 22, what is the value of x?',
             'exp' => '3x = 22 - 7 = 15, so x = 5.',
             'options' => [['text'=>'3','correct'=>false],['text'=>'4','correct'=>false],['text'=>'5','correct'=>true],['text'=>'6','correct'=>false]]],
            ['q' => 'A store sells a jacket for $80 after applying a 20% discount. What was the original price?',
             'exp' => 'If $80 is 80% of original price, original = $80 / 0.8 = $100.',
             'options' => [['text'=>'$90','correct'=>false],['text'=>'$96','correct'=>false],['text'=>'$100','correct'=>true],['text'=>'$120','correct'=>false]]],
            ['q' => 'The sum of three consecutive integers is 54. What is the largest integer?',
             'exp' => 'Let integers be n, n+1, n+2. 3n+3=54 → n=17. Largest = 19.',
             'options' => [['text'=>'17','correct'=>false],['text'=>'18','correct'=>false],['text'=>'19','correct'=>true],['text'=>'20','correct'=>false]]],
            ['q' => 'If f(x) = 2x² – 3x + 1, what is f(3)?',
             'exp' => 'f(3) = 2(9) – 3(3) + 1 = 18 – 9 + 1 = 10.',
             'options' => [['text'=>'8','correct'=>false],['text'=>'10','correct'=>true],['text'=>'12','correct'=>false],['text'=>'14','correct'=>false]]],
            ['q' => 'A car travels 150 miles in 3 hours. At this rate, how far will it travel in 5 hours?',
             'exp' => 'Speed = 150/3 = 50 mph. Distance in 5 hours = 50 × 5 = 250 miles.',
             'options' => [['text'=>'200 miles','correct'=>false],['text'=>'225 miles','correct'=>false],['text'=>'250 miles','correct'=>true],['text'=>'300 miles','correct'=>false]]],
            ['q' => 'Which of the following is equivalent to (x + 3)(x – 5)?',
             'exp' => '(x+3)(x–5) = x² – 5x + 3x – 15 = x² – 2x – 15.',
             'options' => [['text'=>'x² + 2x – 15','correct'=>false],['text'=>'x² – 2x – 15','correct'=>true],['text'=>'x² – 2x + 15','correct'=>false],['text'=>'x² – 8x – 15','correct'=>false]]],
            ['q' => 'In a class of 30 students, 40% are boys. How many girls are in the class?',
             'exp' => 'Boys = 40% of 30 = 12. Girls = 30 – 12 = 18.',
             'options' => [['text'=>'12','correct'=>false],['text'=>'15','correct'=>false],['text'=>'18','correct'=>true],['text'=>'20','correct'=>false]]],
            ['q' => 'The slope of a line passing through (2, 5) and (6, 13) is:',
             'exp' => 'Slope = (13 – 5) / (6 – 2) = 8 / 4 = 2.',
             'options' => [['text'=>'1','correct'=>false],['text'=>'2','correct'=>true],['text'=>'3','correct'=>false],['text'=>'4','correct'=>false]]],
            ['q' => 'If the area of a square is 64 cm², what is the perimeter?',
             'exp' => 'Side = √64 = 8 cm. Perimeter = 4 × 8 = 32 cm.',
             'options' => [['text'=>'16 cm','correct'=>false],['text'=>'24 cm','correct'=>false],['text'=>'32 cm','correct'=>true],['text'=>'64 cm','correct'=>false]]],
            ['q' => 'A number increased by 30% gives 91. What is the original number?',
             'exp' => '1.3 × n = 91 → n = 91 / 1.3 = 70.',
             'options' => [['text'=>'60','correct'=>false],['text'=>'65','correct'=>false],['text'=>'70','correct'=>true],['text'=>'75','correct'=>false]]],
        ];
    }

    private function greVerbalQuestions(): array
    {
        return [
            ['q' => 'Choose the word that best completes: "The professor\'s lecture was so _______ that even students who struggled with the subject found it illuminating."',
             'exp' => 'Lucid means clear and easy to understand — exactly what makes a difficult subject accessible.',
             'options' => [['text'=>'abstruse','correct'=>false],['text'=>'lucid','correct'=>true],['text'=>'pedantic','correct'=>false],['text'=>'verbose','correct'=>false]]],
            ['q' => 'The word "ephemeral" most nearly means:',
             'exp' => 'Ephemeral means lasting for a very short time — transitory.',
             'options' => [['text'=>'Permanent','correct'=>false],['text'=>'Mysterious','correct'=>false],['text'=>'Short-lived','correct'=>true],['text'=>'Abundant','correct'=>false]]],
            ['q' => 'Select two words that could replace "cautious" in the sentence, maintaining the same meaning: "The diplomat was _______ in her choice of words."',
             'exp' => 'Circumspect and prudent both convey careful, cautious judgment in behavior.',
             'options' => [['text'=>'Circumspect and prudent','correct'=>true],['text'=>'Impulsive and bold','correct'=>false],['text'=>'Verbose and garrulous','correct'=>false],['text'=>'Taciturn and reticent','correct'=>false]]],
            ['q' => 'The antonym of "loquacious" is:',
             'exp' => 'Loquacious means very talkative. Its antonym is taciturn — reserved and saying very little.',
             'options' => [['text'=>'Verbose','correct'=>false],['text'=>'Talkative','correct'=>false],['text'=>'Taciturn','correct'=>true],['text'=>'Eloquent','correct'=>false]]],
            ['q' => 'In the passage: "Despite widespread criticism, the policy remained intractable." The word "intractable" means:',
             'exp' => 'Intractable means hard to control or deal with — stubborn and not yielding to change.',
             'options' => [['text'=>'Easily changed','correct'=>false],['text'=>'Flexible','correct'=>false],['text'=>'Stubborn and unmanageable','correct'=>true],['text'=>'Widely accepted','correct'=>false]]],
            ['q' => 'Which sentence uses "enervate" correctly?',
             'exp' => 'Enervate means to weaken or drain energy. The tropical heat causing weakness is the correct usage.',
             'options' => [['text'=>'The coach\'s speech enervated the team before the match.','correct'=>false],['text'=>'The tropical heat enervated the hikers, leaving them exhausted.','correct'=>true],['text'=>'She enervated the crowd with her inspiring performance.','correct'=>false],['text'=>'Regular exercise enervates the muscles.','correct'=>false]]],
            ['q' => '"Pellucid" is to "clarity" as "opaque" is to:',
             'exp' => 'Pellucid relates to clarity (transparent). Opaque relates to obscurity (not allowing light through).',
             'options' => [['text'=>'Transparency','correct'=>false],['text'=>'Brightness','correct'=>false],['text'=>'Obscurity','correct'=>true],['text'=>'Simplicity','correct'=>false]]],
            ['q' => 'Select the word that completes the sentence correctly: "The author\'s writing style was _______, incorporating references to ancient mythology, modern physics, and medieval poetry."',
             'exp' => 'Eclectic means deriving from a diverse range of sources — perfect for a varied writing style.',
             'options' => [['text'=>'Monotonous','correct'=>false],['text'=>'Eclectic','correct'=>true],['text'=>'Parochial','correct'=>false],['text'=>'Derivative','correct'=>false]]],
            ['q' => 'The word "perfidious" most nearly means:',
             'exp' => 'Perfidious means deceitful and untrustworthy — guilty of betrayal.',
             'options' => [['text'=>'Loyal','correct'=>false],['text'=>'Treacherous','correct'=>true],['text'=>'Courageous','correct'=>false],['text'=>'Generous','correct'=>false]]],
            ['q' => '"Garrulous" most nearly means:',
             'exp' => 'Garrulous describes a person who is excessively talkative, especially on trivial matters.',
             'options' => [['text'=>'Reserved','correct'=>false],['text'=>'Amusing','correct'=>false],['text'=>'Excessively talkative','correct'=>true],['text'=>'Knowledgeable','correct'=>false]]],
        ];
    }

    private function gmatQuestions(): array
    {
        return [
            ['q' => 'Is x > 0?\n(1) x² > 0\n(2) x³ > 0',
             'exp' => 'Statement 1: x² > 0 means x ≠ 0, but x could be positive or negative — insufficient. Statement 2: x³ > 0 means x > 0 — sufficient. Answer: B.',
             'options' => [['text'=>'Statement 1 alone is sufficient','correct'=>false],['text'=>'Statement 2 alone is sufficient','correct'=>true],['text'=>'Both statements together are sufficient','correct'=>false],['text'=>'Each statement alone is sufficient','correct'=>false]]],
            ['q' => 'Which of the following most strengthens the argument: "Electric vehicles produce zero tailpipe emissions, so switching to EVs will eliminate urban air pollution."?',
             'exp' => 'The argument needs a premise linking EVs to actual air quality improvement. If the electricity grid is clean, the total emissions reduction is real.',
             'options' => [['text'=>'EVs are more expensive than petrol cars','correct'=>false],['text'=>'Urban electricity grids are powered entirely by renewable energy','correct'=>true],['text'=>'EV batteries require mining of rare earth metals','correct'=>false],['text'=>'Most urban dwellers prefer public transport','correct'=>false]]],
            ['q' => 'The argument "All swans I have observed are white; therefore, all swans are white" is flawed because:',
             'exp' => 'This is the problem of inductive reasoning — a limited sample cannot prove a universal generalisation (hasty generalisation fallacy).',
             'options' => [['text'=>'Swans come in many colors','correct'=>false],['text'=>'It assumes a limited sample represents the entire population','correct'=>true],['text'=>'It uses circular reasoning','correct'=>false],['text'=>'It confuses correlation with causation','correct'=>false]]],
            ['q' => 'A company\'s profit increased by 25% in Year 1 and decreased by 20% in Year 2. The net change over two years is:',
             'exp' => 'If profit = 100, after Y1 = 125, after Y2 = 125 × 0.8 = 100. Net change = 0%.',
             'options' => [['text'=>'5% increase','correct'=>false],['text'=>'5% decrease','correct'=>false],['text'=>'No net change','correct'=>true],['text'=>'10% decrease','correct'=>false]]],
            ['q' => 'What is the value of x + y?\n(1) 2x + y = 10\n(2) x + 2y = 8',
             'exp' => 'Add the equations: 3x + 3y = 18, so x + y = 6. Both statements together are needed.',
             'options' => [['text'=>'Statement 1 alone is sufficient','correct'=>false],['text'=>'Statement 2 alone is sufficient','correct'=>false],['text'=>'Both statements together are sufficient','correct'=>true],['text'=>'Neither statement is sufficient','correct'=>false]]],
            ['q' => 'Which assumption is the argument relying on: "Our product\'s sales increased after we ran ads. Therefore, the ads caused the sales increase."?',
             'exp' => 'The argument assumes no other factors contributed to the sales increase — this is the causal fallacy (post hoc ergo propter hoc).',
             'options' => [['text'=>'Sales always follow advertising','correct'=>false],['text'=>'No other factors caused the sales increase during that period','correct'=>true],['text'=>'The product is superior to competitors','correct'=>false],['text'=>'Advertising is the most cost-effective marketing method','correct'=>false]]],
            ['q' => 'If a = 2b and b = 3c, what is a in terms of c?',
             'exp' => 'a = 2b = 2(3c) = 6c.',
             'options' => [['text'=>'a = 3c','correct'=>false],['text'=>'a = 5c','correct'=>false],['text'=>'a = 6c','correct'=>true],['text'=>'a = 9c','correct'=>false]]],
            ['q' => 'The average (mean) of 5 numbers is 40. If one number is removed and the new average is 45, what was the removed number?',
             'exp' => 'Sum of 5 = 200. Sum of 4 = 180. Removed number = 200 – 180 = 20.',
             'options' => [['text'=>'10','correct'=>false],['text'=>'15','correct'=>false],['text'=>'20','correct'=>true],['text'=>'25','correct'=>false]]],
            ['q' => 'In a survey of 100 people, 60 like tea, 50 like coffee, and 20 like both. How many like neither?',
             'exp' => 'Using inclusion-exclusion: tea or coffee = 60 + 50 – 20 = 90. Neither = 100 – 90 = 10.',
             'options' => [['text'=>'5','correct'=>false],['text'=>'10','correct'=>true],['text'=>'15','correct'=>false],['text'=>'30','correct'=>false]]],
            ['q' => 'Which of the following most weakens the argument: "Countries with higher chocolate consumption have more Nobel Prize winners per capita. Therefore, eating more chocolate boosts intelligence."?',
             'exp' => 'The argument confuses correlation with causation. A confounding variable (wealth) could explain both — wealthy countries consume more chocolate AND invest more in education.',
             'options' => [['text'=>'Chocolate contains antioxidants','correct'=>false],['text'=>'Wealthier countries tend to both consume more chocolate and have better-funded educational systems','correct'=>true],['text'=>'Nobel Prizes are only awarded in certain fields','correct'=>false],['text'=>'Some high-chocolate-consuming countries have few Nobel laureates','correct'=>false]]],
        ];
    }

    private function logicalReasoningQuestions(): array
    {
        return [
            ['q' => 'If "A" is coded as 1, "B" as 2, and so on, what is the code for "EXAM"?',
             'exp' => 'E=5, X=24, A=1, M=13. Code = 5-24-1-13.',
             'options' => [['text'=>'5-24-1-13','correct'=>true],['text'=>'5-23-1-12','correct'=>false],['text'=>'4-24-1-13','correct'=>false],['text'=>'5-24-2-13','correct'=>false]]],
            ['q' => 'In a row of 20 people, Maria is 8th from the left and 13th from the right. How many people are in the row?',
             'exp' => 'Total = 8 + 13 – 1 = 20.',
             'options' => [['text'=>'19','correct'=>false],['text'=>'20','correct'=>true],['text'=>'21','correct'=>false],['text'=>'22','correct'=>false]]],
            ['q' => 'All doctors are scientists. Some scientists are poets. Which conclusion is definitely true?',
             'exp' => 'We can only definitively conclude that some scientists are doctors (conversion of the first premise). We cannot conclude doctors are poets.',
             'options' => [['text'=>'All doctors are poets','correct'=>false],['text'=>'Some scientists are doctors','correct'=>true],['text'=>'Some poets are doctors','correct'=>false],['text'=>'No doctors are poets','correct'=>false]]],
            ['q' => 'Find the missing number in the series: 3, 6, 11, 18, 27, ?',
             'exp' => 'Differences: 3, 5, 7, 9, 11 (odd numbers increasing by 2). Next term = 27 + 11 = 38.',
             'options' => [['text'=>'36','correct'=>false],['text'=>'37','correct'=>false],['text'=>'38','correct'=>true],['text'=>'40','correct'=>false]]],
            ['q' => 'James is older than Maria, who is younger than Ben. Ben is older than Claire, who is older than James. Who is the youngest?',
             'exp' => 'Order: Ben > Claire > James > Maria. Maria is youngest.',
             'options' => [['text'=>'James','correct'=>false],['text'=>'Claire','correct'=>false],['text'=>'Maria','correct'=>true],['text'=>'Ben','correct'=>false]]],
            ['q' => 'A person walks 10 km North, turns East and walks 5 km, then turns South and walks 10 km. How far are they from the starting point?',
             'exp' => 'Net displacement: 5 km East (North and South cancel). Distance = 5 km.',
             'options' => [['text'=>'0 km','correct'=>false],['text'=>'5 km','correct'=>true],['text'=>'10 km','correct'=>false],['text'=>'25 km','correct'=>false]]],
            ['q' => 'If "+" means "÷", "÷" means "×", "×" means "–", and "–" means "+", then: 48 + 6 ÷ 2 × 4 – 1 = ?',
             'exp' => 'Substituting: 48÷6×2–4+1 = 8×2–4+1 = 16–4+1 = 13.',
             'options' => [['text'=>'11','correct'=>false],['text'=>'13','correct'=>true],['text'=>'15','correct'=>false],['text'=>'17','correct'=>false]]],
            ['q' => 'Pointing to a woman, a man says "Her mother is the only daughter of my mother." How is the woman related to the man?',
             'exp' => 'The only daughter of his mother = his sister. The woman\'s mother is his sister. So the woman is his niece.',
             'options' => [['text'=>'Sister','correct'=>false],['text'=>'Daughter','correct'=>false],['text'=>'Niece','correct'=>true],['text'=>'Cousin','correct'=>false]]],
            ['q' => 'Find the odd one out: 16, 25, 36, 48, 64',
             'exp' => '16=4², 25=5², 36=6², 64=8² are perfect squares. 48 is not a perfect square.',
             'options' => [['text'=>'36','correct'=>false],['text'=>'25','correct'=>false],['text'=>'48','correct'=>true],['text'=>'64','correct'=>false]]],
            ['q' => 'A clock shows 4:20. What is the angle between the hour and minute hands?',
             'exp' => 'Minute hand: 20 × 6 = 120°. Hour hand: 4 × 30 + 20 × 0.5 = 120 + 10 = 130°. Angle = |130 – 120| = 10°.',
             'options' => [['text'=>'0°','correct'=>false],['text'=>'10°','correct'=>true],['text'=>'20°','correct'=>false],['text'=>'30°','correct'=>false]]],
        ];
    }

    private function ieltsQuestions(): array
    {
        return [
            ['q' => 'Choose the correct form: "By the time she arrives, we _______ dinner."',
             'exp' => 'Future Perfect is used for an action that will be completed before a specific future time. "will have finished" is correct.',
             'options' => [['text'=>'finish','correct'=>false],['text'=>'will finish','correct'=>false],['text'=>'will have finished','correct'=>true],['text'=>'are finishing','correct'=>false]]],
            ['q' => 'The word "mitigate" most closely means:',
             'exp' => 'Mitigate means to make something less severe, harmful, or painful.',
             'options' => [['text'=>'Worsen','correct'=>false],['text'=>'Reduce the severity of','correct'=>true],['text'=>'Eliminate','correct'=>false],['text'=>'Investigate','correct'=>false]]],
            ['q' => 'Which sentence is grammatically correct?',
             'exp' => '"Neither…nor" is a correlative conjunction that takes a singular verb when both subjects are singular.',
             'options' => [['text'=>'Neither of the students have completed their assignment.','correct'=>false],['text'=>'Neither of the students has completed their assignment.','correct'=>true],['text'=>'Neither student have completed their assignment.','correct'=>false],['text'=>'Neither students has completed their assignment.','correct'=>false]]],
            ['q' => 'In academic writing, which phrase best introduces a counterargument?',
             'exp' => '"However, critics argue that..." is the standard academic phrasing to introduce a counter-perspective.',
             'options' => [['text'=>'Obviously, some people wrongly believe that...','correct'=>false],['text'=>'However, critics argue that...','correct'=>true],['text'=>'Everybody knows that the opposite is...','correct'=>false],['text'=>'To be honest, the other side says...','correct'=>false]]],
            ['q' => 'The passage states: "Urbanisation has accelerated at an unprecedented rate, leading to both economic opportunities and environmental challenges." What does "unprecedented" mean?',
             'exp' => 'Unprecedented means never done or known before — happening for the first time at this scale.',
             'options' => [['text'=>'Gradual and steady','correct'=>false],['text'=>'Predictable','correct'=>false],['text'=>'Never seen before at this scale','correct'=>true],['text'=>'Well-documented','correct'=>false]]],
            ['q' => 'Select the correct spelling:',
             'exp' => '"Necessary" is the correct spelling — commonly misspelled as "neccessary" or "necesary".',
             'options' => [['text'=>'Neccessary','correct'=>false],['text'=>'Necessary','correct'=>true],['text'=>'Necesary','correct'=>false],['text'=>'Necessery','correct'=>false]]],
            ['q' => 'Choose the sentence with correct subject-verb agreement:',
             'exp' => '"The number of students is increasing" is correct — "the number" is a singular subject. "A number of students are" would use a plural verb.',
             'options' => [['text'=>'The number of students are increasing.','correct'=>false],['text'=>'The number of students is increasing.','correct'=>true],['text'=>'The numbers of student is increasing.','correct'=>false],['text'=>'A number of student is increasing.','correct'=>false]]],
            ['q' => 'Which type of writing task is most appropriate for discussing "advantages and disadvantages"?',
             'exp' => 'IELTS Writing Task 2 asks candidates to discuss arguments, often requiring evaluation of advantages and disadvantages of a topic.',
             'options' => [['text'=>'IELTS Writing Task 1','correct'=>false],['text'=>'IELTS Writing Task 2','correct'=>true],['text'=>'IELTS Speaking Part 1','correct'=>false],['text'=>'IELTS Listening Section 3','correct'=>false]]],
            ['q' => 'The phrase "in spite of" is followed by:',
             'exp' => '"In spite of" is a preposition and must be followed by a noun, pronoun, or gerund (verb+ing) — not a clause with a subject and verb.',
             'options' => [['text'=>'A subject + verb clause','correct'=>false],['text'=>'A noun, pronoun, or gerund','correct'=>true],['text'=>'An adjective','correct'=>false],['text'=>'A full sentence','correct'=>false]]],
            ['q' => 'Which sentence correctly uses a relative clause?',
             'exp' => '"Which" introduces a non-restrictive relative clause (giving extra information about the noun), correctly placed after "book" and separated by a comma.',
             'options' => [['text'=>'The book, which I borrowed, it was very interesting.','correct'=>false],['text'=>'The book, which I borrowed, was very interesting.','correct'=>true],['text'=>'The book which I borrowed it was interesting.','correct'=>false],['text'=>'The book that I borrowed, was very interesting.','correct'=>false]]],
        ];
    }

    private function quantAptitudeQuestions(): array
    {
        return [
            ['q' => 'A train 300 m long passes a pole in 30 seconds. How long will it take to pass a platform 450 m long?',
             'exp' => 'Speed = 300/30 = 10 m/s. Time = (300+450)/10 = 75 seconds.',
             'options' => [['text'=>'60 sec','correct'=>false],['text'=>'75 sec','correct'=>true],['text'=>'90 sec','correct'=>false],['text'=>'45 sec','correct'=>false]]],
            ['q' => 'A can complete a job in 12 days. B can complete the same job in 18 days. Working together, how many days will they take?',
             'exp' => 'Combined rate = 1/12 + 1/18 = 3/36 + 2/36 = 5/36. Days = 36/5 = 7.2 days.',
             'options' => [['text'=>'6 days','correct'=>false],['text'=>'7.2 days','correct'=>true],['text'=>'8 days','correct'=>false],['text'=>'9 days','correct'=>false]]],
            ['q' => 'The simple interest on $6,000 at 8% per annum for 3 years is:',
             'exp' => 'SI = (P × R × T) / 100 = (6000 × 8 × 3) / 100 = $1,440.',
             'options' => [['text'=>'$1,200','correct'=>false],['text'=>'$1,440','correct'=>true],['text'=>'$1,600','correct'=>false],['text'=>'$960','correct'=>false]]],
            ['q' => 'A shopkeeper buys an item for $80 and sells it for $100. What is the profit percentage?',
             'exp' => 'Profit = $20. Profit% = (20/80) × 100 = 25%.',
             'options' => [['text'=>'20%','correct'=>false],['text'=>'25%','correct'=>true],['text'=>'30%','correct'=>false],['text'=>'15%','correct'=>false]]],
            ['q' => 'If 20% of a number is 60, what is 45% of that number?',
             'exp' => '20% = 60, so 100% = 300. 45% of 300 = 135.',
             'options' => [['text'=>'120','correct'=>false],['text'=>'130','correct'=>false],['text'=>'135','correct'=>true],['text'=>'140','correct'=>false]]],
            ['q' => 'The average of 8 numbers is 25. If the average of the first 5 is 20, what is the average of the remaining 3?',
             'exp' => 'Sum of 8 = 200. Sum of first 5 = 100. Sum of last 3 = 100. Average = 100/3 ≈ 33.33.',
             'options' => [['text'=>'30','correct'=>false],['text'=>'33.33','correct'=>true],['text'=>'35','correct'=>false],['text'=>'40','correct'=>false]]],
            ['q' => 'Two numbers are in the ratio 3:5. If their sum is 96, what is the larger number?',
             'exp' => 'Parts: 3+5=8. Larger number = (5/8) × 96 = 60.',
             'options' => [['text'=>'36','correct'=>false],['text'=>'48','correct'=>false],['text'=>'60','correct'=>true],['text'=>'72','correct'=>false]]],
            ['q' => 'The compound interest on $5,000 at 10% per annum for 2 years is:',
             'exp' => 'CI = 5000 × [(1.1)² – 1] = 5000 × 0.21 = $1,050.',
             'options' => [['text'=>'$1,000','correct'=>false],['text'=>'$1,050','correct'=>true],['text'=>'$1,100','correct'=>false],['text'=>'$950','correct'=>false]]],
            ['q' => 'A 12% discount is given on an item priced at $250. What is the selling price?',
             'exp' => 'Discount = 12% of 250 = $30. Selling price = $250 – $30 = $220.',
             'options' => [['text'=>'$210','correct'=>false],['text'=>'$215','correct'=>false],['text'=>'$220','correct'=>true],['text'=>'$225','correct'=>false]]],
            ['q' => 'In what time will $2,000 double at 5% simple interest per year?',
             'exp' => 'For SI to double: Interest = Principal. SI = (P × R × T)/100 = P → T = 100/R = 100/5 = 20 years.',
             'options' => [['text'=>'10 years','correct'=>false],['text'=>'15 years','correct'=>false],['text'=>'20 years','correct'=>true],['text'=>'25 years','correct'=>false]]],
        ];
    }
}
