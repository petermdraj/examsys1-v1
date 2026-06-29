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
            ['name' => 'Medicine',           'icon' => '🩺', 'color' => '#7c3aed'],
            ['name' => 'Surgery',            'icon' => '⚕️', 'color' => '#0891b2'],
            ['name' => 'OB/Gyn',             'icon' => '🤰', 'color' => '#ec4899'],
            ['name' => 'Pediatrics',         'icon' => '👶', 'color' => '#16a34a'],
            ['name' => 'Psychiatry',         'icon' => '🧠', 'color' => '#f97316'],
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
            'Medicine'   => ['Cardiology', 'Respiratory Medicine', 'Gastroenterology', 'Endocrinology'],
            'Surgery'    => ['General Surgery', 'Orthopaedics', 'Urology', 'Neurosurgery'],
            'OB/Gyn'     => ['Obstetrics', 'Gynecology', 'Antenatal Care', 'Reproductive Health'],
            'Pediatrics' => ['Neonatology', 'Paediatric Infections', 'Growth & Development', 'Immunisation'],
            'Psychiatry' => ['Mood Disorders', 'Psychosis', 'Anxiety Disorders', 'Substance Use'],
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
        $medLecturer = User::firstOrCreate(
            ['email' => 'alex.medicine@demo.local'],
            ['name' => 'Dr. Alex Rivera', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $medLecturer->assignRole('lecturer');

        $surgLecturer = User::firstOrCreate(
            ['email' => 'natasha.surgery@demo.local'],
            ['name' => 'Dr. Natasha Kowalski', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $surgLecturer->assignRole('lecturer');

        $obgynLecturer = User::firstOrCreate(
            ['email' => 'james.obgyn@demo.local'],
            ['name' => 'Dr. James Okonkwo', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $obgynLecturer->assignRole('lecturer');

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
        $this->createQuiz($medLecturer, $subs['Cardiology'], 'Cardiology Clinical Assessment',
            'Evaluate knowledge of ECG interpretation, heart failure management, arrhythmias, and valvular disease for clinical-year medical students.',
            $this->cardiologyQuestions(), 0);

        $this->createQuiz($obgynLecturer, $subs['Antenatal Care'], 'Obstetrics — Antenatal Care Essentials',
            'Assess understanding of antenatal visits, screening tests, fetal monitoring, and common pregnancy complications.',
            $this->obstetricsQuestions(), 0);

        $this->createQuiz($medLecturer, $subs['Respiratory Medicine'], 'Respiratory Medicine — COPD & Asthma',
            'Tests diagnosis and management of obstructive lung disease, inhaler therapy, and acute exacerbations.',
            $this->respiratoryQuestions(), 0);

        $this->createQuiz($surgLecturer, $subs['General Surgery'], 'General Surgery — Acute Abdomen',
            'Practice questions covering appendicitis, cholecystitis, bowel obstruction, and perioperative care.',
            $this->surgeryQuestions(), 0);

        $this->createQuiz($surgLecturer, $subs['Orthopaedics'], 'Orthopaedics — Fractures & Trauma',
            'Covers fracture classification, initial management, compartment syndrome, and common orthopaedic emergencies.',
            $this->orthopaedicsQuestions(), 0);

        $this->createQuiz($obgynLecturer, $subs['Gynecology'], 'Gynecology — Menstrual & Reproductive Disorders',
            'Covers PCOS, endometriosis, abnormal uterine bleeding, and contraception counselling.',
            $this->gynecologyQuestions(), 0);

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

    private function cardiologyQuestions(): array
    {
        return [
            ['q' => 'Which ECG finding is most suggestive of hyperkalaemia?', 'exp' => 'Peaked T waves are an early sign of hyperkalaemia.',
             'options' => [['text'=>'Peaked T waves','correct'=>true],['text'=>'Prolonged PR only always','correct'=>false],['text'=>'ST elevation in V1-V4 only','correct'=>false],['text'=>'U waves','correct'=>false]]],
            ['q' => 'First-line therapy for stable chronic HFrEF includes:', 'exp' => 'Guidelines recommend ACEi/ARNI, beta-blocker, MRA, and SGLT2 inhibitor in eligible patients.',
             'options' => [['text'=>'ACE inhibitor and evidence-based beta-blocker','correct'=>true],['text'=>'Calcium channel blocker alone','correct'=>false],['text'=>'Digoxin as sole therapy','correct'=>false],['text'=>'Thiazolidinediones','correct'=>false]]],
            ['q' => 'Atrial fibrillation with haemodynamic instability requires:', 'exp' => 'Unstable AF warrants urgent electrical cardioversion.',
             'options' => [['text'=>'Immediate electrical cardioversion','correct'=>true],['text'=>'Outpatient watchful waiting only','correct'=>false],['text'=>'Oral aspirin alone','correct'=>false],['text'=>'No treatment','correct'=>false]]],
            ['q' => 'Troponin elevation is most specific for:', 'exp' => 'Cardiac troponins indicate myocardial injury, especially in ACS.',
             'options' => [['text'=>'Myocardial injury','correct'=>true],['text'=>'Pulmonary embolism only always','correct'=>false],['text'=>'Normal variant in all chest pain','correct'=>false],['text'=>'Anaemia only','correct'=>false]]],
            ['q' => 'A pansystolic murmur at the apex radiating to the axilla indicates:', 'exp' => 'Classic finding of mitral regurgitation.',
             'options' => [['text'=>'Mitral regurgitation','correct'=>true],['text'=>'Aortic stenosis','correct'=>false],['text'=>'PDA','correct'=>false],['text'=>'HOCM','correct'=>false]]],
            ['q' => 'STEMI management includes:', 'exp' => 'Reperfusion (PCI or fibrinolysis where indicated), antiplatelets, anticoagulation per protocol.',
             'options' => [['text'=>'Urgent reperfusion therapy','correct'=>true],['text'=>'Observation only for 72 hours','correct'=>false],['text'=>'Avoid all antiplatelets','correct'=>false],['text'=>'Oral beta-blocker contraindicated always','correct'=>false]]],
            ['q' => 'Which drug reduces mortality post-MI long term?', 'exp' => 'Beta-blockers, ACE inhibitors, statins, and antiplatelets improve outcomes.',
             'options' => [['text'=>'Evidence-based beta-blocker','correct'=>true],['text'=>'Short-acting nifedipine alone','correct'=>false],['text'=>'Routine NSAIDs','correct'=>false],['text'=>'High-dose diuretics alone','correct'=>false]]],
            ['q' => 'In heart failure, BNP is useful to:', 'exp' => 'BNP/NT-proBNP aid diagnosis and prognostication in dyspnoea.',
             'options' => [['text'=>'Differentiate cardiac vs non-cardiac dyspnoea','correct'=>true],['text'=>'Diagnose appendicitis','correct'=>false],['text'=>'Measure liver function','correct'=>false],['text'=>'Replace echocardiography always','correct'=>false]]],
            ['q' => 'Wolff-Parkinson-White syndrome involves:', 'exp' => 'Accessory pathway causes pre-excitation and risk of tachyarrhythmias.',
             'options' => [['text'=>'Accessory AV pathway with delta wave','correct'=>true],['text'=>'Complete heart block only','correct'=>false],['text'=>'Long QT only','correct'=>false],['text'=>'Normal conduction always','correct'=>false]]],
            ['q' => 'Hypertensive emergency is defined by:', 'exp' => 'Severe hypertension with acute end-organ damage requires IV therapy.',
             'options' => [['text'=>'Severe BP with acute end-organ damage','correct'=>true],['text'=>'Any BP >140/90','correct'=>false],['text'=>'White coat effect only','correct'=>false],['text'=>'Isolated headache without BP rise','correct'=>false]]],
        ];
    }

    private function obstetricsQuestions(): array
    {
        return [
            ['q' => 'The recommended number of antenatal contacts in uncomplicated pregnancy (WHO) is:', 'exp' => 'WHO recommends at least 8 contacts.',
             'options' => [['text'=>'8','correct'=>true],['text'=>'2','correct'=>false],['text'=>'15','correct'=>false],['text'=>'1','correct'=>false]]],
            ['q' => 'Gestational diabetes screening is typically performed at:', 'exp' => 'OGTT commonly at 24–28 weeks.',
             'options' => [['text'=>'24–28 weeks','correct'=>true],['text'=>'6 weeks postpartum only','correct'=>false],['text'=>'First trimester only always','correct'=>false],['text'=>'Never in low-risk patients','correct'=>false]]],
            ['q' => 'Preeclampsia includes hypertension and:', 'exp' => 'Proteinuria or end-organ dysfunction after 20 weeks defines preeclampsia.',
             'options' => [['text'=>'Proteinuria or organ dysfunction','correct'=>true],['text'=>'Hyperglycaemia only','correct'=>false],['text'=>'Hypotension','correct'=>false],['text'=>'Normal BP with oedema only','correct'=>false]]],
            ['q' => 'Folic acid supplementation should start:', 'exp' => 'At least 1 month preconception to reduce neural tube defects.',
             'options' => [['text'=>'Before conception','correct'=>true],['text'=>'Only in third trimester','correct'=>false],['text'=>'After delivery only','correct'=>false],['text'=>'Never if diet is adequate always','correct'=>false]]],
            ['q' => 'Normal fetal heart rate is approximately:', 'exp' => 'Baseline FHR 110–160 bpm.',
             'options' => [['text'=>'110–160 bpm','correct'=>true],['text'=>'60–80 bpm','correct'=>false],['text'=>'180–220 bpm','correct'=>false],['text'=>'90–100 bpm always','correct'=>false]]],
            ['q' => 'Which vaccine is recommended in every pregnancy?', 'exp' => 'Influenza (seasonal) and Tdap are routinely recommended.',
             'options' => [['text'=>'Influenza and Tdap','correct'=>true],['text'=>'MMR','correct'=>false],['text'=>'Varicella','correct'=>false],['text'=>'HPV first dose only always','correct'=>false]]],
            ['q' => 'Placenta previa presents with:', 'exp' => 'Painless antepartum bleeding; avoid digital vaginal exam if suspected.',
             'options' => [['text'=>'Painless vaginal bleeding','correct'=>true],['text'=>'Severe abdominal rigidity always','correct'=>false],['text'=>'Fever and purulent discharge only','correct'=>false],['text'=>'No bleeding ever','correct'=>false]]],
            ['q' => 'Ectopic pregnancy is most commonly located in the:', 'exp' => 'Ampulla of fallopian tube is the most common site.',
             'options' => [['text'=>'Fallopian tube','correct'=>true],['text'=>'Ovary always','correct'=>false],['text'=>'Uterine fundus','correct'=>false],['text'=>'Cervix primarily','correct'=>false]]],
            ['q' => 'Group B strep screening is done at:', 'exp' => 'Vaginal-rectal culture at 36–37 weeks in many guidelines.',
             'options' => [['text'=>'36–37 weeks gestation','correct'=>true],['text'=>'Immediately after birth only','correct'=>false],['text'=>'12 weeks only','correct'=>false],['text'=>'Never indicated','correct'=>false]]],
            ['q' => 'Hyperemesis gravidarum may cause:', 'exp' => 'Severe vomiting leads to dehydration, electrolyte disturbances, weight loss.',
             'options' => [['text'=>'Dehydration and electrolyte imbalance','correct'=>true],['text'=>'Permanent fetal malformation always','correct'=>false],['text'=>'No maternal effects','correct'=>false],['text'=>'Hypertension only','correct'=>false]]],
        ];
    }

    private function respiratoryQuestions(): array
    {
        return [
            ['q' => 'First-line maintenance therapy for mild asthma is typically:', 'exp' => 'Low-dose ICS or as-needed ICS-formoterol per guidelines.',
             'options' => [['text'=>'Inhaled corticosteroid','correct'=>true],['text'=>'Oral prednisolone daily long term','correct'=>false],['text'=>'High-flow oxygen only','correct'=>false],['text'=>'Antibiotics prophylaxis always','correct'=>false]]],
            ['q' => 'COPD diagnosis is confirmed by:', 'exp' => 'Post-bronchodilator FEV1/FVC < 0.7 on spirometry.',
             'options' => [['text'=>'Spirometry showing airflow obstruction','correct'=>true],['text'=>'Chest X-ray alone','correct'=>false],['text'=>'Peak flow only','correct'=>false],['text'=>'ABG alone','correct'=>false]]],
            ['q' => 'Acute asthma exacerbation with silent chest suggests:', 'exp' => 'Silent chest indicates severe bronchospasm — life-threatening asthma.',
             'options' => [['text'=>'Life-threatening asthma','correct'=>true],['text'=>'Mild disease','correct'=>false],['text'=>'Resolution of attack','correct'=>false],['text'=>'Normal finding','correct'=>false]]],
            ['q' => 'Long-term oxygen therapy in COPD is indicated if PaO2 is:', 'exp' => 'LTOT if PaO2 ≤ 55 mmHg or SpO2 ≤ 88% when stable.',
             'options' => [['text'=>'≤55 mmHg when stable','correct'=>true],['text'=>'>100 mmHg','correct'=>false],['text'=>'Only during exercise always','correct'=>false],['text'=>'Never indicated','correct'=>false]]],
            ['q' => 'Pneumonia CURB-65 assesses:', 'exp' => 'Severity scoring guides site of care and mortality risk.',
             'options' => [['text'=>'Severity and need for hospitalisation','correct'=>true],['text'=>'Asthma control only','correct'=>false],['text'=>'TB exposure only','correct'=>false],['text'=>'Pulmonary embolism probability only','correct'=>false]]],
            ['q' => 'Pulmonary embolism diagnosis may use:', 'exp' => 'CTPA is commonly used; Wells score guides workup.',
             'options' => [['text'=>'CT pulmonary angiography','correct'=>true],['text'=>'Liver biopsy','correct'=>false],['text'=>'Colonoscopy','correct'=>false],['text'=>'EEG','correct'=>false]]],
            ['q' => 'Type 2 respiratory failure features:', 'exp' => 'Hypoxaemia with hypercapnia (raised PaCO2).',
             'options' => [['text'=>'Hypoxia and hypercapnia','correct'=>true],['text'=>'Hyperoxia only','correct'=>false],['text'=>'Metabolic alkalosis only always','correct'=>false],['text'=>'Normal ABG','correct'=>false]]],
            ['q' => 'Smoking cessation in COPD:', 'exp' => 'Most important intervention to slow progression.',
             'options' => [['text'=>'Slows disease progression','correct'=>true],['text'=>'Has no effect on prognosis','correct'=>false],['text'=>'Contraindicated if on oxygen','correct'=>false],['text'=>'Only helps if age <30','correct'=>false]]],
            ['q' => 'Pleuritic chest pain with haemoptysis suggests:', 'exp' => 'Consider PE, pneumonia, or malignancy depending on context.',
             'options' => [['text'=>'Pulmonary embolism among differentials','correct'=>true],['text'=>'Benign reflux only always','correct'=>false],['text'=>'Anaemia only','correct'=>false],['text'=>'No investigation needed','correct'=>false]]],
            ['q' => 'TB is diagnosed definitively by:', 'exp' => 'Microbiological confirmation (smear/culture/NAAT) from appropriate samples.',
             'options' => [['text'=>'Microbiological confirmation of M. tuberculosis','correct'=>true],['text'=>'Blood glucose only','correct'=>false],['text'=>'Urinalysis','correct'=>false],['text'=>'Skin prick test alone sufficient always','correct'=>false]]],
        ];
    }

    private function surgeryQuestions(): array
    {
        return [
            ['q' => 'Acute appendicitis classically begins with pain in the:', 'exp' => 'Periumbilical pain migrating to RIF is classic.',
             'options' => [['text'=>'Periumbilical region then RIF','correct'=>true],['text'=>'Left shoulder only','correct'=>false],['text'=>'Epigastrium permanently','correct'=>false],['text'=>'No pain','correct'=>false]]],
            ['q' => 'McBurney point tenderness suggests:', 'exp' => 'Located one-third from ASIS to umbilicus — appendicitis sign.',
             'options' => [['text'=>'Appendicitis','correct'=>true],['text'=>'Cholecystitis always','correct'=>false],['text'=>'Diverticulitis always left sided','correct'=>false],['text'=>'Pancreatitis only','correct'=>false]]],
            ['q' => 'Murphy sign is associated with:', 'exp' => 'Inspiratory arrest on RUQ palpation — acute cholecystitis.',
             'options' => [['text'=>'Acute cholecystitis','correct'=>true],['text'=>'Appendicitis','correct'=>false],['text'=>'Renal colic only','correct'=>false],['text'=>'AAA rupture only','correct'=>false]]],
            ['q' => 'Small bowel obstruction may show on AXR:', 'exp' => 'Dilated loops, air-fluid levels, paucity of colonic gas.',
             'options' => [['text'=>'Dilated small bowel loops and air-fluid levels','correct'=>true],['text'=>'Normal film always','correct'=>false],['text'=>'Free air only in all cases','correct'=>false],['text'=>'Pneumoperitoneum excluded if pain mild','correct'=>false]]],
            ['q' => 'Peritonitis presents with:', 'exp' => 'Board-like rigidity, rebound, guarding, systemic toxicity.',
             'options' => [['text'=>'Abdominal rigidity and rebound tenderness','correct'=>true],['text'=>'Painless abdomen always','correct'=>false],['text'=>'Isolated pruritus','correct'=>false],['text'=>'Bradycardia only','correct'=>false]]],
            ['q' => 'Preoperative fasting aims to reduce:', 'exp' => 'Aspiration risk during anaesthesia.',
             'options' => [['text'=>'Aspiration pneumonitis risk','correct'=>true],['text'=>'Wound infection only indirectly always','correct'=>false],['text'=>'Bleeding always','correct'=>false],['text'=>'No clinical purpose','correct'=>false]]],
            ['q' => 'Most common organism in SSI is:', 'exp' => 'S. aureus is the leading cause of surgical site infections.',
             'options' => [['text'=>'Staphylococcus aureus','correct'=>true],['text'=>'E. coli only always','correct'=>false],['text'=>'Candida only','correct'=>false],['text'=>'Viruses primarily','correct'=>false]]],
            ['q' => 'Tension pneumothorax requires:', 'exp' => 'Immediate needle decompression then chest drain.',
             'options' => [['text'=>'Immediate decompression','correct'=>true],['text'=>'Outpatient antibiotics only','correct'=>false],['text'=>'CT before any treatment always','correct'=>false],['text'=>'Observation 24 hours','correct'=>false]]],
            ['q' => 'Acute mesenteric ischaemia presents with:', 'exp' => 'Pain out of proportion to examination findings.',
             'options' => [['text'=>'Pain disproportionate to exam findings','correct'=>true],['text'=>'Painless bleeding only','correct'=>false],['text'=>'Isolated dysuria','correct'=>false],['text'=>'Chronic constipation only','correct'=>false]]],
            ['q' => 'Post-op fever on day 1 is often due to:', 'exp' => 'Atelectasis common early; later consider wound, UTI, pneumonia, DVT/PE.',
             'options' => [['text'=>'Atelectasis','correct'=>true],['text'=>'Anastomotic leak always day 1','correct'=>false],['text'=>'Normal never investigate','correct'=>false],['text'=>'Hyperthyroidism only','correct'=>false]]],
        ];
    }

    private function orthopaedicsQuestions(): array
    {
        return [
            ['q' => 'Colles fracture involves the:', 'exp' => 'Distal radius fracture with dorsal angulation (fall on outstretched hand).',
             'options' => [['text'=>'Distal radius','correct'=>true],['text'=>'Scaphoid only always','correct'=>false],['text'=>'Clavicle midshaft only','correct'=>false],['text'=>'Femoral neck','correct'=>false]]],
            ['q' => 'Compartment syndrome requires:', 'exp' => 'Fasciotomy is definitive treatment — surgical emergency.',
             'options' => [['text'=>'Emergency fasciotomy','correct'=>true],['text'=>'Ice packs only','correct'=>false],['text'=>'Delayed treatment acceptable always','correct'=>false],['text'=>'Oral antibiotics alone','correct'=>false]]],
            ['q' => 'Hip fracture in elderly increases risk of:', 'exp' => 'Mortality and morbidity significant; early surgery improves outcomes.',
             'options' => [['text'=>'Mortality and complications','correct'=>true],['text'=>'No significant impact','correct'=>false],['text'=>'Improved mobility always without surgery','correct'=>false],['text'=>'Only cosmetic issues','correct'=>false]]],
            ['q' => 'Anterior shoulder dislocation is most common after:', 'exp' => 'Abduction and external rotation injury.',
             'options' => [['text'=>'Abduction and external rotation trauma','correct'=>true],['text'=>'Direct posterior blow only always','correct'=>false],['text'=>'Repetitive typing','correct'=>false],['text'=>'Spontaneous without trauma always','correct'=>false]]],
            ['q' => 'Ottawa ankle rules help determine:', 'exp' => 'Need for imaging after ankle/foot injury.',
             'options' => [['text'=>'Need for ankle/foot radiographs','correct'=>true],['text'=>'Knee replacement timing','correct'=>false],['text'=>'Spinal fusion indication','correct'=>false],['text'=>'Cardiac risk only','correct'=>false]]],
            ['q' => 'Open fracture management includes:', 'exp' => 'IV antibiotics, tetanus, urgent orthopaedic review, sterile dressing.',
             'options' => [['text'=>'Antibiotics, tetanus prophylaxis, orthopaedic review','correct'=>true],['text'=>'Wound closure without antibiotics always','correct'=>false],['text'=>'Ignore contamination','correct'=>false],['text'=>'Only oral analgesia','correct'=>false]]],
            ['q' => 'Cauda equina syndrome features include:', 'exp' => 'Saddle anaesthesia, urinary retention, bilateral leg symptoms — emergency.',
             'options' => [['text'=>'Saddle anaesthesia and urinary retention','correct'=>true],['text'=>'Isolated neck pain only','correct'=>false],['text'=>'Mild backache only always','correct'=>false],['text'=>'No urgency','correct'=>false]]],
            ['q' => 'Gout typically affects the:', 'exp' => 'First MTP joint (podagra) classically.',
             'options' => [['text'=>'First metatarsophalangeal joint','correct'=>true],['text'=>'Shoulder only always','correct'=>false],['text'=>'Cervical spine primarily','correct'=>false],['text'=>'No joint predilection','correct'=>false]]],
            ['q' => 'DVT prophylaxis post orthopaedic surgery often uses:', 'exp' => 'LMWH or alternative anticoagulant per protocol.',
             'options' => [['text'=>'Low molecular weight heparin or equivalent','correct'=>true],['text'=>'Aspirin alone always sufficient for all','correct'=>false],['text'=>'No prophylaxis needed ever','correct'=>false],['text'=>'Warfarin loading only always day 1','correct'=>false]]],
            ['q' => 'Greenstick fracture occurs in:', 'exp' => 'Incomplete fracture in paediatric bone.',
             'options' => [['text'=>'Children','correct'=>true],['text'=>'Elderly osteoporotic bone only always','correct'=>false],['text'=>'Never in upper limb','correct'=>false],['text'=>'Only skull','correct'=>false]]],
        ];
    }

    private function gynecologyQuestions(): array
    {
        return [
            ['q' => 'Most common cause of secondary amenorrhoea is:', 'exp' => 'Pregnancy must be excluded first.',
             'options' => [['text'=>'Pregnancy','correct'=>true],['text'=>'Turner syndrome always','correct'=>false],['text'=>'Asherman always first','correct'=>false],['text'=>'Menopause in teens','correct'=>false]]],
            ['q' => 'PCOS is associated with:', 'exp' => 'Hyperandrogenism, oligo-anovulation, polycystic ovaries, insulin resistance.',
             'options' => [['text'=>'Insulin resistance and hyperandrogenism','correct'=>true],['text'=>'Hypothyroidism only always','correct'=>false],['text'=>'Low LH always','correct'=>false],['text'=>'Primary ovarian failure only','correct'=>false]]],
            ['q' => 'Endometriosis classically causes:', 'exp' => 'Cyclical pelvic pain, dysmenorrhoea, dyspareunia, infertility.',
             'options' => [['text'=>'Cyclical pelvic pain and dysmenorrhoea','correct'=>true],['text'=>'Painless amenorrhoea only','correct'=>false],['text'=>'Acute appendicitis always','correct'=>false],['text'=>'No fertility impact','correct'=>false]]],
            ['q' => 'First-line for heavy menstrual bleeding without structural lesion:', 'exp' => 'Levonorgestrel IUD or combined hormonal contraception often first line.',
             'options' => [['text'=>'Levonorgestrel IUD or COC','correct'=>true],['text'=>'Immediate hysterectomy','correct'=>false],['text'=>'Clomiphene','correct'=>false],['text'=>'No treatment needed','correct'=>false]]],
            ['q' => 'Cervical screening detects:', 'exp' => 'Precancerous changes and HPV-related disease.',
             'options' => [['text'=>'Cervical dysplasia and HPV-related disease','correct'=>true],['text'=>'Ovarian cysts directly always','correct'=>false],['text'=>'Endometrial cancer always','correct'=>false],['text'=>'Breast lumps','correct'=>false]]],
            ['q' => 'Bacterial vaginosis is characterised by:', 'exp' => 'Thin grey discharge, fishy odour, clue cells, pH >4.5.',
             'options' => [['text'=>'Thin discharge, fishy odour, clue cells','correct'=>true],['text'=>'Curd-like discharge always','correct'=>false],['text'=>'Green frothy discharge always','correct'=>false],['text'=>'No symptoms ever','correct'=>false]]],
            ['q' => 'Ovarian torsion is a surgical emergency presenting with:', 'exp' => 'Acute severe unilateral pelvic pain, nausea; Doppler ultrasound aids diagnosis.',
             'options' => [['text'=>'Acute severe unilateral pelvic pain','correct'=>true],['text'=>'Painless chronic bloating only','correct'=>false],['text'=>'Bilateral numbness','correct'=>false],['text'=>'Gradual painless amenorrhoea only','correct'=>false]]],
            ['q' => 'Emergency contraception is most effective when taken:', 'exp' => 'As soon as possible after unprotected intercourse.',
             'options' => [['text'=>'As soon as possible after intercourse','correct'=>true],['text'=>'Only if taken 2 weeks later','correct'=>false],['text'=>'Only during menses','correct'=>false],['text'=>'Never effective after 24 hours always','correct'=>false]]],
            ['q' => 'Fibroids (leiomyomas) are:', 'exp' => 'Benign smooth muscle tumours of the uterus.',
             'options' => [['text'=>'Benign uterine smooth muscle tumours','correct'=>true],['text'=>'Malignant always','correct'=>false],['text'=>'Ovarian cysts','correct'=>false],['text'=>'Cervical polyps only','correct'=>false]]],
            ['q' => 'Menopause is defined as:', 'exp' => '12 months of amenorrhoea without other cause, typically reflecting ovarian failure.',
             'options' => [['text'=>'12 months amenorrhoea without other cause','correct'=>true],['text'=>'Single hot flush','correct'=>false],['text'=>'Any irregular cycle in teens','correct'=>false],['text'=>'Age 30 automatically','correct'=>false]]],
        ];
    }
}
