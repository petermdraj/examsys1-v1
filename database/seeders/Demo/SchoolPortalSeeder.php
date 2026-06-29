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

class SchoolPortalSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ────────────────────────────────────────────────────
        $cats = [];
        foreach ([
            ['name' => 'Mathematics',        'icon' => '📐', 'color' => '#7c3aed'],
            ['name' => 'Science',            'icon' => '🔬', 'color' => '#0891b2'],
            ['name' => 'English Language',   'icon' => '📖', 'color' => '#16a34a'],
            ['name' => 'Social Studies',     'icon' => '🌍', 'color' => '#b45309'],
            ['name' => 'Computer Science',   'icon' => '💻', 'color' => '#dc2626'],
        ] as $c) {
            $slug = Str::slug($c['name']) . '-school';
            $cats[$c['name']] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $c['name'], 'icon' => $c['icon'], 'color' => $c['color'], 'is_active' => true, 'sort_order' => 1]
            );
        }

        // Sub-categories
        $subs = [];
        $subDefs = [
            'Mathematics'      => ['Algebra', 'Geometry', 'Arithmetic', 'Statistics'],
            'Science'          => ['Physics', 'Chemistry', 'Biology', 'Environmental Science'],
            'English Language' => ['Grammar', 'Reading Comprehension', 'Vocabulary', 'Writing Skills'],
            'Social Studies'   => ['World History', 'Geography', 'Civics', 'Economics'],
            'Computer Science' => ['Programming Basics', 'Hardware & OS', 'Internet & Networking', 'MS Office'],
        ];
        foreach ($subDefs as $parentName => $children) {
            foreach ($children as $child) {
                $slug = Str::slug($child) . '-school';
                $subs[$child] = Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $child, 'parent_id' => $cats[$parentName]->id, 'is_active' => true, 'sort_order' => 1]
                );
            }
        }

        // ── Lecturers ─────────────────────────────────────────────────────
        // ── Creators (Teachers) ───────────────────────────────────────────
        $mathTeacher = User::firstOrCreate(
            ['email' => 'david.math@school.demo'],
            ['name' => 'Mr. David Chen', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $mathTeacher->assignRole('lecturer');

        $scienceTeacher = User::firstOrCreate(
            ['email' => 'amara.science@school.demo'],
            ['name' => 'Ms. Amara Osei', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $scienceTeacher->assignRole('lecturer');

        $engTeacher = User::firstOrCreate(
            ['email' => 'sarah.english@school.demo'],
            ['name' => 'Mrs. Sarah Mitchell', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $engTeacher->assignRole('lecturer');

        // ── Students ─────────────────────────────────────────
        foreach ([
            ['Emma Taylor',    'emma.t@student.demo'],
            ['Noah Martinez',  'noah@student.demo'],
            ['Chloe Dubois',   'chloe@student.demo'],
            ['Ethan Williams', 'ethan@student.demo'],
            ['Zoe Kim',        'zoe@student.demo'],
            ['Luca Ferrari',   'luca@student.demo'],
        ] as [$name, $email]) {
            $u = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'),
                 'role' => 'student', 'is_active' => true, 'email_verified_at' => now()]
            );
            $u->assignRole('student');
        }

        // ── Quizzes ───────────────────────────────────────────────────────
        // Free tasters — entry points to the platform
        $this->createQuiz($mathTeacher, $subs['Algebra'], 'Grade 9 Algebra — Linear Equations',
            'Test your understanding of linear equations in one and two variables, including word problems and graphical solutions.',
            $this->algebraQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($scienceTeacher, $subs['Physics'], 'Physics — Motion, Force & Energy',
            'Covers Newton\'s laws of motion, types of forces, kinetic and potential energy, and work-energy theorem for Grade 9.',
            $this->physicsQuestions(), 'mcq_single', false, 0);

        // Paid school quizzes
        $this->createQuiz($mathTeacher, $subs['Geometry'], 'Geometry Fundamentals — Lines, Angles & Triangles',
            'Covers properties of lines and angles, types of triangles, congruence, similarity, and the Pythagorean theorem.',
            $this->geometryQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($scienceTeacher, $subs['Biology'], 'Human Body Systems — Grade 8',
            'Explore the major systems of the human body including the digestive, respiratory, circulatory, and nervous systems.',
            $this->biologyQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($scienceTeacher, $subs['Chemistry'], 'Elements, Compounds & Mixtures',
            'Understand the difference between elements, compounds, and mixtures. Covers the periodic table, chemical bonding, and reactions.',
            $this->chemistryQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($engTeacher, $subs['Grammar'], 'English Grammar — Tenses & Parts of Speech',
            'Practice identifying and using the correct tenses and parts of speech in sentences. Ideal for Grade 7–9 students.',
            $this->grammarQuestions(), 'mcq_single', false, 0);

        // ── Theme: Ocean Pro — blue + amber, Sora + DM Sans ─────────────
        $settings = app(\App\Settings\PlatformSettings::class);
        $settings->primary_color   = '#0369a1';
        $settings->accent_color    = '#f59e0b';
        $settings->font_display    = 'Sora';
        $settings->font_primary    = 'DM Sans';
        $settings->font_size_base  = '16px';
        $settings->save();
    }

    private function createQuiz(User $creator, Category $category, string $title, string $desc, array $questions, string $type = 'mcq_single', bool $negativeMarking = false): void
    {
        $quiz = Quiz::create([
            'lecturer_id'              => $creator->id,
            'category_id'             => $category->id,
            'title'                   => $title,
            'slug'                    => Str::slug($title) . '-' . Str::random(4),
            'description'             => $desc,
            'status'                  => 'published',
            'visibility'              => 'public',
            'duration_minutes'        => 20,
            'max_attempts'            => null,
            'pass_percentage'         => 50,
            'shuffle_questions'       => false,
            'shuffle_options'         => true,
            'show_result_immediately' => true,
            'negative_marking_enabled'=> $negativeMarking,
            'certificate_enabled'     => true,
            'total_questions'         => count($questions),
            'total_marks'             => count($questions),
        ]);

        foreach ($questions as $i => $q) {
            $question = Question::create([
                'quiz_id'       => $quiz->id,
                'lecturer_id'    => $creator->id,
                'type'          => $q['type'] ?? $type,
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

    private function algebraQuestions(): array
    {
        return [
            ['q' => 'Solve for x: 3x + 7 = 22', 'exp' => '3x = 22 - 7 = 15, so x = 15/3 = 5.',
             'options' => [['text'=>'x = 4','correct'=>false],['text'=>'x = 5','correct'=>true],['text'=>'x = 6','correct'=>false],['text'=>'x = 7','correct'=>false]]],
            ['q' => 'Which of the following is a linear equation in two variables?', 'exp' => 'A linear equation in two variables is of the form ax + by + c = 0. The equation 2x + 3y = 6 is linear in two variables x and y.',
             'options' => [['text'=>'x² + y = 5','correct'=>false],['text'=>'2x + 3y = 6','correct'=>true],['text'=>'xy = 4','correct'=>false],['text'=>'x² + y² = 9','correct'=>false]]],
            ['q' => 'If 5x – 2(3 – x) = 4, what is x?', 'exp' => '5x – 6 + 2x = 4 → 7x = 10 → x = 10/7.',
             'options' => [['text'=>'x = 1','correct'=>false],['text'=>'x = 10/7','correct'=>true],['text'=>'x = 2','correct'=>false],['text'=>'x = 7/10','correct'=>false]]],
            ['q' => 'The graph of y = 3x + 2 passes through which point?', 'exp' => 'When x=0, y=2. So the y-intercept is (0,2). Check: y=3(0)+2=2. The graph passes through (0,2).',
             'options' => [['text'=>'(0,3)','correct'=>false],['text'=>'(0,2)','correct'=>true],['text'=>'(2,0)','correct'=>false],['text'=>'(1,3)','correct'=>false]]],
            ['q' => 'Factorise: x² + 5x + 6', 'exp' => 'We need two numbers that multiply to 6 and add to 5: those are 2 and 3. So x²+5x+6 = (x+2)(x+3).',
             'options' => [['text'=>'(x+1)(x+6)','correct'=>false],['text'=>'(x+2)(x+3)','correct'=>true],['text'=>'(x–2)(x–3)','correct'=>false],['text'=>'(x+4)(x+2)','correct'=>false]]],
            ['q' => 'The value of the expression 2a + 3b when a = 3 and b = 2 is:', 'exp' => '2(3) + 3(2) = 6 + 6 = 12.',
             'options' => [['text'=>'10','correct'=>false],['text'=>'12','correct'=>true],['text'=>'14','correct'=>false],['text'=>'16','correct'=>false]]],
            ['q' => 'Which property states that a(b + c) = ab + ac?', 'exp' => 'The Distributive Property of multiplication over addition states that a(b+c) = ab + ac.',
             'options' => [['text'=>'Commutative Property','correct'=>false],['text'=>'Associative Property','correct'=>false],['text'=>'Distributive Property','correct'=>true],['text'=>'Identity Property','correct'=>false]]],
            ['q' => 'If y = 2x and x + y = 12, find x.', 'exp' => 'Substituting y = 2x: x + 2x = 12 → 3x = 12 → x = 4.',
             'options' => [['text'=>'3','correct'=>false],['text'=>'4','correct'=>true],['text'=>'6','correct'=>false],['text'=>'8','correct'=>false]]],
            ['q' => 'Simplify: (x + 3)² = ?', 'exp' => '(x+3)² = x² + 2(x)(3) + 3² = x² + 6x + 9.',
             'options' => [['text'=>'x² + 9','correct'=>false],['text'=>'x² + 6x + 9','correct'=>true],['text'=>'x² + 3x + 9','correct'=>false],['text'=>'x² + 6x + 6','correct'=>false]]],
            ['q' => 'The slope of the line y = –4x + 7 is:', 'exp' => 'In slope-intercept form y = mx + c, m is the slope. For y = –4x + 7, slope m = –4.',
             'options' => [['text'=>'7','correct'=>false],['text'=>'4','correct'=>false],['text'=>'–4','correct'=>true],['text'=>'–7','correct'=>false]]],
        ];
    }

    private function geometryQuestions(): array
    {
        return [
            ['q' => 'The sum of all interior angles of a triangle is:', 'exp' => 'The sum of all three interior angles of any triangle is always 180°.',
             'options' => [['text'=>'90°','correct'=>false],['text'=>'180°','correct'=>true],['text'=>'270°','correct'=>false],['text'=>'360°','correct'=>false]]],
            ['q' => 'Two lines that never intersect and are always the same distance apart are called:', 'exp' => 'Parallel lines are lines in the same plane that never intersect and remain equidistant from each other.',
             'options' => [['text'=>'Perpendicular lines','correct'=>false],['text'=>'Parallel lines','correct'=>true],['text'=>'Concurrent lines','correct'=>false],['text'=>'Transversal lines','correct'=>false]]],
            ['q' => 'If two sides of a triangle are 5 cm and 12 cm, and the angle between them is 90°, find the hypotenuse.', 'exp' => 'By Pythagoras theorem: h² = 5² + 12² = 25 + 144 = 169, so h = 13 cm.',
             'options' => [['text'=>'10 cm','correct'=>false],['text'=>'13 cm','correct'=>true],['text'=>'15 cm','correct'=>false],['text'=>'17 cm','correct'=>false]]],
            ['q' => 'An angle greater than 90° but less than 180° is called:', 'exp' => 'An obtuse angle is an angle greater than 90° but less than 180°.',
             'options' => [['text'=>'Acute angle','correct'=>false],['text'=>'Right angle','correct'=>false],['text'=>'Obtuse angle','correct'=>true],['text'=>'Reflex angle','correct'=>false]]],
            ['q' => 'The perimeter of a rectangle with length 12 cm and width 7 cm is:', 'exp' => 'Perimeter of rectangle = 2(l+w) = 2(12+7) = 2×19 = 38 cm.',
             'options' => [['text'=>'19 cm','correct'=>false],['text'=>'84 cm','correct'=>false],['text'=>'38 cm','correct'=>true],['text'=>'26 cm','correct'=>false]]],
            ['q' => 'Vertically opposite angles are always:', 'exp' => 'Vertically opposite angles (formed when two lines intersect) are always equal in measure.',
             'options' => [['text'=>'Supplementary','correct'=>false],['text'=>'Complementary','correct'=>false],['text'=>'Equal','correct'=>true],['text'=>'Unequal','correct'=>false]]],
            ['q' => 'A polygon with 8 sides is called:', 'exp' => 'An octagon is a polygon with eight sides and eight angles.',
             'options' => [['text'=>'Hexagon','correct'=>false],['text'=>'Heptagon','correct'=>false],['text'=>'Octagon','correct'=>true],['text'=>'Nonagon','correct'=>false]]],
            ['q' => 'In an equilateral triangle, each interior angle measures:', 'exp' => 'An equilateral triangle has all sides equal and all angles equal. Since total = 180°, each angle = 180°/3 = 60°.',
             'options' => [['text'=>'45°','correct'=>false],['text'=>'60°','correct'=>true],['text'=>'90°','correct'=>false],['text'=>'120°','correct'=>false]]],
            ['q' => 'The area of a triangle with base 10 cm and height 6 cm is:', 'exp' => 'Area of triangle = ½ × base × height = ½ × 10 × 6 = 30 cm².',
             'options' => [['text'=>'60 cm²','correct'=>false],['text'=>'30 cm²','correct'=>true],['text'=>'16 cm²','correct'=>false],['text'=>'20 cm²','correct'=>false]]],
            ['q' => 'Corresponding angles formed by a transversal cutting two parallel lines are:', 'exp' => 'When a transversal cuts two parallel lines, corresponding angles are in the same position at each intersection and are equal.',
             'options' => [['text'=>'Supplementary','correct'=>false],['text'=>'Unequal','correct'=>false],['text'=>'Equal','correct'=>true],['text'=>'Complementary','correct'=>false]]],
        ];
    }

    private function biologyQuestions(): array
    {
        return [
            ['q' => 'Which organ pumps blood to all parts of the body?', 'exp' => 'The heart is a muscular organ that pumps blood throughout the body via the circulatory system.',
             'options' => [['text'=>'Lung','correct'=>false],['text'=>'Kidney','correct'=>false],['text'=>'Heart','correct'=>true],['text'=>'Liver','correct'=>false]]],
            ['q' => 'The process by which the body breaks down food into nutrients is called:', 'exp' => 'Digestion is the process by which the digestive system breaks down food into simpler nutrients that can be absorbed into the bloodstream.',
             'options' => [['text'=>'Respiration','correct'=>false],['text'=>'Digestion','correct'=>true],['text'=>'Excretion','correct'=>false],['text'=>'Absorption','correct'=>false]]],
            ['q' => 'Which part of the cell contains DNA?', 'exp' => 'DNA (deoxyribonucleic acid) is found in the nucleus of eukaryotic cells, stored in the form of chromosomes.',
             'options' => [['text'=>'Cell membrane','correct'=>false],['text'=>'Cytoplasm','correct'=>false],['text'=>'Nucleus','correct'=>true],['text'=>'Mitochondria','correct'=>false]]],
            ['q' => 'Oxygen is carried in the blood by:', 'exp' => 'Haemoglobin, a protein in red blood cells, binds to oxygen in the lungs and carries it to tissues throughout the body.',
             'options' => [['text'=>'White blood cells','correct'=>false],['text'=>'Haemoglobin in red blood cells','correct'=>true],['text'=>'Platelets','correct'=>false],['text'=>'Plasma','correct'=>false]]],
            ['q' => 'The smallest unit of life is:', 'exp' => 'The cell is the basic structural, functional, and biological unit of all living organisms. It is the smallest unit of life.',
             'options' => [['text'=>'Tissue','correct'=>false],['text'=>'Organ','correct'=>false],['text'=>'Cell','correct'=>true],['text'=>'Molecule','correct'=>false]]],
            ['q' => 'Which gas do we inhale during breathing?', 'exp' => 'We inhale oxygen (O₂) during breathing. The oxygen is used in cellular respiration to produce energy.',
             'options' => [['text'=>'Carbon dioxide','correct'=>false],['text'=>'Nitrogen','correct'=>false],['text'=>'Oxygen','correct'=>true],['text'=>'Hydrogen','correct'=>false]]],
            ['q' => 'The human skeleton has how many bones in an adult?', 'exp' => 'An adult human skeleton has 206 bones. Babies are born with about 270–300 bones, which fuse over time.',
             'options' => [['text'=>'150','correct'=>false],['text'=>'206','correct'=>true],['text'=>'270','correct'=>false],['text'=>'300','correct'=>false]]],
            ['q' => 'Which organ is responsible for filtering blood and producing urine?', 'exp' => 'The kidneys filter blood to remove waste products and excess water, producing urine as a by-product.',
             'options' => [['text'=>'Liver','correct'=>false],['text'=>'Lungs','correct'=>false],['text'=>'Kidney','correct'=>true],['text'=>'Spleen','correct'=>false]]],
            ['q' => 'What is the function of the nervous system?', 'exp' => 'The nervous system controls and coordinates all the activities of the body by transmitting signals between different parts of the body and the brain.',
             'options' => [['text'=>'To pump blood','correct'=>false],['text'=>'To digest food','correct'=>false],['text'=>'To control and coordinate body activities','correct'=>true],['text'=>'To filter waste','correct'=>false]]],
            ['q' => 'Photosynthesis is carried out by plants using:', 'exp' => 'Chlorophyll is the green pigment in plant cells that absorbs sunlight to drive photosynthesis, converting CO₂ and water into glucose and oxygen.',
             'options' => [['text'=>'Haemoglobin','correct'=>false],['text'=>'Chlorophyll','correct'=>true],['text'=>'Melanin','correct'=>false],['text'=>'Keratin','correct'=>false]]],
        ];
    }

    private function chemistryQuestions(): array
    {
        return [
            ['q' => 'Which of the following is a pure substance?', 'exp' => 'Distilled water (H₂O) is a pure substance (compound) with a definite chemical composition. Sea water and air are mixtures.',
             'options' => [['text'=>'Sea water','correct'=>false],['text'=>'Air','correct'=>false],['text'=>'Distilled water','correct'=>true],['text'=>'Soil','correct'=>false]]],
            ['q' => 'The number of protons in an atom is called its:', 'exp' => 'The atomic number of an element equals the number of protons in the nucleus of that atom.',
             'options' => [['text'=>'Atomic mass','correct'=>false],['text'=>'Atomic number','correct'=>true],['text'=>'Mass number','correct'=>false],['text'=>'Valence','correct'=>false]]],
            ['q' => 'Water is a compound because:', 'exp' => 'Water (H₂O) is a compound because it consists of two different elements (hydrogen and oxygen) chemically combined in a fixed ratio.',
             'options' => [['text'=>'It is liquid','correct'=>false],['text'=>'It contains only one type of atom','correct'=>false],['text'=>'It is made of two elements combined chemically','correct'=>true],['text'=>'It can be separated by filtering','correct'=>false]]],
            ['q' => 'Which method is used to separate a mixture of salt and water?', 'exp' => 'Evaporation is used to separate salt from water. When the water is evaporated, the salt remains behind.',
             'options' => [['text'=>'Filtration','correct'=>false],['text'=>'Distillation','correct'=>false],['text'=>'Evaporation','correct'=>true],['text'=>'Magnetic separation','correct'=>false]]],
            ['q' => 'The symbol for Gold in the periodic table is:', 'exp' => 'Gold\'s symbol is Au, derived from the Latin word "Aurum".',
             'options' => [['text'=>'Go','correct'=>false],['text'=>'Gd','correct'=>false],['text'=>'Au','correct'=>true],['text'=>'Ag','correct'=>false]]],
            ['q' => 'An atom of Carbon has 6 protons and 6 neutrons. Its mass number is:', 'exp' => 'Mass number = protons + neutrons = 6 + 6 = 12.',
             'options' => [['text'=>'6','correct'=>false],['text'=>'12','correct'=>true],['text'=>'18','correct'=>false],['text'=>'24','correct'=>false]]],
            ['q' => 'Which of the following is a chemical change?', 'exp' => 'Burning of paper (combustion) is a chemical change because new substances (ash, CO₂, water vapour) are formed and the change is irreversible.',
             'options' => [['text'=>'Melting of ice','correct'=>false],['text'=>'Dissolving sugar in water','correct'=>false],['text'=>'Burning of paper','correct'=>true],['text'=>'Cutting of glass','correct'=>false]]],
            ['q' => 'Which element has the chemical symbol Fe?', 'exp' => 'Fe is the chemical symbol for Iron, derived from the Latin name "Ferrum".',
             'options' => [['text'=>'Fluorine','correct'=>false],['text'=>'Francium','correct'=>false],['text'=>'Iron','correct'=>true],['text'=>'Fermium','correct'=>false]]],
            ['q' => 'The process of converting a liquid to vapour by heating is called:', 'exp' => 'Evaporation is the process by which liquid water (or any liquid) changes into water vapour (gas) when heated.',
             'options' => [['text'=>'Condensation','correct'=>false],['text'=>'Melting','correct'=>false],['text'=>'Evaporation','correct'=>true],['text'=>'Freezing','correct'=>false]]],
            ['q' => 'Hydrogen and oxygen combine to form water in the ratio:', 'exp' => 'Water (H₂O) is always composed of hydrogen and oxygen in the mass ratio 1:8, or the mole ratio 2:1.',
             'options' => [['text'=>'1:1','correct'=>false],['text'=>'1:8','correct'=>true],['text'=>'2:3','correct'=>false],['text'=>'1:4','correct'=>false]]],
        ];
    }

    private function grammarQuestions(): array
    {
        return [
            ['q' => 'Identify the noun in: "The dog chased the ball."', 'exp' => '"Dog" and "ball" are both nouns. "Dog" is the subject noun.',
             'options' => [['text'=>'chased','correct'=>false],['text'=>'dog','correct'=>true],['text'=>'the','correct'=>false],['text'=>'quickly','correct'=>false]]],
            ['q' => 'Which sentence is in the Simple Past tense?', 'exp' => '"She cooked dinner" uses the simple past form "cooked" (past tense of cook).',
             'options' => [['text'=>'She cooks dinner.','correct'=>false],['text'=>'She cooked dinner.','correct'=>true],['text'=>'She is cooking dinner.','correct'=>false],['text'=>'She will cook dinner.','correct'=>false]]],
            ['q' => 'The word "quickly" in the sentence "She ran quickly" is a:', 'exp' => '"Quickly" describes how she ran. A word that modifies a verb, adjective, or another adverb is called an adverb.',
             'options' => [['text'=>'Noun','correct'=>false],['text'=>'Adjective','correct'=>false],['text'=>'Adverb','correct'=>true],['text'=>'Pronoun','correct'=>false]]],
            ['q' => 'Choose the correct article: "___ honest man always tells the truth."', 'exp' => 'We use "An" before words beginning with a vowel sound. "Honest" begins with a silent "H" and sounds like "onest", so we use "An".',
             'options' => [['text'=>'A','correct'=>false],['text'=>'An','correct'=>true],['text'=>'The','correct'=>false],['text'=>'No article needed','correct'=>false]]],
            ['q' => 'Which of the following is a conjunction?', 'exp' => '"Because" is a subordinating conjunction used to connect a dependent clause to a main clause.',
             'options' => [['text'=>'Beautiful','correct'=>false],['text'=>'Quickly','correct'=>false],['text'=>'Because','correct'=>true],['text'=>'On','correct'=>false]]],
            ['q' => 'The plural of "child" is:', 'exp' => 'Child has an irregular plural form: children. It does not follow the standard rule of adding -s or -es.',
             'options' => [['text'=>'Childs','correct'=>false],['text'=>'Childes','correct'=>false],['text'=>'Children','correct'=>true],['text'=>'Childrens','correct'=>false]]],
            ['q' => '"She has been studying for three hours." This sentence is in which tense?', 'exp' => 'The structure "has/have + been + verb-ing" indicates Present Perfect Continuous (Progressive) tense.',
             'options' => [['text'=>'Simple Present','correct'=>false],['text'=>'Present Perfect','correct'=>false],['text'=>'Present Perfect Continuous','correct'=>true],['text'=>'Past Continuous','correct'=>false]]],
            ['q' => 'Choose the correct form: "Neither of the boys ___ ready."', 'exp' => '"Neither" is singular in this construction, so it takes a singular verb. "Neither of the boys is ready."',
             'options' => [['text'=>'are','correct'=>false],['text'=>'were','correct'=>false],['text'=>'is','correct'=>true],['text'=>'have been','correct'=>false]]],
            ['q' => 'A word that replaces a noun is called a:', 'exp' => 'A pronoun is a word used in place of a noun. Examples: he, she, it, they, we, I.',
             'options' => [['text'=>'Adjective','correct'=>false],['text'=>'Pronoun','correct'=>true],['text'=>'Verb','correct'=>false],['text'=>'Adverb','correct'=>false]]],
            ['q' => 'Which sentence uses the correct punctuation?', 'exp' => 'A question must end with a question mark. "What is your name?" is correctly punctuated.',
             'options' => [['text'=>'What is your name.','correct'=>false],['text'=>'What is your name?','correct'=>true],['text'=>'What is your name!','correct'=>false],['text'=>'What is your name,','correct'=>false]]],
        ];
    }

    private function physicsQuestions(): array
    {
        return [
            ['q' => 'Newton\'s First Law of Motion is also known as:', 'exp' => 'Newton\'s First Law states that an object remains at rest or in uniform motion unless acted upon by a net external force. This is the Law of Inertia.',
             'options' => [['text'=>'Law of Acceleration','correct'=>false],['text'=>'Law of Inertia','correct'=>true],['text'=>'Law of Action-Reaction','correct'=>false],['text'=>'Law of Gravitation','correct'=>false]]],
            ['q' => 'The SI unit of force is:', 'exp' => 'The SI unit of force is the Newton (N), named after Sir Isaac Newton. 1 N = 1 kg·m/s².',
             'options' => [['text'=>'Joule','correct'=>false],['text'=>'Watt','correct'=>false],['text'=>'Newton','correct'=>true],['text'=>'Pascal','correct'=>false]]],
            ['q' => 'A body moving at constant speed in a circular path has:', 'exp' => 'Even at constant speed in circular motion, the direction of velocity changes continuously. Hence, the object is accelerating (centripetal acceleration).',
             'options' => [['text'=>'No acceleration','correct'=>false],['text'=>'Centripetal acceleration','correct'=>true],['text'=>'Zero velocity','correct'=>false],['text'=>'Constant velocity','correct'=>false]]],
            ['q' => 'Which type of energy does a stretched rubber band possess?', 'exp' => 'A stretched rubber band stores elastic potential energy — energy stored due to deformation of an elastic object.',
             'options' => [['text'=>'Kinetic energy','correct'=>false],['text'=>'Thermal energy','correct'=>false],['text'=>'Elastic potential energy','correct'=>true],['text'=>'Chemical energy','correct'=>false]]],
            ['q' => 'The formula for calculating work done is:', 'exp' => 'Work done = Force × Displacement × cos(θ), where θ is the angle between force and displacement. When θ=0°, W = F×d.',
             'options' => [['text'=>'W = m × a','correct'=>false],['text'=>'W = F × d','correct'=>true],['text'=>'W = ½mv²','correct'=>false],['text'=>'W = mgh','correct'=>false]]],
            ['q' => 'Sound cannot travel through:', 'exp' => 'Sound is a mechanical wave that requires a medium (solid, liquid, or gas) to travel. It cannot travel through a vacuum.',
             'options' => [['text'=>'Water','correct'=>false],['text'=>'Steel','correct'=>false],['text'=>'Vacuum','correct'=>true],['text'=>'Air','correct'=>false]]],
            ['q' => 'When you drop an object from a height, its potential energy converts to:', 'exp' => 'As an object falls, its height decreases, so gravitational potential energy decreases and converts to kinetic energy.',
             'options' => [['text'=>'Chemical energy','correct'=>false],['text'=>'Thermal energy','correct'=>false],['text'=>'Kinetic energy','correct'=>true],['text'=>'Nuclear energy','correct'=>false]]],
            ['q' => 'Friction is a force that acts:', 'exp' => 'Friction is a contact force that always opposes the relative motion or tendency of motion between two surfaces in contact.',
             'options' => [['text'=>'In the direction of motion','correct'=>false],['text'=>'Perpendicular to motion','correct'=>false],['text'=>'Opposite to the direction of motion','correct'=>true],['text'=>'At an angle to motion','correct'=>false]]],
            ['q' => 'The rate of change of velocity is called:', 'exp' => 'Acceleration is defined as the rate of change of velocity with respect to time. a = (v–u)/t.',
             'options' => [['text'=>'Speed','correct'=>false],['text'=>'Momentum','correct'=>false],['text'=>'Acceleration','correct'=>true],['text'=>'Displacement','correct'=>false]]],
            ['q' => 'Which law states that for every action there is an equal and opposite reaction?', 'exp' => 'Newton\'s Third Law of Motion states that for every action, there is an equal and opposite reaction.',
             'options' => [['text'=>'Newton\'s First Law','correct'=>false],['text'=>'Newton\'s Second Law','correct'=>false],['text'=>'Newton\'s Third Law','correct'=>true],['text'=>'Law of Gravitation','correct'=>false]]],
        ];
    }
}
