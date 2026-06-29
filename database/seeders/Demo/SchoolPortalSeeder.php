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
            ['name' => 'Basic Sciences',       'icon' => '🫀', 'color' => '#7c3aed'],
            ['name' => 'Paraclinical Sciences','icon' => '🔬', 'color' => '#0891b2'],
            ['name' => 'Clinical Sciences',    'icon' => '🩺', 'color' => '#16a34a'],
            ['name' => 'Surgery & Emergency',    'icon' => '⚕️', 'color' => '#b45309'],
            ['name' => 'OB/Gyn & Pediatrics',    'icon' => '👶', 'color' => '#dc2626'],
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
            'Basic Sciences'        => ['Anatomy', 'Physiology', 'Biochemistry', 'Histology'],
            'Paraclinical Sciences' => ['Pathology', 'Pharmacology', 'Microbiology', 'Forensic Medicine'],
            'Clinical Sciences'     => ['Medicine', 'Surgery', 'Community Medicine', 'Radiology'],
            'Surgery & Emergency'   => ['General Surgery', 'Orthopaedics', 'Emergency Medicine', 'Anaesthesia'],
            'OB/Gyn & Pediatrics'   => ['Obstetrics', 'Gynecology', 'Pediatrics', 'Neonatology'],
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
        $anatomyTeacher = User::firstOrCreate(
            ['email' => 'david.anatomy@school.demo'],
            ['name' => 'Dr. David Chen', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $anatomyTeacher->assignRole('lecturer');

        $scienceTeacher = User::firstOrCreate(
            ['email' => 'amara.pathology@school.demo'],
            ['name' => 'Dr. Amara Osei', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $scienceTeacher->assignRole('lecturer');

        $medTeacher = User::firstOrCreate(
            ['email' => 'sarah.medicine@school.demo'],
            ['name' => 'Dr. Sarah Mitchell', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $medTeacher->assignRole('lecturer');

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
        $this->createQuiz($anatomyTeacher, $subs['Anatomy'], 'Upper Limb Anatomy — Bones & Muscles',
            'Test your knowledge of the clavicle, scapula, humerus, and major muscles of the upper limb including innervation.',
            $this->anatomyQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($anatomyTeacher, $subs['Physiology'], 'Cardiovascular Physiology — Pressure & Flow',
            'Covers cardiac output, blood pressure regulation, Starling forces, and the renin-angiotensin system for Year 1 MBBS.',
            $this->physiologyQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($anatomyTeacher, $subs['Biochemistry'], 'Amino Acids, Proteins & Enzymes',
            'Understand essential amino acids, protein structure, enzyme kinetics, and key metabolic pathways.',
            $this->biochemistryQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($scienceTeacher, $subs['Pathology'], 'General Pathology — Cell Injury & Inflammation',
            'Explore reversible and irreversible cell injury, acute and chronic inflammation, and wound healing.',
            $this->pathologyQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($scienceTeacher, $subs['Pharmacology'], 'Antimicrobial Pharmacology',
            'Covers mechanisms of antibiotics, resistance patterns, and clinical use of penicillins, cephalosporins, and aminoglycosides.',
            $this->pharmacologyQuestions(), 'mcq_single', false, 0);

        $this->createQuiz($medTeacher, $subs['Medicine'], 'Internal Medicine — History & Examination',
            'Practice core clinical skills: taking a medical history, systems review, and cardiovascular examination findings.',
            $this->medicineQuestions(), 'mcq_single', false, 0);

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

    private function anatomyQuestions(): array
    {
        return [
            ['q' => 'Which bone forms the anterior part of the shoulder girdle?', 'exp' => 'The clavicle (collarbone) connects the sternum to the scapula and forms the anterior shoulder girdle.',
             'options' => [['text'=>'Scapula','correct'=>false],['text'=>'Clavicle','correct'=>true],['text'=>'Humerus','correct'=>false],['text'=>'Radius','correct'=>false]]],
            ['q' => 'The axillary nerve innervates which muscle?', 'exp' => 'The axillary nerve (C5-C6) supplies the deltoid and teres minor muscles.',
             'options' => [['text'=>'Biceps brachii','correct'=>false],['text'=>'Deltoid','correct'=>true],['text'=>'Triceps','correct'=>false],['text'=>'Brachialis','correct'=>false]]],
            ['q' => 'The median nerve passes through the:', 'exp' => 'The median nerve travels through the carpal tunnel at the wrist.',
             'options' => [['text'=>'Guyon canal','correct'=>false],['text'=>'Carpal tunnel','correct'=>true],['text'=>'Cubital tunnel','correct'=>false],['text'=>'Anatomical snuffbox','correct'=>false]]],
            ['q' => 'Which artery is palpated at the wrist for pulse?', 'exp' => 'The radial artery is commonly used to assess peripheral pulse at the wrist.',
             'options' => [['text'=>'Ulnar artery','correct'=>false],['text'=>'Radial artery','correct'=>true],['text'=>'Brachial artery','correct'=>false],['text'=>'Axillary artery','correct'=>false]]],
            ['q' => 'The rotator cuff consists of how many muscles?', 'exp' => 'The rotator cuff comprises supraspinatus, infraspinatus, teres minor, and subscapularis.',
             'options' => [['text'=>'Two','correct'=>false],['text'=>'Four','correct'=>true],['text'=>'Six','correct'=>false],['text'=>'Eight','correct'=>false]]],
            ['q' => 'Fracture of the scaphoid bone is clinically important because of risk of:', 'exp' => 'Scaphoid fractures may disrupt blood supply via the dorsal carpal branch, leading to avascular necrosis.',
             'options' => [['text'=>'Compartment syndrome only','correct'=>false],['text'=>'Avascular necrosis','correct'=>true],['text'=>'Fat embolism only','correct'=>false],['text'=>'No complications','correct'=>false]]],
            ['q' => 'The brachial plexus roots are derived from spinal segments:', 'exp' => 'The brachial plexus is formed by ventral rami of C5-T1.',
             'options' => [['text'=>'C3-C6','correct'=>false],['text'=>'C5-T1','correct'=>true],['text'=>'C7-T2','correct'=>false],['text'=>'T1-T4','correct'=>false]]],
            ['q' => 'Which vein is commonly used for venepuncture in the antecubital fossa?', 'exp' => 'The median cubital vein crosses the antecubital fossa and is a common site for blood draws.',
             'options' => [['text'=>'Basilic vein only','correct'=>false],['text'=>'Median cubital vein','correct'=>true],['text'=>'Cephalic vein only in foot','correct'=>false],['text'=>'Great saphenous vein','correct'=>false]]],
            ['q' => 'The olecranon process is part of which bone?', 'exp' => 'The olecranon is the proximal end of the ulna forming the point of the elbow.',
             'options' => [['text'=>'Radius','correct'=>false],['text'=>'Ulna','correct'=>true],['text'=>'Humerus','correct'=>false],['text'=>'Scaphoid','correct'=>false]]],
            ['q' => 'Erb-Duchenne palsy typically involves which nerve roots?', 'exp' => 'Erb palsy (waiter tip position) involves C5-C6 injury of the upper trunk.',
             'options' => [['text'=>'C8-T1','correct'=>false],['text'=>'C5-C6','correct'=>true],['text'=>'T1-T2','correct'=>false],['text'=>'C3-C4','correct'=>false]]],
        ];
    }

    private function physiologyQuestions(): array
    {
        return [
            ['q' => 'Cardiac output is calculated as:', 'exp' => 'CO = stroke volume × heart rate (L/min).',
             'options' => [['text'=>'Blood pressure × heart rate','correct'=>false],['text'=>'Stroke volume × heart rate','correct'=>true],['text'=>'Preload × afterload','correct'=>false],['text'=>'MAP × resistance','correct'=>false]]],
            ['q' => 'The Frank-Starling law states that:', 'exp' => 'Increased end-diastolic volume increases stroke volume up to a physiological limit.',
             'options' => [['text'=>'Heart rate determines contractility only','correct'=>false],['text'=>'Increased preload increases stroke volume','correct'=>true],['text'=>'Afterload has no effect on output','correct'=>false],['text'=>'CO is independent of venous return','correct'=>false]]],
            ['q' => 'Which receptor mediates increased heart rate from sympathetic stimulation?', 'exp' => 'Beta-1 adrenergic receptors in the SA node increase pacemaker activity.',
             'options' => [['text'=>'Alpha-1','correct'=>false],['text'=>'Beta-1','correct'=>true],['text'=>'M2 muscarinic','correct'=>false],['text'=>'D2 dopamine','correct'=>false]]],
            ['q' => 'Mean arterial pressure (MAP) approximates:', 'exp' => 'MAP ≈ DBP + 1/3(SBP − DBP).',
             'options' => [['text'=>'SBP + DBP','correct'=>false],['text'=>'DBP + 1/3 pulse pressure','correct'=>true],['text'=>'SBP − DBP','correct'=>false],['text'=>'SBP/DBP ratio','correct'=>false]]],
            ['q' => 'The baroreceptor reflex helps maintain:', 'exp' => 'Carotid and aortic baroreceptors detect pressure changes and adjust HR and vascular tone.',
             'options' => [['text'=>'Blood glucose','correct'=>false],['text'=>'Arterial blood pressure','correct'=>true],['text'=>'Body temperature only','correct'=>false],['text'=>'Plasma osmolality','correct'=>false]]],
            ['q' => 'During exercise, skeletal muscle blood flow increases primarily due to:', 'exp' => 'Local metabolites cause vasodilation (functional hyperaemia) in active muscle.',
             'options' => [['text'=>'Increased sympathetic vasoconstriction everywhere','correct'=>false],['text'=>'Local metabolic vasodilation','correct'=>true],['text'=>'Decreased cardiac output','correct'=>false],['text'=>'Increased blood viscosity','correct'=>false]]],
            ['q' => 'The P wave on ECG represents:', 'exp' => 'P wave = atrial depolarisation.',
             'options' => [['text'=>'Ventricular depolarisation','correct'=>false],['text'=>'Atrial depolarisation','correct'=>true],['text'=>'Ventricular repolarisation','correct'=>false],['text'=>'AV node delay only','correct'=>false]]],
            ['q' => 'Renin is released from the kidney in response to:', 'exp' => 'Decreased renal perfusion, sympathetic stimulation, and decreased NaCl at macula densa trigger renin.',
             'options' => [['text'=>'Increased blood volume only','correct'=>false],['text'=>'Decreased renal perfusion pressure','correct'=>true],['text'=>'Hypernatraemia only','correct'=>false],['text'=>'High atrial stretch only','correct'=>false]]],
            ['q' => 'Systemic vascular resistance is primarily determined by:', 'exp' => 'Arteriolar radius strongly influences resistance (Poiseuille: R ∝ 1/r⁴).',
             'options' => [['text'=>'Venous capacitance only','correct'=>false],['text'=>'Arteriolar radius','correct'=>true],['text'=>'Blood glucose','correct'=>false],['text'=>'Plasma protein only','correct'=>false]]],
            ['q' => 'The QT interval on ECG corresponds to:', 'exp' => 'QT interval covers ventricular depolarisation and repolarisation.',
             'options' => [['text'=>'Atrial systole only','correct'=>false],['text'=>'Ventricular depolarisation and repolarisation','correct'=>true],['text'=>'AV conduction only','correct'=>false],['text'=>'Diastole only','correct'=>false]]],
        ];
    }

    private function biochemistryQuestions(): array
    {
        return [
            ['q' => 'Which amino acid is essential in humans?', 'exp' => 'Essential amino acids cannot be synthesised and must be obtained from diet; lysine is essential.',
             'options' => [['text'=>'Alanine','correct'=>false],['text'=>'Lysine','correct'=>true],['text'=>'Glutamine','correct'=>false],['text'=>'Glycine','correct'=>false]]],
            ['q' => 'The primary structure of a protein refers to:', 'exp' => 'Primary structure is the linear sequence of amino acids.',
             'options' => [['text'=>'Alpha helix arrangement','correct'=>false],['text'=>'Amino acid sequence','correct'=>true],['text'=>'Quaternary subunit assembly','correct'=>false],['text'=>'Disulfide bonds only','correct'=>false]]],
            ['q' => 'Km in enzyme kinetics represents:', 'exp' => 'Km is the substrate concentration at half-maximal velocity, reflecting enzyme-substrate affinity.',
             'options' => [['text'=>'Maximum reaction velocity','correct'=>false],['text'=>'Substrate concentration at half Vmax','correct'=>true],['text'=>'Inhibitor concentration','correct'=>false],['text'=>'Product inhibition constant only','correct'=>false]]],
            ['q' => 'Glycolysis occurs in the:', 'exp' => 'Glycolysis takes place in the cytoplasm, producing pyruvate and ATP.',
             'options' => [['text'=>'Mitochondrial matrix only','correct'=>false],['text'=>'Cytoplasm','correct'=>true],['text'=>'Nucleus','correct'=>false],['text'=>'Peroxisome only','correct'=>false]]],
            ['q' => 'The rate-limiting enzyme of glycolysis is:', 'exp' => 'Phosphofructokinase-1 (PFK-1) is a key regulatory step in glycolysis.',
             'options' => [['text'=>'Hexokinase only always','correct'=>false],['text'=>'Phosphofructokinase-1','correct'=>true],['text'=>'Pyruvate kinase only in fasting','correct'=>false],['text'=>'Aldolase','correct'=>false]]],
            ['q' => 'Competitive inhibitors increase apparent Km because:', 'exp' => 'Competitive inhibitors compete for active site; higher substrate needed to reach Vmax/2.',
             'options' => [['text'=>'They denature the enzyme','correct'=>false],['text'=>'They compete with substrate for the active site','correct'=>true],['text'=>'They bind irreversibly always','correct'=>false],['text'=>'They increase Vmax','correct'=>false]]],
            ['q' => 'The citric acid cycle produces (per acetyl-CoA):', 'exp' => 'One turn yields 3 NADH, 1 FADH2, 1 GTP, and 2 CO2 per acetyl-CoA.',
             'options' => [['text'=>'Only CO2','correct'=>false],['text'=>'NADH, FADH2, and GTP','correct'=>true],['text'=>'Only ATP directly in large amounts','correct'=>false],['text'=>'Lactate','correct'=>false]]],
            ['q' => 'HbA1c reflects average blood glucose over approximately:', 'exp' => 'Glycated haemoglobin reflects glucose control over ~2–3 months.',
             'options' => [['text'=>'1 week','correct'=>false],['text'=>'2–3 months','correct'=>true],['text'=>'24 hours','correct'=>false],['text'=>'1 year','correct'=>false]]],
            ['q' => 'DNA replication is semiconservative meaning:', 'exp' => 'Each new double helix contains one original and one newly synthesised strand.',
             'options' => [['text'=>'Both strands are entirely new','correct'=>false],['text'=>'Each daughter molecule has one old and one new strand','correct'=>true],['text'=>'Only one strand is copied','correct'=>false],['text'=>'RNA replaces DNA','correct'=>false]]],
            ['q' => 'Urea cycle removes excess nitrogen primarily as:', 'exp' => 'The urea cycle converts ammonia to urea for renal excretion.',
             'options' => [['text'=>'Uric acid','correct'=>false],['text'=>'Urea','correct'=>true],['text'=>'Creatinine','correct'=>false],['text'=>'Bilirubin','correct'=>false]]],
        ];
    }

    private function pathologyQuestions(): array
    {
        return [
            ['q' => 'Acute inflammation is characterised histologically by:', 'exp' => 'Neutrophils dominate the early inflammatory infiltrate.',
             'options' => [['text'=>'Plasma cells only','correct'=>false],['text'=>'Neutrophils','correct'=>true],['text'=>'Fibrosis first','correct'=>false],['text'=>'Caseating granulomas always','correct'=>false]]],
            ['q' => 'Reversible cell injury may show:', 'exp' => 'Cellular swelling and fatty change can be reversible if the insult is removed.',
             'options' => [['text'=>'Karyolysis only','correct'=>false],['text'=>'Cellular swelling and fatty change','correct'=>true],['text'=>'Coagulative necrosis always','correct'=>false],['text'=>'Immediate apoptosis only','correct'=>false]]],
            ['q' => 'Caseous necrosis is typical of:', 'exp' => 'TB granulomas show cheese-like (caseous) necrosis.',
             'options' => [['text'=>'Myocardial infarction','correct'=>false],['text'=>'Tuberculosis','correct'=>true],['text'=>'Brain infarct','correct'=>false],['text'=>'Breast fat necrosis only','correct'=>false]]],
            ['q' => 'Metaplasia is defined as:', 'exp' => 'Reversible change of one differentiated cell type to another, often due to chronic irritation.',
             'options' => [['text'=>'Malignant transformation','correct'=>false],['text'=>'Reversible change from one cell type to another','correct'=>true],['text'=>'Decrease in cell size','correct'=>false],['text'=>'Increase in cell number only','correct'=>false]]],
            ['q' => 'Granulation tissue contains:', 'exp' => 'Granulation tissue has new capillaries, fibroblasts, and inflammatory cells during healing.',
             'options' => [['text'=>'Only keratin','correct'=>false],['text'=>'Capillaries, fibroblasts, and inflammatory cells','correct'=>true],['text'=>'Mature scar collagen only','correct'=>false],['text'=>'No vessels','correct'=>false]]],
            ['q' => 'Dysplasia indicates:', 'exp' => 'Disordered growth with cytological atypia; may precede malignancy.',
             'options' => [['text'=>'Normal adaptation','correct'=>false],['text'=>'Disordered pre-neoplastic cellular changes','correct'=>true],['text'=>'Benign hypertrophy only','correct'=>false],['text'=>'Complete healing','correct'=>false]]],
            ['q' => 'Apoptosis differs from necrosis by:', 'exp' => 'Apoptosis is programmed, energy-dependent, and non-inflammatory.',
             'options' => [['text'=>'Membrane rupture and inflammation','correct'=>false],['text'=>'Programmed cell death without inflammation','correct'=>true],['text'=>'Always pathogenic infection required','correct'=>false],['text'=>'Random DNA damage only','correct'=>false]]],
            ['q' => 'An abscess is:', 'exp' => 'A localised collection of pus (neutrophils and debris) in a cavity.',
             'options' => [['text'=>'Generalised vasculitis','correct'=>false],['text'=>'Localised collection of pus','correct'=>true],['text'=>'Benign tumour','correct'=>false],['text'=>'Chronic granuloma only','correct'=>false]]],
            ['q' => 'Staging of cancer refers to:', 'exp' => 'Staging describes extent/spread (TNM); grading describes differentiation.',
             'options' => [['text'=>'Histological grade only','correct'=>false],['text'=>'Extent and spread of disease','correct'=>true],['text'=>'Patient age','correct'=>false],['text'=>'Blood group','correct'=>false]]],
            ['q' => 'Chronic inflammation is dominated by:', 'exp' => 'Macrophages, lymphocytes, and plasma cells characterise chronic inflammation.',
             'options' => [['text'=>'Neutrophils only','correct'=>false],['text'=>'Macrophages and lymphocytes','correct'=>true],['text'=>'Eosinophils only always','correct'=>false],['text'=>'No inflammatory cells','correct'=>false]]],
        ];
    }

    private function pharmacologyQuestions(): array
    {
        return [
            ['q' => 'Penicillins act by inhibiting:', 'exp' => 'Beta-lactams block transpeptidase-mediated peptidoglycan cross-linking.',
             'options' => [['text'=>'DNA gyrase','correct'=>false],['text'=>'Cell wall synthesis','correct'=>true],['text'=>'30S ribosome','correct'=>false],['text'=>'Folate pathway','correct'=>false]]],
            ['q' => 'Gentamicin toxicity includes:', 'exp' => 'Aminoglycosides cause dose-related nephrotoxicity and ototoxicity.',
             'options' => [['text'=>'Hepatic failure only','correct'=>false],['text'=>'Nephrotoxicity and ototoxicity','correct'=>true],['text'=>'Photosensitivity only','correct'=>false],['text'=>'No significant toxicity','correct'=>false]]],
            ['q' => 'MRSA is typically treated with:', 'exp' => 'Vancomycin (or alternatives like linezolid/daptomycin) for serious MRSA infections.',
             'options' => [['text'=>'Amoxicillin alone','correct'=>false],['text'=>'Vancomycin','correct'=>true],['text'=>'Metronidazole alone','correct'=>false],['text'=>'Fluconazole','correct'=>false]]],
            ['q' => 'Macrolides (e.g. azithromycin) bind the:', 'exp' => 'Macrolides inhibit the 50S ribosomal subunit.',
             'options' => [['text'=>'30S subunit','correct'=>false],['text'=>'50S subunit','correct'=>true],['text'=>'DNA gyrase','correct'=>false],['text'=>'Cell wall','correct'=>false]]],
            ['q' => 'Beta-lactamase inhibitors like clavulanate:', 'exp' => 'Clavulanate protects beta-lactams from enzymatic degradation.',
             'options' => [['text'=>'Kill anaerobes directly','correct'=>false],['text'=>'Protect beta-lactams from beta-lactamase','correct'=>true],['text'=>'Increase renal excretion only','correct'=>false],['text'=>'Block protein synthesis','correct'=>false]]],
            ['q' => 'Fluoroquinolones are contraindicated in children because of:', 'exp' => 'Risk of cartilage damage/tendinopathy limits paediatric use.',
             'options' => [['text'=>'Hepatotoxicity only in adults','correct'=>false],['text'=>'Risk of cartilage damage','correct'=>true],['text'=>'No contraindications','correct'=>false],['text'=>'Red man syndrome','correct'=>false]]],
            ['q' => 'Metronidazole is first-line for:', 'exp' => 'Metronidazole covers anaerobes and protozoa (e.g. C. difficile, Giardia).',
             'options' => [['text'=>'Gram-positive cocci only','correct'=>false],['text'=>'Anaerobic infections and certain protozoa','correct'=>true],['text'=>'Viral infections','correct'=>false],['text'=>'Fungal meningitis','correct'=>false]]],
            ['q' => 'Therapeutic index is defined as:', 'exp' => 'TI = TD50/ED50; larger values suggest wider safety margin.',
             'options' => [['text'=>'ED50/TD50','correct'=>false],['text'=>'TD50/ED50','correct'=>true],['text'=>'LD50 only','correct'=>false],['text'=>'Cmax/AUC','correct'=>false]]],
            ['q' => 'Tetracyclines should not be given with milk because:', 'exp' => 'Divalent cations chelate tetracyclines, reducing absorption.',
             'options' => [['text'=>'They cause milk allergy','correct'=>false],['text'=>'Calcium chelation reduces absorption','correct'=>true],['text'=>'They increase milk production','correct'=>false],['text'=>'No interaction exists','correct'=>false]]],
            ['q' => 'Rifampicin induces cytochrome P450, causing:', 'exp' => 'Enzyme induction increases metabolism of many co-administered drugs.',
             'options' => [['text'=>'Decreased metabolism of other drugs','correct'=>false],['text'=>'Increased metabolism of other drugs','correct'=>true],['text'=>'No drug interactions','correct'=>false],['text'=>'Renal failure always','correct'=>false]]],
        ];
    }

    private function medicineQuestions(): array
    {
        return [
            ['q' => 'Which component is NOT part of the standard medical history (SOCRATES applies to pain)?', 'exp' => 'Past medical, drug, family, and social history are core; SOCRATES is for pain characterisation.',
             'options' => [['text'=>'Past medical history','correct'=>false],['text'=>'Patient\'s favourite colour','correct'=>true],['text'=>'Drug history','correct'=>false],['text'=>'Family history','correct'=>false]]],
            ['q' => 'JVP elevation suggests:', 'exp' => 'Raised JVP indicates increased central venous pressure, e.g. right heart failure or fluid overload.',
             'options' => [['text'=>'Hypovolaemia','correct'=>false],['text'=>'Raised central venous pressure','correct'=>true],['text'=>'Normal finding always','correct'=>false],['text'=>'Primary lung disease only','correct'=>false]]],
            ['q' => 'Bilateral pitting oedema may indicate:', 'exp' => 'Systemic causes include heart failure, renal disease, liver disease, and hypoalbuminaemia.',
             'options' => [['text'=>'Local lymphatic obstruction only','correct'=>false],['text'=>'Heart, renal, or liver disease','correct'=>true],['text'=>'Normal ageing only','correct'=>false],['text'=>'Isolated DVT always','correct'=>false]]],
            ['q' => 'A pansystolic murmur at the apex radiating to the axilla suggests:', 'exp' => 'Mitral regurgitation classically causes apical pansystolic murmur radiating to axilla.',
             'options' => [['text'=>'Aortic stenosis','correct'=>false],['text'=>'Mitral regurgitation','correct'=>true],['text'=>'Pulmonary stenosis','correct'=>false],['text'=>'Tricuspid stenosis only','correct'=>false]]],
            ['q' => 'Clubbing of fingers is associated with:', 'exp' => 'Clubbing occurs in chronic hypoxia, IBD, cirrhosis, and congenital heart disease among others.',
             'options' => [['text'=>'Acute viral illness only','correct'=>false],['text'=>'Chronic hypoxia and other systemic diseases','correct'=>true],['text'=>'Iron deficiency only always','correct'=>false],['text'=>'Never clinically significant','correct'=>false]]],
            ['q' => 'Kussmaul breathing indicates:', 'exp' => 'Deep, sighing respirations occur in metabolic acidosis as respiratory compensation.',
             'options' => [['text'=>'Metabolic alkalosis','correct'=>false],['text'=>'Metabolic acidosis','correct'=>true],['text'=>'Pure respiratory alkalosis only','correct'=>false],['text'=>'Normal sleep pattern','correct'=>false]]],
            ['q' => 'The first step in managing an unresponsive patient is to:', 'exp' => 'Assess responsiveness and airway per basic life support algorithms.',
             'options' => [['text'=>'Order CT scan','correct'=>false],['text'=>'Check responsiveness and airway','correct'=>true],['text'=>'Administer antibiotics','correct'=>false],['text'=>'Discharge home','correct'=>false]]],
            ['q' => 'Haemoptysis is defined as:', 'exp' => 'Coughing up blood originating from the lower respiratory tract.',
             'options' => [['text'=>'Blood in stool','correct'=>false],['text'=>'Coughing up blood from the respiratory tract','correct'=>true],['text'=>'Blood in urine','correct'=>false],['text'=>'Nosebleed only','correct'=>false]]],
            ['q' => 'Orthopnoea is a symptom commonly associated with:', 'exp' => 'Difficulty breathing when lying flat suggests pulmonary oedema/heart failure.',
             'options' => [['text'=>'Left ventricular failure','correct'=>true],['text'=>'Appendicitis','correct'=>false],['text'=>'Hypothyroidism only','correct'=>false],['text'=>'Normal variant always','correct'=>false]]],
            ['q' => 'When taking a drug history, it is important to ask about:', 'exp' => 'Include prescription, OTC, herbal, and allergy/intolerance details.',
             'options' => [['text'=>'Prescription drugs only','correct'=>false],['text'=>'Prescription, OTC, and herbal medicines plus allergies','correct'=>true],['text'=>'Vitamins only','correct'=>false],['text'=>'No drug history needed','correct'=>false]]],
        ];
    }
}
