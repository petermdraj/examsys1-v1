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

class ProfessionalTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ────────────────────────────────────────────────────
        $cats = [];
        foreach ([
            ['name' => 'Technology',           'icon' => '⚙️', 'color' => '#7c3aed'],
            ['name' => 'Business & Management','icon' => '📊', 'color' => '#0891b2'],
            ['name' => 'Design & Creative',    'icon' => '🎨', 'color' => '#ec4899'],
            ['name' => 'Marketing & Sales',    'icon' => '📣', 'color' => '#f97316'],
            ['name' => 'Human Resources',      'icon' => '👥', 'color' => '#16a34a'],
        ] as $c) {
            $slug = Str::slug($c['name']) . '-pro';
            $cats[$c['name']] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $c['name'], 'icon' => $c['icon'], 'color' => $c['color'], 'is_active' => true, 'sort_order' => 1]
            );
        }

        // Sub-categories
        $subs = [];
        $subDefs = [
            'Technology'            => ['PHP & Laravel', 'JavaScript & Node.js', 'Python', 'DevOps & Cloud', 'Cybersecurity'],
            'Business & Management' => ['Project Management', 'Agile & Scrum', 'Leadership', 'Operations Management'],
            'Design & Creative'     => ['UI/UX Design', 'Graphic Design', 'Product Design'],
            'Marketing & Sales'     => ['Digital Marketing', 'SEO & Content', 'Sales Techniques'],
            'Human Resources'       => ['HR Fundamentals', 'Talent Acquisition', 'Labour Laws'],
        ];
        foreach ($subDefs as $parentName => $children) {
            foreach ($children as $child) {
                $slug = Str::slug($child) . '-pro';
                $subs[$child] = Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $child, 'parent_id' => $cats[$parentName]->id, 'is_active' => true, 'sort_order' => 1]
                );
            }
        }

        // ── Lecturers ─────────────────────────────────────────────────────
        // ── Creators ─────────────────────────────────────────────────────
        $techLecturer = User::firstOrCreate(
            ['email' => 'alex.tech@demo.local'],
            ['name' => 'Alex Rivera', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $techLecturer->assignRole('lecturer');

        $bizLecturer = User::firstOrCreate(
            ['email' => 'natasha.biz@demo.local'],
            ['name' => 'Natasha Kowalski', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $bizLecturer->assignRole('lecturer');

        $hrLecturer = User::firstOrCreate(
            ['email' => 'james.hr@demo.local'],
            ['name' => 'James Okonkwo', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $hrLecturer->assignRole('lecturer');

        // ── Students ────────────────────────────────────────
        foreach ([
            ['Lucas Martin',     'lucas@candidate.demo'],
            ['Priya Kapoor',     'priya.k@candidate.demo'],
            ['David Chen',       'david@candidate.demo'],
            ['Sofia Moreno',     'sofia@candidate.demo'],
            ['Mikhail Petrov',   'mikhail@candidate.demo'],
            ['Fatima Al-Hassan', 'fatima@candidate.demo'],
        ] as [$name, $email]) {
            $u = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'),
                 'role' => 'student', 'is_active' => true, 'email_verified_at' => now()]
            );
            $u->assignRole('student');
        }

        // ── Quizzes ───────────────────────────────────────────────────────
        // Free quizzes (entry-level / tasters)
        $this->createQuiz($techLecturer, $subs['PHP & Laravel'], 'PHP Developer Skills Assessment',
            'Evaluate PHP fundamentals, OOP concepts, design patterns, and Laravel-specific knowledge. Suitable for mid-level backend developer screening.',
            $this->phpQuestions(), 0);

        $this->createQuiz($hrLecturer, $subs['Digital Marketing'], 'Digital Marketing Fundamentals',
            'Assess knowledge of SEO, SEM, social media marketing, email campaigns, content strategy, analytics, and digital advertising.',
            $this->digitalMarketingQuestions(), 0);

        // Paid quizzes
        $this->createQuiz($techLecturer, $subs['JavaScript & Node.js'], 'JavaScript & ES6+ Proficiency Test',
            'Tests modern JavaScript concepts: closures, promises, async/await, prototypes, ES6+ features, and common DOM manipulation patterns.',
            $this->jsQuestions(), 0);

        $this->createQuiz($bizLecturer, $subs['Project Management'], 'Project Management Professional (PMP) Mock Test',
            'Practice questions covering project lifecycle, scope management, risk assessment, stakeholder engagement, and agile methodologies per PMBOK guidelines.',
            $this->pmpQuestions(), 0);

        $this->createQuiz($bizLecturer, $subs['Agile & Scrum'], 'Scrum Master Certification Prep',
            'Covers the Scrum framework: roles, ceremonies, artifacts, sprint planning, retrospectives, and scaling Scrum. Based on the Scrum Guide.',
            $this->scrumQuestions(), 0);

        $this->createQuiz($hrLecturer, $subs['HR Fundamentals'], 'HR Professional Skills Assessment',
            'Covers HR fundamentals including recruitment, onboarding, performance management, compensation, employee relations, and HR compliance.',
            $this->hrQuestions(), 0);

        // ── Theme: Slate Corporate — dark indigo + violet, Space Grotesk + IBM Plex Sans ──
        $settings = app(\App\Settings\PlatformSettings::class);
        $settings->primary_color   = '#1e293b';
        $settings->accent_color    = '#6366f1';
        $settings->font_display    = 'Space Grotesk';
        $settings->font_primary    = 'IBM Plex Sans';
        $settings->font_size_base  = '15px';
        $settings->save();
    }

    private function createQuiz(User $creator, Category $category, string $title, string $desc, array $questions): void
    {
        $quiz = Quiz::create([
            'lecturer_id'              => $creator->id,
            'category_id'             => $category->id,
            'title'                   => $title,
            'slug'                    => Str::slug($title) . '-' . Str::random(4),
            'description'             => $desc,
            'status'                  => 'published',
            'visibility'              => 'public',
            'duration_minutes'        => 45,
            'max_attempts'            => 3,
            'pass_percentage'         => 70,
            'shuffle_questions'       => true,
            'shuffle_options'         => true,
            'show_result_immediately' => true,
            'negative_marking_enabled'=> false,
            'certificate_enabled'     => true,
            'total_questions'         => count($questions),
            'total_marks'             => count($questions),
        ]);

        foreach ($questions as $i => $q) {
            $question = Question::create([
                'quiz_id'       => $quiz->id,
                'lecturer_id'    => $creator->id,
                'type'          => 'mcq_single',
                'content'       => $q['q'],
                'explanation'   => $q['exp'] ?? null,
                'marks'         => 1,
                'negative_marks'=> 0,
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

    private function phpQuestions(): array
    {
        return [
            ['q' => 'Which keyword is used to prevent a class from being inherited in PHP?', 'exp' => 'The "final" keyword in PHP prevents a class from being extended by another class. It can also prevent methods from being overridden.',
             'options' => [['text'=>'abstract','correct'=>false],['text'=>'static','correct'=>false],['text'=>'final','correct'=>true],['text'=>'private','correct'=>false]]],
            ['q' => 'What does the "??" operator do in PHP?', 'exp' => 'The null coalescing operator (??) returns the left operand if it exists and is not null, otherwise returns the right operand.',
             'options' => [['text'=>'Spaceship comparison','correct'=>false],['text'=>'Null coalescing — returns left if not null, else right','correct'=>true],['text'=>'Strict equality check','correct'=>false],['text'=>'Ternary shorthand','correct'=>false]]],
            ['q' => 'In Laravel, which Artisan command creates a new model with migration?', 'exp' => 'php artisan make:model ModelName -m creates both the Eloquent model and its corresponding database migration file.',
             'options' => [['text'=>'php artisan make:model -migration','correct'=>false],['text'=>'php artisan make:model ModelName -m','correct'=>true],['text'=>'php artisan model:create --migrate','correct'=>false],['text'=>'php artisan generate:model -mig','correct'=>false]]],
            ['q' => 'What is the output of: var_dump((int)"42abc");', 'exp' => 'PHP casts the leading numeric characters: "42abc" cast to int gives 42. var_dump outputs int(42).',
             'options' => [['text'=>'string(5) "42abc"','correct'=>false],['text'=>'int(42)','correct'=>true],['text'=>'bool(false)','correct'=>false],['text'=>'NULL','correct'=>false]]],
            ['q' => 'Which design pattern does Laravel\'s Service Container implement?', 'exp' => 'Laravel\'s Service Container is an implementation of the Inversion of Control (IoC) container and uses Dependency Injection pattern.',
             'options' => [['text'=>'Factory Pattern','correct'=>false],['text'=>'Observer Pattern','correct'=>false],['text'=>'Dependency Injection / IoC Container','correct'=>true],['text'=>'Singleton Pattern','correct'=>false]]],
            ['q' => 'In PHP, what is the difference between == and ===?', 'exp' => '== performs loose comparison (type coercion allowed), while === performs strict comparison (type and value must both match).',
             'options' => [['text'=>'No difference','correct'=>false],['text'=>'== is strict, === is loose','correct'=>false],['text'=>'== is loose (type coercion), === is strict (type + value)','correct'=>true],['text'=>'=== only works for objects','correct'=>false]]],
            ['q' => 'What does PSR-4 define?', 'exp' => 'PSR-4 is the PHP Standard Recommendation for autoloading classes from file paths, mapping namespace prefixes to directory paths.',
             'options' => [['text'=>'Coding style guide','correct'=>false],['text'=>'Autoloading standard for classes','correct'=>true],['text'=>'HTTP message interface','correct'=>false],['text'=>'Logging interface','correct'=>false]]],
            ['q' => 'In Laravel, the "hasMany" relationship returns:', 'exp' => 'hasMany returns an Illuminate\\Database\\Eloquent\\Relations\\HasMany instance, representing a one-to-many relationship.',
             'options' => [['text'=>'A single Model instance','correct'=>false],['text'=>'A Collection of related Models','correct'=>true],['text'=>'A BelongsTo instance','correct'=>false],['text'=>'A Builder query','correct'=>false]]],
            ['q' => 'Which PHP function is used to start a session?', 'exp' => 'session_start() must be called before any output is sent to the browser to initiate or resume a session.',
             'options' => [['text'=>'start_session()','correct'=>false],['text'=>'session_start()','correct'=>true],['text'=>'begin_session()','correct'=>false],['text'=>'init_session()','correct'=>false]]],
            ['q' => 'What is a Trait in PHP?', 'exp' => 'A Trait is a mechanism for code reuse in PHP that allows methods to be inserted into classes. It avoids multiple inheritance limitations.',
             'options' => [['text'=>'A type of interface','correct'=>false],['text'=>'An abstract class','correct'=>false],['text'=>'A code reuse mechanism that inserts methods into classes','correct'=>true],['text'=>'A PHP module for type checking','correct'=>false]]],
        ];
    }

    private function jsQuestions(): array
    {
        return [
            ['q' => 'What is the output of: console.log(typeof null);', 'exp' => 'This is a well-known JavaScript bug. typeof null returns "object" even though null is not an object. It has been this way since the first version of JavaScript.',
             'options' => [['text'=>'"null"','correct'=>false],['text'=>'"undefined"','correct'=>false],['text'=>'"object"','correct'=>true],['text'=>'"boolean"','correct'=>false]]],
            ['q' => 'Which of the following correctly creates a Promise that resolves with the value 42?', 'exp' => 'Promise.resolve(42) is the shorthand to create a Promise that is already resolved with the value 42.',
             'options' => [['text'=>'new Promise(42)','correct'=>false],['text'=>'Promise.resolve(42)','correct'=>true],['text'=>'Promise.fulfilled(42)','correct'=>false],['text'=>'new Promise(() => 42)','correct'=>false]]],
            ['q' => 'What does the "..." (spread) operator do in JavaScript?', 'exp' => 'The spread operator (...) expands an iterable (like an array) into individual elements, or spreads object properties into another object.',
             'options' => [['text'=>'Creates a rest parameter','correct'=>false],['text'=>'Expands iterable elements or object properties','correct'=>true],['text'=>'Defines a generator function','correct'=>false],['text'=>'Creates a shallow reference','correct'=>false]]],
            ['q' => 'What is a closure in JavaScript?', 'exp' => 'A closure is a function that retains access to its outer (enclosing) scope even after the outer function has finished executing.',
             'options' => [['text'=>'A function with no return value','correct'=>false],['text'=>'A function that remembers variables from its outer scope','correct'=>true],['text'=>'An immediately invoked function expression','correct'=>false],['text'=>'A function stored in a variable','correct'=>false]]],
            ['q' => 'What is the difference between "let" and "var" in JavaScript?', 'exp' => '"let" is block-scoped (available only within the block it is declared in), while "var" is function-scoped (or globally scoped) and gets hoisted.',
             'options' => [['text'=>'No difference','correct'=>false],['text'=>'"let" is block-scoped; "var" is function-scoped and hoisted','correct'=>true],['text'=>'"var" is block-scoped; "let" is function-scoped','correct'=>false],['text'=>'"let" cannot be reassigned','correct'=>false]]],
            ['q' => 'Which Array method returns a new array with only elements that pass a test?', 'exp' => 'Array.filter() creates a new array with all elements that pass the test implemented by the provided callback function.',
             'options' => [['text'=>'Array.map()','correct'=>false],['text'=>'Array.find()','correct'=>false],['text'=>'Array.filter()','correct'=>true],['text'=>'Array.reduce()','correct'=>false]]],
            ['q' => 'What will the following code output? console.log(1 + "2" + 3);', 'exp' => 'In JS, when + encounters a string, it performs concatenation. 1+"2" = "12" (string concat), then "12"+3 = "123".',
             'options' => [['text'=>'6','correct'=>false],['text'=>'"123"','correct'=>true],['text'=>'"15"','correct'=>false],['text'=>'Error','correct'=>false]]],
            ['q' => 'What is event delegation in JavaScript?', 'exp' => 'Event delegation is a technique where a single event listener is added to a parent element to handle events from multiple child elements, leveraging event bubbling.',
             'options' => [['text'=>'Assigning events directly to each child element','correct'=>false],['text'=>'Listening on a parent to handle events from child elements','correct'=>true],['text'=>'Preventing default browser behaviour','correct'=>false],['text'=>'Using Web Workers for events','correct'=>false]]],
            ['q' => 'What does "async/await" in JavaScript do?', 'exp' => 'async/await is syntactic sugar over Promises that lets you write asynchronous code in a synchronous style. async functions always return a Promise.',
             'options' => [['text'=>'Makes code run on multiple threads','correct'=>false],['text'=>'Syntax for writing asynchronous code in a synchronous style using Promises','correct'=>true],['text'=>'Blocks all code until the function completes','correct'=>false],['text'=>'Creates a new JavaScript runtime context','correct'=>false]]],
            ['q' => 'Which statement about arrow functions is TRUE?', 'exp' => 'Arrow functions do not have their own "this" context — they inherit "this" from the enclosing lexical scope. They also cannot be used as constructors.',
             'options' => [['text'=>'Arrow functions can be used as constructors','correct'=>false],['text'=>'Arrow functions have their own "this" binding','correct'=>false],['text'=>'Arrow functions inherit "this" from the enclosing scope','correct'=>true],['text'=>'Arrow functions cannot access outer variables','correct'=>false]]],
        ];
    }

    private function pmpQuestions(): array
    {
        return [
            ['q' => 'According to PMBOK, which document formally authorizes a project and identifies the project manager?', 'exp' => 'The Project Charter is the document that formally authorizes the existence of a project and gives the project manager authority to apply resources.',
             'options' => [['text'=>'Project Management Plan','correct'=>false],['text'=>'Project Charter','correct'=>true],['text'=>'Statement of Work','correct'=>false],['text'=>'Project Scope Statement','correct'=>false]]],
            ['q' => 'A project has a Budget at Completion (BAC) of $100,000. The Earned Value (EV) is $40,000 and Actual Cost (AC) is $50,000. What is the Cost Performance Index (CPI)?', 'exp' => 'CPI = EV / AC = $40,000 / $50,000 = 0.8. A CPI below 1.0 means the project is over budget.',
             'options' => [['text'=>'1.25','correct'=>false],['text'=>'0.8','correct'=>true],['text'=>'1.0','correct'=>false],['text'=>'0.5','correct'=>false]]],
            ['q' => 'Which risk response strategy involves shifting the negative impact of a risk to a third party?', 'exp' => 'Transfer is a risk response strategy that shifts the impact and ownership of a risk to a third party, such as through insurance or outsourcing.',
             'options' => [['text'=>'Avoid','correct'=>false],['text'=>'Mitigate','correct'=>false],['text'=>'Transfer','correct'=>true],['text'=>'Accept','correct'=>false]]],
            ['q' => 'The Critical Path Method (CPM) identifies:', 'exp' => 'The Critical Path is the longest sequence of dependent tasks that determines the minimum project duration. Activities on the critical path have zero float.',
             'options' => [['text'=>'Tasks with the highest risk','correct'=>false],['text'=>'The sequence of tasks that determines project duration','correct'=>true],['text'=>'The most expensive activities','correct'=>false],['text'=>'Tasks that can be done in parallel','correct'=>false]]],
            ['q' => 'Which communication model describes the components: Sender, Message, Medium, Receiver, Feedback?', 'exp' => 'This is the Interactive Communication Model, which captures the two-way nature of communication and includes feedback as a component.',
             'options' => [['text'=>'Push Communication Model','correct'=>false],['text'=>'Pull Communication Model','correct'=>false],['text'=>'Interactive Communication Model','correct'=>true],['text'=>'Linear Communication Model','correct'=>false]]],
            ['q' => 'Scope creep in project management refers to:', 'exp' => 'Scope creep is the uncontrolled expansion of project scope without adjustments to time, cost, and resources — often due to undocumented changes.',
             'options' => [['text'=>'Reducing project scope to save cost','correct'=>false],['text'=>'Uncontrolled expansion of project scope without adjustments','correct'=>true],['text'=>'Documenting scope changes formally','correct'=>false],['text'=>'Meeting scope requirements on time','correct'=>false]]],
            ['q' => 'During which process group is the Project Management Plan created?', 'exp' => 'The Project Management Plan is developed during the Planning process group, where the project scope, schedule, budget, and plans are defined.',
             'options' => [['text'=>'Initiating','correct'=>false],['text'=>'Planning','correct'=>true],['text'=>'Executing','correct'=>false],['text'=>'Monitoring & Controlling','correct'=>false]]],
            ['q' => 'What is a RACI chart used for in project management?', 'exp' => 'A RACI chart defines roles and responsibilities by indicating who is Responsible, Accountable, Consulted, and Informed for each task.',
             'options' => [['text'=>'Tracking project risks','correct'=>false],['text'=>'Scheduling resources','correct'=>false],['text'=>'Defining roles and responsibilities for tasks','correct'=>true],['text'=>'Managing project communications','correct'=>false]]],
            ['q' => 'Float (or Slack) in project scheduling is:', 'exp' => 'Float is the amount of time an activity can be delayed without delaying the project completion date (total float) or the next activity (free float).',
             'options' => [['text'=>'The total project duration','correct'=>false],['text'=>'Extra budget allocated to tasks','correct'=>false],['text'=>'Time an activity can be delayed without delaying the project','correct'=>true],['text'=>'Parallel activities in the schedule','correct'=>false]]],
            ['q' => 'Which of the following is NOT a characteristic of a project?', 'exp' => 'Projects are temporary and unique endeavours. Repetitive, ongoing operations are NOT projects — they are operational work.',
             'options' => [['text'=>'Temporary duration','correct'=>false],['text'=>'Unique deliverable','correct'=>false],['text'=>'Repetitive and ongoing operations','correct'=>true],['text'=>'Progressive elaboration','correct'=>false]]],
        ];
    }

    private function scrumQuestions(): array
    {
        return [
            ['q' => 'In Scrum, who is responsible for maximizing the value of the product?', 'exp' => 'The Product Owner is responsible for maximizing the value of the product resulting from the Scrum Team\'s work, including managing the Product Backlog.',
             'options' => [['text'=>'Scrum Master','correct'=>false],['text'=>'Development Team','correct'=>false],['text'=>'Product Owner','correct'=>true],['text'=>'Stakeholders','correct'=>false]]],
            ['q' => 'What is the maximum recommended length of a Sprint?', 'exp' => 'According to the Scrum Guide, a Sprint is a time-box of one month or less. The most common Sprint length is two weeks.',
             'options' => [['text'=>'Two weeks','correct'=>false],['text'=>'One month','correct'=>true],['text'=>'Six weeks','correct'=>false],['text'=>'Three months','correct'=>false]]],
            ['q' => 'Which Scrum artefact provides transparency and an opportunity for inspection about work done and future plans?', 'exp' => 'The Sprint Backlog is the set of Product Backlog items selected for the Sprint, plus a plan for delivering the product Increment and realising the Sprint Goal.',
             'options' => [['text'=>'Product Backlog','correct'=>false],['text'=>'Increment','correct'=>false],['text'=>'Sprint Backlog','correct'=>true],['text'=>'Burndown Chart','correct'=>false]]],
            ['q' => 'The Sprint Retrospective is held to:', 'exp' => 'The Sprint Retrospective is an opportunity for the Scrum Team to inspect itself and create a plan for improvements to be enacted during the next Sprint.',
             'options' => [['text'=>'Plan the next Sprint','correct'=>false],['text'=>'Demo the product to stakeholders','correct'=>false],['text'=>'Inspect the team and plan improvements for the next Sprint','correct'=>true],['text'=>'Review backlog priorities','correct'=>false]]],
            ['q' => 'What does "Definition of Done" mean in Scrum?', 'exp' => 'The Definition of Done is a shared understanding of what "done" means — the criteria that a Product Backlog item must meet to be considered complete.',
             'options' => [['text'=>'A list of all tasks to be completed','correct'=>false],['text'=>'The criteria an increment must meet to be considered complete','correct'=>true],['text'=>'The end date of the project','correct'=>false],['text'=>'The acceptance criteria for user stories','correct'=>false]]],
            ['q' => 'Who facilitates the Daily Scrum?', 'exp' => 'The Development Team is responsible for conducting the Daily Scrum. The Scrum Master ensures that the team has the meeting, but does not run it.',
             'options' => [['text'=>'Product Owner','correct'=>false],['text'=>'Scrum Master','correct'=>false],['text'=>'The Development Team','correct'=>true],['text'=>'Project Sponsor','correct'=>false]]],
            ['q' => 'Which statement about the Product Backlog is TRUE?', 'exp' => 'The Product Backlog is an ordered list of everything that might be needed in the product. It is never complete and evolves as the product and market change.',
             'options' => [['text'=>'It is finalized at the start of the project','correct'=>false],['text'=>'It is owned by the Scrum Master','correct'=>false],['text'=>'It is ordered and evolves as the product and environment change','correct'=>true],['text'=>'It can only be updated at Sprint boundaries','correct'=>false]]],
            ['q' => 'How long should a Daily Scrum typically last?', 'exp' => 'The Daily Scrum is a 15-minute time-boxed event for the Development Team to inspect progress toward the Sprint Goal.',
             'options' => [['text'=>'30 minutes','correct'=>false],['text'=>'1 hour','correct'=>false],['text'=>'15 minutes','correct'=>true],['text'=>'As long as needed','correct'=>false]]],
            ['q' => 'What are the three pillars of empiricism in Scrum?', 'exp' => 'Scrum is founded on empirical process control theory, with three pillars: Transparency (visible work), Inspection (detect variances), and Adaptation (adjust processes).',
             'options' => [['text'=>'Planning, Executing, Reviewing','correct'=>false],['text'=>'Transparency, Inspection, Adaptation','correct'=>true],['text'=>'Vision, Roadmap, Delivery','correct'=>false],['text'=>'Velocity, Quality, Teamwork','correct'=>false]]],
            ['q' => 'The Scrum Master\'s role is best described as:', 'exp' => 'The Scrum Master is a servant-leader who helps the team understand and apply Scrum, removes impediments, and coaches the team on self-organisation.',
             'options' => [['text'=>'The manager who assigns tasks to developers','correct'=>false],['text'=>'A servant-leader who coaches the team and removes impediments','correct'=>true],['text'=>'The person responsible for the product vision','correct'=>false],['text'=>'A QA tester who verifies sprint output','correct'=>false]]],
        ];
    }

    private function digitalMarketingQuestions(): array
    {
        return [
            ['q' => 'What does SEO stand for?', 'exp' => 'SEO stands for Search Engine Optimisation — the practice of increasing the quantity and quality of traffic to a website from search engines.',
             'options' => [['text'=>'Social Engagement Optimisation','correct'=>false],['text'=>'Search Engine Optimisation','correct'=>true],['text'=>'Search Engagement Outreach','correct'=>false],['text'=>'Site Experience Output','correct'=>false]]],
            ['q' => 'Which metric measures the percentage of visitors who leave a website after viewing only one page?', 'exp' => 'Bounce rate measures the percentage of visitors who navigate away from the site after viewing only the entry page, without interacting further.',
             'options' => [['text'=>'Click-Through Rate (CTR)','correct'=>false],['text'=>'Conversion Rate','correct'=>false],['text'=>'Bounce Rate','correct'=>true],['text'=>'Impression Rate','correct'=>false]]],
            ['q' => 'In Google Ads, "Quality Score" is based on:', 'exp' => 'Google Ads Quality Score is determined by three factors: Expected CTR, Ad Relevance, and Landing Page Experience. A higher Quality Score lowers your CPC.',
             'options' => [['text'=>'Budget size and bid amount only','correct'=>false],['text'=>'Expected CTR, ad relevance, and landing page experience','correct'=>true],['text'=>'Number of keywords in ad group','correct'=>false],['text'=>'Ad format and creative quality','correct'=>false]]],
            ['q' => 'What is the primary purpose of a buyer persona in marketing?', 'exp' => 'A buyer persona is a semi-fictional representation of an ideal customer, helping marketers tailor messaging and strategies to specific audience segments.',
             'options' => [['text'=>'To create fictional characters for ads','correct'=>false],['text'=>'To represent ideal customers and guide targeted marketing','correct'=>true],['text'=>'To track customer purchase behaviour','correct'=>false],['text'=>'To comply with GDPR regulations','correct'=>false]]],
            ['q' => 'Which of the following is an example of owned media?', 'exp' => 'Owned media is content or channels that a brand controls. A company blog is owned media. Paid ads are paid media; press coverage is earned media.',
             'options' => [['text'=>'A newspaper advertisement','correct'=>false],['text'=>'A press mention in a magazine','correct'=>false],['text'=>'The company\'s own blog','correct'=>true],['text'=>'An influencer\'s sponsored post','correct'=>false]]],
            ['q' => 'What does A/B testing in digital marketing involve?', 'exp' => 'A/B testing (split testing) involves showing two variants of a page, email, or ad to different audience segments to determine which performs better.',
             'options' => [['text'=>'Testing the site on two different browsers','correct'=>false],['text'=>'Comparing two variants to see which performs better','correct'=>true],['text'=>'Running ads on two different platforms','correct'=>false],['text'=>'Testing before and after a campaign launch','correct'=>false]]],
            ['q' => 'Email open rate is calculated as:', 'exp' => 'Open Rate = (Unique Opens / Number of Emails Delivered) × 100%. It measures what percentage of delivered emails were opened.',
             'options' => [['text'=>'(Emails sent / Emails opened) × 100','correct'=>false],['text'=>'(Unique opens / Emails delivered) × 100','correct'=>true],['text'=>'(Clicks / Emails sent) × 100','correct'=>false],['text'=>'(Bounces / Emails sent) × 100','correct'=>false]]],
            ['q' => 'Which social media metric best indicates content resonance with an audience?', 'exp' => 'Engagement rate (likes, comments, shares, saves as a % of reach or followers) best indicates how well content resonates with the audience.',
             'options' => [['text'=>'Number of followers','correct'=>false],['text'=>'Number of impressions','correct'=>false],['text'=>'Engagement rate','correct'=>true],['text'=>'Page views','correct'=>false]]],
            ['q' => 'What is "remarketing" in digital advertising?', 'exp' => 'Remarketing (retargeting) shows ads to people who have previously visited your website or interacted with your content, targeting warm audiences.',
             'options' => [['text'=>'Sending repeated emails to leads','correct'=>false],['text'=>'Advertising to new audiences only','correct'=>false],['text'=>'Showing ads to previous website visitors or engagers','correct'=>true],['text'=>'Re-publishing old content','correct'=>false]]],
            ['q' => 'Which of the following is a key on-page SEO factor?', 'exp' => 'Title tags are a critical on-page SEO element that signals to search engines what the page is about, directly influencing search rankings and CTR.',
             'options' => [['text'=>'Number of backlinks from external sites','correct'=>false],['text'=>'Domain authority score','correct'=>false],['text'=>'Title tags and meta descriptions','correct'=>true],['text'=>'Social media follower count','correct'=>false]]],
        ];
    }

    private function hrQuestions(): array
    {
        return [
            ['q' => 'What is the primary purpose of an employee onboarding programme?', 'exp' => 'Onboarding helps new employees integrate into the organisation, understand their role, and become productive faster while reducing early turnover.',
             'options' => [['text'=>'To conduct performance reviews','correct'=>false],['text'=>'To help new hires integrate and become productive faster','correct'=>true],['text'=>'To handle disciplinary procedures','correct'=>false],['text'=>'To process payroll for new employees','correct'=>false]]],
            ['q' => 'The "halo effect" in performance appraisals refers to:', 'exp' => 'The halo effect occurs when a rater\'s overall positive impression of an employee causes them to rate all aspects positively, even where performance may differ.',
             'options' => [['text'=>'Rating all employees the same','correct'=>false],['text'=>'Bias where one positive trait influences all ratings','correct'=>true],['text'=>'Rating recent performance more heavily','correct'=>false],['text'=>'Comparing employees against each other','correct'=>false]]],
            ['q' => 'Which international framework provides the foundation for fair minimum wage standards globally?', 'exp' => 'The ILO Minimum Wage Fixing Convention (No. 131, 0) establishes international standards for setting minimum wages, covering workers in all sectors globally.',
             'options' => [['text'=>'UN Global Compact','correct'=>false],['text'=>'ILO Minimum Wage Fixing Convention (No. 131)','correct'=>true],['text'=>'ISO 9001 Standard','correct'=>false],['text'=>'OECD Labour Framework','correct'=>false]]],
            ['q' => '360-degree feedback involves collecting feedback from:', 'exp' => '360-degree feedback gathers input from multiple sources including self-assessment, peers, direct reports, supervisors, and sometimes customers.',
             'options' => [['text'=>'Only the direct manager','correct'=>false],['text'=>'The HR department only','correct'=>false],['text'=>'Multiple sources: self, peers, reports, managers, and customers','correct'=>true],['text'=>'External consultants only','correct'=>false]]],
            ['q' => 'What does "attrition rate" measure in HR?', 'exp' => 'Attrition rate measures the percentage of employees who leave the organisation over a given period, both voluntarily and involuntarily.',
             'options' => [['text'=>'Employee satisfaction','correct'=>false],['text'=>'The rate at which employees leave the organisation','correct'=>true],['text'=>'Productivity per employee','correct'=>false],['text'=>'Training completion rate','correct'=>false]]],
            ['q' => 'A competency-based interview focuses on:', 'exp' => 'Competency-based (behavioral) interviews ask candidates to describe past behaviour using the STAR method to predict future job performance.',
             'options' => [['text'=>'Technical knowledge tests','correct'=>false],['text'=>'Past behaviour to predict future performance (STAR method)','correct'=>true],['text'=>'Personality traits only','correct'=>false],['text'=>'Salary negotiation','correct'=>false]]],
            ['q' => 'Employee engagement is best described as:', 'exp' => 'Employee engagement is the level of emotional commitment and involvement employees have towards their organisation and its goals.',
             'options' => [['text'=>'Employee satisfaction with pay','correct'=>false],['text'=>'Number of working hours per week','correct'=>false],['text'=>'Emotional commitment and involvement in organisational goals','correct'=>true],['text'=>'Attendance and punctuality record','correct'=>false]]],
            ['q' => 'Which HR model identifies human capital as a strategic asset tied to business outcomes?', 'exp' => 'The Strategic HRM (SHRM) model positions HR as a strategic partner, aligning human capital management with business goals for competitive advantage.',
             'options' => [['text'=>'Traditional Administrative HR Model','correct'=>false],['text'=>'Strategic HRM Model','correct'=>true],['text'=>'Compliance-first HR Model','correct'=>false],['text'=>'Welfare-focused HR Model','correct'=>false]]],
            ['q' => 'What is the purpose of a Job Analysis?', 'exp' => 'Job analysis is the process of studying a job to determine its duties, responsibilities, required skills, outcomes, and work environment.',
             'options' => [['text'=>'To determine employee pay grades','correct'=>false],['text'=>'To study a job\'s duties, skills, and requirements','correct'=>true],['text'=>'To evaluate individual employee performance','correct'=>false],['text'=>'To design office layouts','correct'=>false]]],
            ['q' => 'Constructive dismissal occurs when:', 'exp' => 'Constructive dismissal occurs when an employer makes working conditions so intolerable that the employee has no choice but to resign.',
             'options' => [['text'=>'An employee is fired for misconduct','correct'=>false],['text'=>'A role is made redundant','correct'=>false],['text'=>'Employer makes conditions so intolerable the employee must resign','correct'=>true],['text'=>'An employee resigns voluntarily','correct'=>false]]],
        ];
    }
}
