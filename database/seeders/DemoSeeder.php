<?php

namespace Database\Seeders;

use App\Models\AiGenerationLog;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\StudentBatch;
use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data…');

        $this->call(CategorySeeder::class);

        // Wipe previous demo data cleanly
        $this->wipe();

        $creators  = $this->seedLecturers();
        $batches   = $this->seedBatches();
        $customers = $this->seedStudents($batches);
        $quizzes   = $this->seedQuizzes($creators);
        $this->seedAssignments($quizzes, $creators, $batches, $customers);
        $this->seedAttempts($quizzes, $customers);
        $this->seedAiLogs($creators, $quizzes);

        $this->command->newLine();
        $this->command->info('✅ Demo seed complete. Test credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',   'admin@quiz.com', 'password'],
                ['Lecturer', 'priya@demo.quiz',   'password'],
                ['Lecturer', 'rahul@demo.quiz',   'password'],
                ['Lecturer', 'sofia@demo.quiz',   'password'],
                ['Student','alice@demo.quiz',   'password'],
                ['Student','bob@demo.quiz',     'password'],
                ['Student','carol@demo.quiz',   'password'],
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
        $quizIds = Quiz::whereIn('lecturer_id', $userIds)->pluck('id');

        DB::table('attempt_answers')->whereIn('attempt_id',
            DB::table('attempts')->whereIn('quiz_id', $quizIds)->pluck('id')
        )->delete();
        DB::table('certificates')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('attempts')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('quiz_enrollments')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('quiz_assignments')->whereIn('quiz_id', $quizIds)->delete();
        // Delete questions/options for these quizzes
        $questionIds = DB::table('questions')->whereIn('quiz_id', $quizIds)->pluck('id');
        DB::table('fill_blank_answers')->whereIn('question_id', $questionIds)->delete();
        DB::table('question_options')->whereIn('question_id', $questionIds)->delete();
        DB::table('questions')->whereIn('quiz_id', $quizIds)->delete();
        DB::table('quizzes')->whereIn('id', $quizIds)->delete();

        DB::table('ai_generation_logs')->whereIn('user_id', $userIds)->delete();
        User::withTrashed()->whereIn('email', $demoEmails)->forceDelete();
        StudentBatch::whereIn('code', ['MBBS-2026-A', 'MBBS-2026-B'])->delete();
    }

    private function seedBatches(): array
    {
        return [
            'a' => StudentBatch::create([
                'name'        => 'MBBS 2026 — Group A',
                'code'        => 'MBBS-2026-A',
                'description' => 'Demo batch for first-year medical students.',
                'is_active'   => true,
            ]),
            'b' => StudentBatch::create([
                'name'        => 'MBBS 2026 — Group B',
                'code'        => 'MBBS-2026-B',
                'description' => 'Demo batch for second medical cohort.',
                'is_active'   => true,
            ]),
        ];
    }

    // ──────────────────────────────────────────
    // Creators
    // ──────────────────────────────────────────
    private function seedLecturers(): array
    {
        $data = [
            ['name'=>'Dr. Priya Sharma',    'email'=>'priya@demo.quiz',   'credits'=>8],
            ['name'=>'Dr. Rahul Mehta',     'email'=>'rahul@demo.quiz',   'credits'=>3],
            ['name'=>'Dr. Sofia Rodriguez', 'email'=>'sofia@demo.quiz',   'credits'=>10],
        ];
        return User::unguarded(function () use ($data) {
            return array_map(function ($d) {
                $user = User::create([
                    'name'                      => $d['name'],
                    'email'                     => $d['email'],
                    'password'                  => bcrypt('password'),
                    'role'                      => 'lecturer',
                    'is_active'                 => true,
                    'ai_credits_free_remaining' => $d['credits'],
                    'ai_credits_used'           => 10 - $d['credits'],
                    'email_verified_at'         => now(),
                ]);
                $user->assignRole('lecturer');

                return $user;
            }, $data);
        });
    }

    // ──────────────────────────────────────────
    // Customers (named, for easy testing)
    // ──────────────────────────────────────────
    private function seedStudents(array $batches): array
    {
        $named = [
            ['name'=>'Alice Johnson', 'email'=>'alice@demo.quiz', 'batch'=>'a'],
            ['name'=>'Bob Smith',     'email'=>'bob@demo.quiz', 'batch'=>'a'],
            ['name'=>'Carol White',   'email'=>'carol@demo.quiz', 'batch'=>'a'],
            ['name'=>'Dave Brown',    'email'=>'dave@demo.quiz', 'batch'=>'b'],
            ['name'=>'Eve Davis',     'email'=>'eve@demo.quiz', 'batch'=>'b'],
            ['name'=>'Frank Miller',  'email'=>'frank@demo.quiz', 'batch'=>'b'],
            ['name'=>'Grace Wilson',  'email'=>'grace@demo.quiz', 'batch'=>'b'],
            ['name'=>'Henry Taylor',  'email'=>'henry@demo.quiz', 'batch'=>'a'],
            ['name'=>'Ivy Anderson',  'email'=>'ivy@demo.quiz', 'batch'=>'b'],
            ['name'=>'Jack Thomas',   'email'=>'jack@demo.quiz', 'batch'=>'a'],
        ];
        $customers = [];
        User::unguarded(function () use ($named, $batches, &$customers) {
            foreach ($named as $d) {
                $user = User::create([
                    'name'                      => $d['name'],
                    'email'                     => $d['email'],
                    'password'                  => bcrypt('password'),
                    'role'                      => 'student',
                    'student_batch_id'          => $batches[$d['batch']]->id,
                    'is_active'                 => true,
                    'ai_credits_free_remaining' => 0,
                    'email_verified_at'         => now(),
                ]);
                $user->assignRole('student');
                $customers[] = $user;
            }
            for ($i = 1; $i <= 10; $i++) {
                $user = User::create([
                    'name'                      => "Student $i",
                    'email'                     => "student{$i}@demo.quiz",
                    'password'                  => bcrypt('password'),
                    'role'                      => 'student',
                    'student_batch_id'          => $batches[$i % 2 ? 'a' : 'b']->id,
                    'is_active'                 => true,
                    'ai_credits_free_remaining' => 0,
                    'email_verified_at'         => now(),
                ]);
                $user->assignRole('student');
                $customers[] = $user;
            }
        });
        return $customers;
    }

    private function seedAssignments(array $quizzes, array $creators, array $batches, array $customers): void
    {
        $service = app(QuizAssignmentService::class);

        foreach (array_slice($quizzes, 0, 6) as $quiz) {
            $service->assignToBatch($quiz, $quiz->lecturer, $batches['a']);
        }

        $service->assignToBatch($quizzes[6], $creators[0], $batches['b']);

        if (isset($customers[3], $quizzes[7])) {
            $service->assignToStudent($quizzes[7], $quizzes[7]->lecturer, $customers[3]);
        }
    }

    private function seedAttempts(array $quizzes, array $customers): void
    {
        foreach (array_slice($quizzes, 0, 3) as $quiz) {
            foreach (array_slice($customers, 0, 4) as $student) {
                $enrollment = QuizEnrollment::firstOrCreate(
                    ['quiz_id' => $quiz->id, 'user_id' => $student->id],
                    ['enrolled_at' => now(), 'source' => 'assigned']
                );

                $score = random_int(4, 10);
                Attempt::create([
                    'quiz_id'            => $quiz->id,
                    'user_id'            => $student->id,
                    'enrollment_id'      => $enrollment->id,
                    'status'             => 'completed',
                    'score'              => $score,
                    'total_marks'        => 10,
                    'percentage'         => $score * 10,
                    'time_taken_seconds' => random_int(300, 1800),
                    'started_at'         => now()->subDays(random_int(1, 14)),
                    'submitted_at'       => now()->subDays(random_int(0, 13)),
                ]);
            }
        }
    }

    private function seedAiLogs(array $creators, array $quizzes): void
    {
        foreach ($creators as $i => $creator) {
            AiGenerationLog::create([
                'user_id'              => $creator->id,
                'quiz_id'              => $quizzes[$i]->id ?? null,
                'prompt'               => 'Generate 10 MCQ questions for demo quiz.',
                'questions_generated'  => 10,
                'tokens_used'          => 920,
                'model'                => 'gpt-4o-mini',
                'status'               => 'success',
                'was_free'             => true,
            ]);
        }
    }

    // ──────────────────────────────────────────
    // Quizzes + Questions
    // ──────────────────────────────────────────
    private function seedQuizzes(array $creators): array
    {
        $cats = Category::pluck('id', 'slug');
        $medicine     = $cats['medicine']     ?? Category::first()->id;
        $surgery      = $cats['surgery']      ?? $medicine;
        $obstetrics   = $cats['obstetrics']   ?? $medicine;
        $gynecology   = $cats['gynecology']   ?? $medicine;
        $pediatrics   = $cats['pediatrics']   ?? $medicine;
        $pharmacology = $cats['pharmacology'] ?? $medicine;
        $anatomy      = $cats['anatomy']      ?? $medicine;
        $physiology   = $cats['physiology']   ?? $medicine;
        $pathology    = $cats['pathology']    ?? $medicine;
        $microbiology = $cats['microbiology'] ?? $medicine;

        $quizDefs = [
            ['title'=>'Internal Medicine — Heart Failure & Hypertension', 'cat'=>$medicine,     'lecturer'=>$creators[0], 'duration'=>30, 'pass'=>60, 'neg'=>false, 'cert'=>true],
            ['title'=>'General Surgery — Wounds & Healing',              'cat'=>$surgery,      'lecturer'=>$creators[1], 'duration'=>45, 'pass'=>65, 'neg'=>true,  'cert'=>true],
            ['title'=>'Obstetrics — Antenatal Care',                     'cat'=>$obstetrics,   'lecturer'=>$creators[2], 'duration'=>20, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Gynecology — Menstrual Disorders',                'cat'=>$gynecology,   'lecturer'=>$creators[2], 'duration'=>30, 'pass'=>70, 'neg'=>false, 'cert'=>true],
            ['title'=>'Pediatrics — Immunization & Growth',              'cat'=>$pediatrics,   'lecturer'=>$creators[1], 'duration'=>25, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Pharmacology — Antibiotics & Mechanisms',         'cat'=>$pharmacology, 'lecturer'=>$creators[0], 'duration'=>30, 'pass'=>60, 'neg'=>false, 'cert'=>true],
            ['title'=>'Human Anatomy — Cardiovascular System',           'cat'=>$anatomy,      'lecturer'=>$creators[1], 'duration'=>60, 'pass'=>65, 'neg'=>true,  'cert'=>true],
            ['title'=>'Physiology — Renal Function',                       'cat'=>$physiology,   'lecturer'=>$creators[0], 'duration'=>20, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Pathology — Inflammation & Neoplasia',            'cat'=>$pathology,    'lecturer'=>$creators[2], 'duration'=>20, 'pass'=>60, 'neg'=>false, 'cert'=>false],
            ['title'=>'Microbiology — Bacteria & Staining',              'cat'=>$microbiology, 'lecturer'=>$creators[0], 'duration'=>40, 'pass'=>65, 'neg'=>false, 'cert'=>true],
        ];

        $quizzes = [];
        $allQuestionBanks = [
            $this->medicineQs(), $this->surgeryQs(), $this->obstetricsQs(), $this->gynecologyQs(),
            $this->pediatricsQs(), $this->pharmacologyQs(), $this->anatomyQs(), $this->physiologyQs(),
            $this->pathologyQs(), $this->microbiologyQs(),
        ];

        foreach ($quizDefs as $i => $def) {
            $quiz = Quiz::create([
                'lecturer_id'               => $def['lecturer']->id,
                'category_id'              => $def['cat'],
                'title'                    => $def['title'],
                'slug'                     => Str::slug($def['title']),
                'description'              => "A comprehensive quiz covering {$def['title']}. Test your knowledge and earn a certificate!",
                'status'                   => 'published',
                'visibility'               => 'public',
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
    // Question banks (10 Qs each quiz)
    // ──────────────────────────────────────────
    private function medicineQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which drug class is first-line for chronic heart failure with reduced ejection fraction?','explanation'=>'ACE inhibitors (or ARNI) are cornerstone therapy for HFrEF.','options'=>[['content'=>'Calcium channel blockers','is_correct'=>false],['content'=>'ACE inhibitors','is_correct'=>true],['content'=>'Alpha blockers','is_correct'=>false],['content'=>'Thiazolidinediones','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'B-type natriuretic peptide (BNP) is elevated in acute decompensated heart failure.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'A blood pressure of 148/92 mmHg on two separate occasions is classified as:','options'=>[['content'=>'Normal','is_correct'=>false],['content'=>'Elevated','is_correct'=>false],['content'=>'Stage 1 hypertension','is_correct'=>true],['content'=>'Hypertensive emergency','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The most common cause of secondary hypertension in young adults is renal ______ disease.','blank_answers'=>['parenchymal','parenchymal disease']],
        ['type'=>'mcq_single','content'=>'Which finding is most specific for left ventricular failure?','options'=>[['content'=>'Jugular venous distension','is_correct'=>false],['content'=>'Bilateral basal crackles','is_correct'=>true],['content'=>'Peripheral oedema alone','is_correct'=>false],['content'=>'Tachycardia','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Metformin is contraindicated in which condition?','options'=>[['content'=>'Type 2 diabetes with obesity','is_correct'=>false],['content'=>'Severe renal impairment (eGFR <30)','is_correct'=>true],['content'=>'Hypertension','is_correct'=>false],['content'=>'Hyperlipidaemia','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Digoxin toxicity can cause visual disturbances such as yellow-green halos.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which electrolyte abnormality predisposes to torsades de pointes?','options'=>[['content'=>'Hyperkalaemia','is_correct'=>false],['content'=>'Hypomagnesaemia','is_correct'=>true],['content'=>'Hypernatraemia','is_correct'=>false],['content'=>'Hypercalcaemia','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The JVP "a" wave is absent in:','options'=>[['content'=>'Tricuspid regurgitation','is_correct'=>false],['content'=>'Atrial fibrillation','is_correct'=>true],['content'=>'Right heart failure','is_correct'=>false],['content'=>'Pulmonary embolism','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The gold standard investigation for diagnosing pulmonary embolism is CT pulmonary ______.','blank_answers'=>['angiography','angiogram']],
    ]; }

    private function surgeryQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which phase of wound healing involves collagen deposition and scar formation?','explanation'=>'Proliferative phase involves granulation tissue and collagen synthesis.','options'=>[['content'=>'Inflammatory phase','is_correct'=>false],['content'=>'Proliferative phase','is_correct'=>true],['content'=>'Haemostasis','is_correct'=>false],['content'=>'Remodelling only','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Primary intention healing occurs when wound edges are approximated with sutures.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The most common organism in surgical site infections is:','options'=>[['content'=>'Escherichia coli','is_correct'=>false],['content'=>'Staphylococcus aureus','is_correct'=>true],['content'=>'Pseudomonas aeruginosa','is_correct'=>false],['content'=>'Streptococcus pyogenes','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Prophylactic antibiotics for clean-contaminated surgery should ideally be given within ______ minutes before incision.','blank_answers'=>['60','sixty']],
        ['type'=>'mcq_single','content'=>'Which sign indicates raised intracranial pressure after head injury?','options'=>[['content'=>'Bilateral pupil constriction','is_correct'=>false],['content'=>'Unilateral fixed dilated pupil','is_correct'=>true],['content'=>'Bradycardia with hypotension only','is_correct'=>false],['content'=>'Normal GCS','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Acute appendicitis classically presents with pain migrating to the:','options'=>[['content'=>'Left iliac fossa','is_correct'=>false],['content'=>'Right iliac fossa','is_correct'=>true],['content'=>'Epigastrium','is_correct'=>false],['content'=>'Umbilicus only','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Tetanus prophylaxis is required for contaminated puncture wounds.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which fluid is preferred for initial resuscitation in haemorrhagic shock?','options'=>[['content'=>'5% dextrose','is_correct'=>false],['content'=>'Isotonic crystalloid (0.9% saline or balanced solution)','is_correct'=>true],['content'=>'Hypotonic saline','is_correct'=>false],['content'=>'Fresh frozen plasma alone','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'A patient with suspected bowel obstruction should NOT receive:','options'=>[['content'=>'IV fluids','is_correct'=>false],['content'=>'Nasogastric decompression','is_correct'=>false],['content'=>'Oral laxatives','is_correct'=>true],['content'=>'Surgical review','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The surgical instrument used to clamp blood vessels is called a ______.','blank_answers'=>['haemostat','hemostat','artery forceps']],
    ]; }

    private function obstetricsQs(): array { return [
        ['type'=>'mcq_single','content'=>'How many antenatal visits are recommended in an uncomplicated pregnancy (WHO model)?','options'=>[['content'=>'4','is_correct'=>false],['content'=>'8','is_correct'=>true],['content'=>'12','is_correct'=>false],['content'=>'2','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Folic acid supplementation should begin at least one month before conception.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The first fetal movement (quickening) is typically felt at approximately:','options'=>[['content'=>'8 weeks','is_correct'=>false],['content'=>'18–20 weeks','is_correct'=>true],['content'=>'28 weeks','is_correct'=>false],['content'=>'32 weeks','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Gestational diabetes is usually screened with an oral glucose ______ test at 24–28 weeks.','blank_answers'=>['tolerance','tolerance test']],
        ['type'=>'mcq_single','content'=>'Which vaccine is routinely recommended in every pregnancy?','options'=>[['content'=>'MMR','is_correct'=>false],['content'=>'Influenza and Tdap','is_correct'=>true],['content'=>'Varicella','is_correct'=>false],['content'=>'HPV','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Fundal height at 20 weeks gestation is approximately at the level of the:','options'=>[['content'=>'Symphysis pubis','is_correct'=>false],['content'=>'Umbilicus','is_correct'=>true],['content'=>'Xiphisternum','is_correct'=>false],['content'=>'Costal margin','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Preeclampsia is defined by hypertension and proteinuria after 20 weeks gestation.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which ultrasound measurement is used for dating in early pregnancy?','options'=>[['content'=>'Femur length','is_correct'=>false],['content'=>'Crown-rump length','is_correct'=>true],['content'=>'Abdominal circumference','is_correct'=>false],['content'=>'Biparietal diameter only in third trimester','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The normal fetal heart rate range is approximately:','options'=>[['content'=>'60–80 bpm','is_correct'=>false],['content'=>'110–160 bpm','is_correct'=>true],['content'=>'180–200 bpm','is_correct'=>false],['content'=>'90–100 bpm','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The hormone detected in urine pregnancy tests is human chorionic ______.','blank_answers'=>['gonadotropin','gonadotrophin','hcg']],
    ]; }

    private function gynecologyQs(): array { return [
        ['type'=>'mcq_single','content'=>'The most common cause of secondary amenorrhoea is:','options'=>[['content'=>'Asherman syndrome','is_correct'=>false],['content'=>'Pregnancy','is_correct'=>true],['content'=>'Turner syndrome','is_correct'=>false],['content'=>'Müllerian agenesis','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Polycystic ovary syndrome (PCOS) is associated with insulin resistance.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which hormone is elevated in PCOS?','options'=>[['content'=>'FSH','is_correct'=>false],['content'=>'LH (relative to FSH)','is_correct'=>true],['content'=>'Prolactin always normal','is_correct'=>false],['content'=>'TSH','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Dysmenorrhoea caused by endometrial tissue outside the uterus is called ______.','blank_answers'=>['endometriosis']],
        ['type'=>'mcq_single','content'=>'The most common type of uterine fibroid is:','options'=>[['content'=>'Subserosal','is_correct'=>false],['content'=>'Intramural','is_correct'=>true],['content'=>'Submucosal','is_correct'=>false],['content'=>'Cervical','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'First-line treatment for heavy menstrual bleeding without structural pathology is:','options'=>[['content'=>'Hysterectomy','is_correct'=>false],['content'=>'Combined oral contraceptive pill or levonorgestrel IUD','is_correct'=>true],['content'=>'Clomiphene','is_correct'=>false],['content'=>'Danazol as first line','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Cervical cancer screening with HPV testing/Pap smear reduces mortality.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which condition presents with cyclical pelvic pain and dyspareunia?','options'=>[['content'=>'Bacterial vaginosis','is_correct'=>false],['content'=>'Endometriosis','is_correct'=>true],['content'=>'Vulvovaginal candidiasis','is_correct'=>false],['content'=>'Trichomoniasis','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The normal pH of the vagina in reproductive age is approximately:','options'=>[['content'=>'7.0–8.0','is_correct'=>false],['content'=>'3.8–4.5','is_correct'=>true],['content'=>'6.5–7.0','is_correct'=>false],['content'=>'2.0–3.0','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The most common bacterial cause of pelvic inflammatory disease is Neisseria ______ or Chlamydia trachomatis.','blank_answers'=>['gonorrhoeae','gonorrhoeae']],
    ]; }

    private function pediatricsQs(): array { return [
        ['type'=>'mcq_single','content'=>'At what age is the first dose of MMR vaccine typically given?','options'=>[['content'=>'Birth','is_correct'=>false],['content'=>'12–15 months','is_correct'=>true],['content'=>'5 years only','is_correct'=>false],['content'=>'6 weeks','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Exclusive breastfeeding is recommended for the first six months of life.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which milestone is expected at approximately 6 months of age?','options'=>[['content'=>'Walking independently','is_correct'=>false],['content'=>'Sitting without support','is_correct'=>true],['content'=>'Speaking two-word sentences','is_correct'=>false],['content'=>'Running','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'A newborn with physiological jaundice appearing after 24 hours has elevated ______.','blank_answers'=>['bilirubin']],
        ['type'=>'mcq_single','content'=>'The most common cause of bronchiolitis in infants is:','options'=>[['content'=>'Rhinovirus','is_correct'=>false],['content'=>'Respiratory syncytial virus (RSV)','is_correct'=>true],['content'=>'Influenza A only','is_correct'=>false],['content'=>'Streptococcus pneumoniae','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which sign suggests dehydration in a child?','options'=>[['content'=>'Moist mucous membranes','is_correct'=>false],['content'=>'Sunken fontanelle and reduced skin turgor','is_correct'=>true],['content'=>'Bounding pulses','is_correct'=>false],['content'=>'Increased urine output','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Febrile seizures in children aged 6 months to 5 years are usually benign.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Oral rehydration solution (ORS) is used to treat:','options'=>[['content'=>'Hypertension','is_correct'=>false],['content'=>'Mild to moderate dehydration from gastroenteritis','is_correct'=>true],['content'=>'Asthma exacerbation','is_correct'=>false],['content'=>'Diabetic ketoacidosis as sole treatment','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which vaccine prevents Haemophilus influenzae type b meningitis?','options'=>[['content'=>'BCG','is_correct'=>false],['content'=>'Hib conjugate vaccine','is_correct'=>true],['content'=>'Hepatitis A','is_correct'=>false],['content'=>'Rotavirus only','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The WHO growth chart plots weight-for-age and length/height-for-______ in children.','blank_answers'=>['age']],
    ]; }

    private function pharmacologyQs(): array { return [
        ['type'=>'mcq_single','content'=>'Penicillins inhibit bacterial cell wall synthesis by blocking:','explanation'=>'Beta-lactams bind penicillin-binding proteins (transpeptidases).','options'=>[['content'=>'DNA gyrase','is_correct'=>false],['content'=>'Peptidoglycan cross-linking','is_correct'=>true],['content'=>'30S ribosomal subunit','is_correct'=>false],['content'=>'Folate synthesis','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Aminoglycosides are bactericidal and require oxygen for uptake (ineffective against anaerobes).','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which antibiotic class inhibits the 50S ribosomal subunit?','options'=>[['content'=>'Tetracyclines','is_correct'=>false],['content'=>'Macrolides','is_correct'=>true],['content'=>'Aminoglycosides','is_correct'=>false],['content'=>'Fluoroquinolones','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Vancomycin is used primarily against Gram-______ cocci including MRSA.','blank_answers'=>['positive']],
        ['type'=>'mcq_single','content'=>'The main toxicity of gentamicin is:','options'=>[['content'=>'Hepatotoxicity','is_correct'=>false],['content'=>'Nephrotoxicity and ototoxicity','is_correct'=>true],['content'=>'Bone marrow suppression only','is_correct'=>false],['content'=>'Peripheral neuropathy','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Metronidazole is effective against:','options'=>[['content'=>'Gram-positive aerobes only','is_correct'=>false],['content'=>'Anaerobic bacteria and certain protozoa','is_correct'=>true],['content'=>'Mycobacteria','is_correct'=>false],['content'=>'Fungi','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Ciprofloxacin is contraindicated in children due to risk of cartilage damage.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which mechanism describes bacterial beta-lactamase production?','options'=>[['content'=>'Efflux pump','is_correct'=>false],['content'=>'Enzymatic inactivation of antibiotic','is_correct'=>true],['content'=>'Target site mutation only','is_correct'=>false],['content'=>'Decreased permeability only','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Rifampicin discolours body fluids:','options'=>[['content'=>'Blue','is_correct'=>false],['content'=>'Orange-red','is_correct'=>true],['content'=>'Green','is_correct'=>false],['content'=>'Purple','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The combination of amoxicillin and clavulanic acid prevents beta-______ degradation.','blank_answers'=>['lactamase','lactam']],
    ]; }

    private function anatomyQs(): array { return [
        ['type'=>'mcq_single','content'=>'Which chamber of the heart receives oxygenated blood from the lungs?','options'=>[['content'=>'Right atrium','is_correct'=>false],['content'=>'Left atrium','is_correct'=>true],['content'=>'Right ventricle','is_correct'=>false],['content'=>'Left ventricle','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'The aortic valve has three cusps.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The coronary artery that supplies the anterior interventricular septum is the:','options'=>[['content'=>'Right coronary artery','is_correct'=>false],['content'=>'Left anterior descending (LAD) artery','is_correct'=>true],['content'=>'Circumflex artery only','is_correct'=>false],['content'=>'Posterior descending artery always from RCA','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Blood flows from the right ventricle to the lungs via the pulmonary ______.','blank_answers'=>['artery','arteries']],
        ['type'=>'mcq_single','content'=>'The sinoatrial (SA) node is located in the:','options'=>[['content'=>'Interventricular septum','is_correct'=>false],['content'=>'Right atrium near the SVC opening','is_correct'=>true],['content'=>'Left ventricle','is_correct'=>false],['content'=>'Aortic root','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which structure prevents backflow from the left ventricle to the left atrium?','options'=>[['content'=>'Tricuspid valve','is_correct'=>false],['content'=>'Mitral (bicuspid) valve','is_correct'=>true],['content'=>'Pulmonary valve','is_correct'=>false],['content'=>'Aortic valve','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'The bundle of His conducts impulses from the AV node to the ventricles.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The great cardiac vein drains into the:','options'=>[['content'=>'Superior vena cava','is_correct'=>false],['content'=>'Coronary sinus','is_correct'=>true],['content'=>'Pulmonary vein','is_correct'=>false],['content'=>'Inferior vena cava directly','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which layer of the heart wall is composed of cardiac muscle?','options'=>[['content'=>'Epicardium','is_correct'=>false],['content'=>'Myocardium','is_correct'=>true],['content'=>'Endocardium only','is_correct'=>false],['content'=>'Pericardium only','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The fibrous skeleton of the heart separates the atria from the ______.','blank_answers'=>['ventricles','ventricle']],
    ]; }

    private function physiologyQs(): array { return [
        ['type'=>'mcq_single','content'=>'The functional unit of the kidney is the:','options'=>[['content'=>'Glomerulus alone','is_correct'=>false],['content'=>'Nephron','is_correct'=>true],['content'=>'Collecting duct only','is_correct'=>false],['content'=>'Loop of Henle only','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'ADH (vasopressin) increases water reabsorption in the collecting ducts.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Most glucose reabsorption occurs in the:','options'=>[['content'=>'Loop of Henle','is_correct'=>false],['content'=>'Proximal convoluted tubule','is_correct'=>true],['content'=>'Distal convoluted tubule','is_correct'=>false],['content'=>'Collecting duct','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The hormone that promotes sodium reabsorption in the distal nephron is ______.','blank_answers'=>['aldosterone']],
        ['type'=>'mcq_single','content'=>'GFR is primarily determined by:','options'=>[['content'=>'Tubular secretion rate','is_correct'=>false],['content'=>'Glomerular capillary hydrostatic and oncotic pressures','is_correct'=>true],['content'=>'Urine flow rate only','is_correct'=>false],['content'=>'ADH level only','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which part of the nephron is impermeable to water?','options'=>[['content'=>'Proximal tubule','is_correct'=>false],['content'=>'Ascending limb of loop of Henle','is_correct'=>true],['content'=>'Medullary collecting duct with ADH','is_correct'=>false],['content'=>'Descending limb of loop of Henle','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Renin is secreted by juxtaglomerular cells in response to decreased renal perfusion.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The normal range for serum creatinine in adults is approximately:','options'=>[['content'=>'0.1–0.3 mg/dL','is_correct'=>false],['content'=>'0.6–1.2 mg/dL','is_correct'=>true],['content'=>'3.0–5.0 mg/dL','is_correct'=>false],['content'=>'10–15 mg/dL','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Bicarbonate is primarily reabsorbed in the:','options'=>[['content'=>'Proximal tubule','is_correct'=>true],['content'=>'Thin descending limb only','is_correct'=>false],['content'=>'Papilla only','is_correct'=>false],['content'=>'Bowman capsule','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The countercurrent multiplier mechanism concentrates urine in the renal ______.','blank_answers'=>['medulla']],
    ]; }

    private function pathologyQs(): array { return [
        ['type'=>'mcq_single','content'=>'The cardinal signs of acute inflammation include:','options'=>[['content'=>'Rubor, calor, tumor, dolor','is_correct'=>true],['content'=>'Atrophy, metaplasia, dysplasia','is_correct'=>false],['content'=>'Necrosis, apoptosis, autophagy only','is_correct'=>false],['content'=>'Fibrosis, scarring, keloid','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Neutrophils are the predominant cell in acute inflammation.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which type of necrosis is associated with tuberculosis?','options'=>[['content'=>'Coagulative','is_correct'=>false],['content'=>'Caseous','is_correct'=>true],['content'=>'Liquefactive','is_correct'=>false],['content'=>'Fat necrosis','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Spread of malignant cells through lymphatics to regional nodes is called lymphatic ______.','blank_answers'=>['spread','metastasis','metastases']],
        ['type'=>'mcq_single','content'=>'Dysplasia is characterised by:','options'=>[['content'=>'Normal cell size and organisation','is_correct'=>false],['content'=>'Disordered cellular proliferation and atypia','is_correct'=>true],['content'=>'Complete loss of differentiation only','is_correct'=>false],['content'=>'Benign hypertrophy','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The most common site of metastasis for carcinomas is:','options'=>[['content'=>'Brain only','is_correct'=>false],['content'=>'Regional lymph nodes','is_correct'=>true],['content'=>'Skin only','is_correct'=>false],['content'=>'Muscle','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Grading of tumours refers to histological differentiation and aggressiveness.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which immunohistochemical marker is used for epithelial tumours?','options'=>[['content'=>'CD20','is_correct'=>false],['content'=>'Cytokeratin','is_correct'=>true],['content'=>'S100','is_correct'=>false],['content'=>'CD3','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Apoptosis differs from necrosis because it is:','options'=>[['content'=>'Always pathological and inflammatory','is_correct'=>false],['content'=>'Programmed, energy-dependent cell death without inflammation','is_correct'=>true],['content'=>'Always caused by infection','is_correct'=>false],['content'=>'Irreversible membrane rupture first','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Chronic inflammation is characterised by infiltration of ______ and plasma cells.','blank_answers'=>['lymphocytes','macrophages']],
    ]; }

    private function microbiologyQs(): array { return [
        ['type'=>'mcq_single','content'=>'Gram-positive bacteria retain crystal violet because they have a thick:','options'=>[['content'=>'Outer membrane','is_correct'=>false],['content'=>'Peptidoglycan layer','is_correct'=>true],['content'=>'Capsule only','is_correct'=>false],['content'=>'Lipopolysaccharide layer','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Mycobacteria are acid-fast due to mycolic acid in their cell wall.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which stain is used to identify Mycobacterium tuberculosis?','options'=>[['content'=>'Gram stain','is_correct'=>false],['content'=>'Ziehl-Neelsen (acid-fast) stain','is_correct'=>true],['content'=>'Giemsa stain only','is_correct'=>false],['content'=>'India ink','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'Bacteria that require oxygen for growth are called ______.','blank_answers'=>['obligate aerobes','aerobes','obligate aerobic']],
        ['type'=>'mcq_single','content'=>'Staphylococcus aureus is catalase:','options'=>[['content'=>'Negative','is_correct'=>false],['content'=>'Positive','is_correct'=>true],['content'=>'Variable only in MRSA','is_correct'=>false],['content'=>'Not tested','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which organism causes gas gangrene (clostridial myonecrosis)?','options'=>[['content'=>'Clostridium difficile','is_correct'=>false],['content'=>'Clostridium perfringens','is_correct'=>true],['content'=>'Clostridium tetani only in wounds without gas','is_correct'=>false],['content'=>'Bacillus anthracis','is_correct'=>false]]],
        ['type'=>'true_false','content'=>'Endotoxin is a component of the outer membrane of Gram-negative bacteria.','options'=>[['content'=>'True','is_correct'=>true],['content'=>'False','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'The coagulase test distinguishes:','options'=>[['content'=>'S. aureus from S. epidermidis','is_correct'=>true],['content'=>'E. coli from Klebsiella','is_correct'=>false],['content'=>'Streptococcus pyogenes from pneumoniae','is_correct'=>false],['content'=>'Salmonella from Shigella','is_correct'=>false]]],
        ['type'=>'mcq_single','content'=>'Which medium is selective for Gram-negative enteric bacilli?','options'=>[['content'=>'Blood agar','is_correct'=>false],['content'=>'MacConkey agar','is_correct'=>true],['content'=>'Chocolate agar only','is_correct'=>false],['content'=>'Sabouraud dextrose agar','is_correct'=>false]]],
        ['type'=>'fill_blank','content'=>'The toxin responsible for botulism is produced by Clostridium ______.','blank_answers'=>['botulinum']],
    ]; }
}
