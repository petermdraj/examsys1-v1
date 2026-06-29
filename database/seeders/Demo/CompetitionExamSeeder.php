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
            ['name' => 'Medical Licensing Exams', 'icon' => '📝', 'color' => '#4f46e5'],
            ['name' => 'Clinical Sciences',       'icon' => '🩺', 'color' => '#0891b2'],
            ['name' => 'Basic Medical Sciences','icon' => '🔬', 'color' => '#16a34a'],
            ['name' => 'Medical Ethics & Law',    'icon' => '⚖️', 'color' => '#b45309'],
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
            'Medical Licensing Exams' => ['USMLE Step 1', 'PLAB Part 1', 'NEET PG', 'FMGE'],
            'Clinical Sciences'       => ['Internal Medicine', 'Surgery', 'Paediatrics', 'Obstetrics & Gynaecology'],
            'Basic Medical Sciences'  => ['Anatomy', 'Physiology', 'Pathology', 'Pharmacology'],
            'Medical Ethics & Law'    => ['Medical Ethics', 'Consent & Capacity', 'Professional Conduct', 'Medico-legal Cases'],
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

        // ── Lecturers ─────────────────────────────────────────────────────
        // ── Creators ─────────────────────────────────────────────────────
        $lecturer1 = User::firstOrCreate(
            ['email' => 'emma.medicine@demo.local'],
            ['name' => 'Dr. Emma Wilson', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $lecturer1->assignRole('lecturer');

        $lecturer2 = User::firstOrCreate(
            ['email' => 'carlos.surgery@demo.local'],
            ['name' => 'Dr. Carlos Mendoza', 'password' => Hash::make('password'), 'role' => 'lecturer',
             'is_active' => true, 'ai_credits_free_remaining' => 10, 'email_verified_at' => now()]
        );
        $lecturer2->assignRole('lecturer');

        // ── Students ────────────────────────────────────────────────────
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
                 'role' => 'student', 'is_active' => true, 'email_verified_at' => now()]
            );
            $u->assignRole('student');
        }

        // ── Quizzes ──────────────────────────────────────────────────────
        $this->createQuiz($lecturer1, $subs['USMLE Step 1'], 'USMLE Step 1 — Cardiovascular & Renal',
            'High-yield Step 1-style questions covering cardiovascular physiology, pharmacology, and renal pathophysiology for medical licensing exam preparation.',
            $this->usmleQuestions(), 0);

        $this->createQuiz($lecturer1, $subs['PLAB Part 1'], 'PLAB Part 1 — Clinical Medicine',
            'Practice PLAB-style single-best-answer questions on common medical presentations, investigations, and UK clinical guidelines.',
            $this->plabQuestions(), 0);

        $this->createQuiz($lecturer2, $subs['NEET PG'], 'NEET PG — Medicine & Surgery',
            'Targeted NEET PG practice covering internal medicine, general surgery, and high-yield clinical vignettes for postgraduate entrance exams.',
            $this->neetPgQuestions(), 0);

        $this->createQuiz($lecturer2, $subs['Internal Medicine'], 'Clinical Vignettes — Diagnosis & Management',
            'Case-based questions testing diagnostic reasoning and first-line management across common medical emergencies and ward scenarios.',
            $this->clinicalVignetteQuestions(), 0);

        $this->createQuiz($lecturer1, $subs['Pharmacology'], 'Pharmacology — Mechanisms & Adverse Effects',
            'Exam-style pharmacology covering antibiotic classes, cardiovascular drugs, anaesthetic agents, and major drug interactions.',
            $this->examPharmacologyQuestions(), 0);

        $this->createQuiz($lecturer2, $subs['Medical Ethics'], 'Medical Ethics & Professionalism',
            'Questions on informed consent, capacity, confidentiality, duty of candour, and professional conduct for licensing and board exams.',
            $this->medicalEthicsQuestions(), 0);

        // ── Theme: Default — purple + gold ─────────────────────
        $settings = app(\App\Settings\PlatformSettings::class);
        $settings->primary_color   = '#6C2E63';
        $settings->accent_color    = '#E0A431';
        $settings->font_display    = 'Plus Jakarta Sans';
        $settings->font_primary    = 'Inter';
        $settings->font_size_base  = '16px';
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
                'lecturer_id'    => $creator->id,
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

    private function usmleQuestions(): array
    {
        return [
            ['q' => 'A patient with heart failure has elevated BNP. BNP is released primarily from:', 'exp' => 'Ventricular myocytes release BNP in response to wall stress.',
             'options' => [['text'=>'Atrial myocytes only','correct'=>false],['text'=>'Ventricular myocytes','correct'=>true],['text'=>'Adrenal cortex','correct'=>false],['text'=>'Pituitary','correct'=>false]]],
            ['q' => 'Which diuretic acts on the thick ascending limb of the loop of Henle?', 'exp' => 'Loop diuretics (furosemide) inhibit Na-K-2Cl cotransporter.',
             'options' => [['text'=>'Hydrochlorothiazide','correct'=>false],['text'=>'Furosemide','correct'=>true],['text'=>'Spironolactone','correct'=>false],['text'=>'Amiloride','correct'=>false]]],
            ['q' => 'Renin secretion increases when there is:', 'exp' => 'Decreased renal perfusion stimulates juxtaglomerular renin release.',
             'options' => [['text'=>'Decreased renal arterial pressure','correct'=>true],['text'=>'Increased sodium delivery to macula densa only always inhibiting','correct'=>false],['text'=>'Hypervolaemia only','correct'=>false],['text'=>'High atrial stretch only','correct'=>false]]],
            ['q' => 'ACE inhibitors can cause cough due to accumulation of:', 'exp' => 'Bradykinin accumulates when ACE is inhibited.',
             'options' => [['text'=>'Bradykinin','correct'=>true],['text'=>'Angiotensin II','correct'=>false],['text'=>'Aldosterone only','correct'=>false],['text'=>'ADH','correct'=>false]]],
            ['q' => 'The most common cause of aortic stenosis in elderly patients is:', 'exp' => 'Senile calcific aortic stenosis is most common in developed countries.',
             'options' => [['text'=>'Senile calcific degeneration','correct'=>true],['text'=>'Rheumatic fever only always','correct'=>false],['text'=>'Infective endocarditis only','correct'=>false],['text'=>'Marfan syndrome always','correct'=>false]]],
            ['q' => 'Heparin acts by activating:', 'exp' => 'Heparin enhances antithrombin III activity.',
             'options' => [['text'=>'Antithrombin III','correct'=>true],['text'=>'Protein C only directly','correct'=>false],['text'=>'Factor VII','correct'=>false],['text'=>'Plasminogen always','correct'=>false]]],
            ['q' => 'Type 1 RTA is characterised by:', 'exp' => 'Distal RTA — impaired distal acid secretion, hypokalaemia, nephrolithiasis risk.',
             'options' => [['text'=>'Distal tubular acid secretion defect','correct'=>true],['text'=>'Proximal bicarbonate loss only always type 2','correct'=>false],['text'=>'Hyperkalaemia always in all RTA','correct'=>false],['text'=>'Normal anion gap metabolic alkalosis','correct'=>false]]],
            ['q' => 'Statins lower LDL primarily by inhibiting:', 'exp' => 'HMG-CoA reductase is the rate-limiting step in cholesterol synthesis.',
             'options' => [['text'=>'HMG-CoA reductase','correct'=>true],['text'=>'Lipoprotein lipase only','correct'=>false],['text'=>'Cholesterol absorption in gut only','correct'=>false],['text'=>'Bile acid sequestration directly','correct'=>false]]],
            ['q' => 'In STEMI, time to reperfusion affects:', 'exp' => 'Earlier reperfusion preserves myocardium and improves survival.',
             'options' => [['text'=>'Myocardial salvage and mortality','correct'=>true],['text'=>'Only hospital billing','correct'=>false],['text'=>'No clinical outcome','correct'=>false],['text'=>'Renal function only','correct'=>false]]],
            ['q' => 'Digoxin toxicity is increased by:', 'exp' => 'Hypokalaemia increases digoxin binding to Na/K ATPase toxicity.',
             'options' => [['text'=>'Hypokalaemia','correct'=>true],['text'=>'Hyperkalaemia always protective only','correct'=>false],['text'=>'High protein diet only','correct'=>false],['text'=>'Beta-blockers always eliminate toxicity','correct'=>false]]],
        ];
    }

    private function plabQuestions(): array
    {
        return [
            ['q' => 'A 65-year-old with sudden pleuritic chest pain and dyspnoea. Most appropriate initial investigation?', 'exp' => 'Suspected PE — Wells score and D-dimer/CTPA per NICE.',
             'options' => [['text'=>'CT pulmonary angiography if indicated after risk stratification','correct'=>true],['text'=>'Liver biopsy','correct'=>false],['text'=>'Colonoscopy','correct'=>false],['text'=>'No investigation if SpO2 normal always','correct'=>false]]],
            ['q' => 'Best initial management of anaphylaxis includes:', 'exp' => 'IM adrenaline (epinephrine) is first-line; lie flat, airway, oxygen.',
             'options' => [['text'=>'Intramuscular adrenaline','correct'=>true],['text'=>'Oral antihistamine alone','correct'=>false],['text'=>'IV beta-blocker','correct'=>false],['text'=>'Observation only','correct'=>false]]],
            ['q' => 'A patient lacks capacity. Who decides best interests treatment in UK?', 'exp' => 'Clinicians decide in patient best interests using MCA principles if LPA not applicable.',
             'options' => [['text'=>'Clinical team in patient\'s best interests per MCA','correct'=>true],['text'=>'Any family member always overrides','correct'=>false],['text'=>'Police','correct'=>false],['text'=>'No treatment permitted','correct'=>false]]],
            ['q' => 'First-line treatment for community-acquired pneumonia (low severity) often includes:', 'exp' => 'Amoxicillin commonly first line in UK guidelines for low CURB-65.',
             'options' => [['text'=>'Amoxicillin','correct'=>true],['text'=>'Metronidazole alone always','correct'=>false],['text'=>'Antivirals only','correct'=>false],['text'=>'No antibiotics ever outpatient','correct'=>false]]],
            ['q' => 'Diabetic ketoacidosis initial management includes:', 'exp' => 'IV fluids, insulin infusion, potassium monitoring, treat precipitant.',
             'options' => [['text'=>'IV fluids and fixed-rate insulin infusion','correct'=>true],['text'=>'Subcutaneous insulin only and discharge','correct'=>false],['text'=>'Oral metformin loading','correct'=>false],['text'=>'High-dose sodium bicarbonate always routine','correct'=>false]]],
            ['q' => 'Child with non-blanching rash and fever requires:', 'exp' => 'Consider meningococcal disease — urgent assessment and antibiotics.',
             'options' => [['text'=>'Urgent senior review and possible IV antibiotics','correct'=>true],['text'=>'Paracetamol and home if smiling','correct'=>false],['text'=>'Topical cream only','correct'=>false],['text'=>'Delayed review 1 week','correct'=>false]]],
            ['q' => 'GCS eye opening to pain scores:', 'exp' => 'Eye opening to pain = 2 on GCS.',
             'options' => [['text'=>'2','correct'=>true],['text'=>'3','correct'=>false],['text'=>'4','correct'=>false],['text'=>'1 only if none','correct'=>false]]],
            ['q' => 'Most sensitive marker for pancreatitis is:', 'exp' => 'Serum lipase (or amylase) elevated in acute pancreatitis.',
             'options' => [['text'=>'Lipase','correct'=>true],['text'=>'Troponin only always','correct'=>false],['text'=>'CRP alone diagnostic','correct'=>false],['text'=>'Bilirubin only','correct'=>false]]],
            ['q' => 'Testosterone deficiency in adult male may cause:', 'exp' => 'Reduced libido, fatigue, decreased muscle mass, anaemia.',
             'options' => [['text'=>'Reduced libido and fatigue','correct'=>true],['text'=>'Polyuria only always','correct'=>false],['text'=>'Hyperreflexia only','correct'=>false],['text'=>'No symptoms ever','correct'=>false]]],
            ['q' => 'Safeguarding concern in vulnerable adult requires:', 'exp' => 'Follow local safeguarding policy — document, escalate to safeguarding lead.',
             'options' => [['text'=>'Escalation per local safeguarding procedures','correct'=>true],['text'=>'Ignore if patient declines','correct'=>false],['text'=>'Post on social media','correct'=>false],['text'=>'Discharge without documentation','correct'=>false]]],
        ];
    }

    private function neetPgQuestions(): array
    {
        return [
            ['q' => 'Most common cause of community-acquired pneumonia in adults is:', 'exp' => 'Streptococcus pneumoniae is the most common bacterial cause.',
             'options' => [['text'=>'Streptococcus pneumoniae','correct'=>true],['text'=>'Mycobacterium tuberculosis always','correct'=>false],['text'=>'Legionella only','correct'=>false],['text'=>'H. pylori','correct'=>false]]],
            ['q' => 'Cushing syndrome features include:', 'exp' => 'Moon facies, central obesity, striae, hypertension, hyperglycaemia.',
             'options' => [['text'=>'Central obesity, moon facies, striae','correct'=>true],['text'=>'Weight loss and hyperpigmentation only always','correct'=>false],['text'=>'Hypotension only','correct'=>false],['text'=>'Bradycardia always','correct'=>false]]],
            ['q' => 'Acute otitis media in children first-line antibiotic often:', 'exp' => 'Amoxicillin commonly used if antibiotics indicated.',
             'options' => [['text'=>'Amoxicillin','correct'=>true],['text'=>'Vancomycin IV always first','correct'=>false],['text'=>'Fluconazole','correct'=>false],['text'=>'No antibiotics ever','correct'=>false]]],
            ['q' => 'Wilson disease involves accumulation of:', 'exp' => 'Copper accumulates due to defective ceruloplasmin/biliary excretion.',
             'options' => [['text'=>'Copper','correct'=>true],['text'=>'Iron only always haemochromatosis','correct'=>false],['text'=>'Lead','correct'=>false],['text'=>'Calcium','correct'=>false]]],
            ['q' => 'Most common thyroid malignancy is:', 'exp' => 'Papillary carcinoma is the most common thyroid cancer.',
             'options' => [['text'=>'Papillary carcinoma','correct'=>true],['text'=>'Anaplastic always most common','correct'=>false],['text'=>'Medullary only in all regions','correct'=>false],['text'=>'Lymphoma primarily','correct'=>false]]],
            ['q' => 'Hirschsprung disease involves absence of ganglion cells in:', 'exp' => 'Aganglionosis of distal colon causes functional obstruction in neonates.',
             'options' => [['text'=>'Distal colon rectum','correct'=>true],['text'=>'Duodenum only always','correct'=>false],['text'=>'Stomach primarily','correct'=>false],['text'=>'Oesophagus','correct'=>false]]],
            ['q' => 'First investigation for suspected pulmonary TB is often:', 'exp' => 'Chest X-ray and sputum smear/culture/NAAT.',
             'options' => [['text'=>'Chest radiograph and sputum microbiology','correct'=>true],['text'=>'ECG only','correct'=>false],['text'=>'Upper GI endoscopy','correct'=>false],['text'=>'Bone scan only','correct'=>false]]],
            ['q' => 'Tension pneumothorax immediate treatment:', 'exp' => 'Needle decompression then chest drain.',
             'options' => [['text'=>'Needle thoracostomy/decompression','correct'=>true],['text'=>'Wait for CT','correct'=>false],['text'=>'High-dose steroids only','correct'=>false],['text'=>'Pericardiocentesis','correct'=>false]]],
            ['q' => 'Mechanism of action of omeprazole is:', 'exp' => 'Proton pump inhibitors block H+/K+ ATPase in parietal cells.',
             'options' => [['text'=>'Proton pump inhibition','correct'=>true],['text'=>'H2 receptor blockade only always','correct'=>false],['text'=>'Antacid neutralisation','correct'=>false],['text'=>'Mucosal prostaglandin only','correct'=>false]]],
            ['q' => 'Classic triad of normal pressure hydrocephalus:', 'exp' => 'Wet, wacky, wobbly — urinary incontinence, dementia, gait apraxia.',
             'options' => [['text'=>'Gait disturbance, dementia, urinary incontinence','correct'=>true],['text'=>'Fever, rash, joint pain','correct'=>false],['text'=>'Chest pain, dyspnoea, syncope only always','correct'=>false],['text'=>'Jaundice, ascites, spider naevi only','correct'=>false]]],
        ];
    }

    private function clinicalVignetteQuestions(): array
    {
        return [
            ['q' => 'A 55-year-old smoker with weight loss and haemoptysis. Next step?', 'exp' => 'Urgent chest imaging and referral pathway for suspected lung cancer.',
             'options' => [['text'=>'Urgent chest X-ray/CT and cancer pathway referral','correct'=>true],['text'=>'Reassurance only','correct'=>false],['text'=>'Antibiotics 4 weeks before any imaging always','correct'=>false],['text'=>'Pelvic ultrasound first','correct'=>false]]],
            ['q' => 'A diabetic patient with fruity breath and Kussmaul respiration has:', 'exp' => 'DKA — check glucose, ketones, blood gas, start IV fluids and insulin.',
             'options' => [['text'=>'Diabetic ketoacidosis','correct'=>true],['text'=>'Hyperosmolar hyperglycaemic state only always with no ketones','correct'=>false],['text'=>'Simple hypoglycaemia','correct'=>false],['text'=>'Normal variation','correct'=>false]]],
            ['q' => 'Elderly patient on warfarin with head injury after fall requires:', 'exp' => 'Low threshold for CT head; reverse anticoagulation if intracranial bleed.',
             'options' => [['text'=>'Urgent CT head and coagulation management','correct'=>true],['text'=>'Discharge if GCS 15 always without imaging','correct'=>false],['text'=>'Increase warfarin dose','correct'=>false],['text'=>'No assessment needed','correct'=>false]]],
            ['q' => 'Young woman with sudden severe unilateral pelvic pain mid-cycle may have:', 'exp' => 'Consider ovarian torsion, ruptured cyst, ectopic if pregnant.',
             'options' => [['text'=>'Ovarian torsion or ruptured ovarian cyst among differentials','correct'=>true],['text'=>'Benign condition never needs imaging always','correct'=>false],['text'=>'Appendicitis excluded if left sided always','correct'=>false],['text'=>'No pregnancy test needed ever','correct'=>false]]],
            ['q' => 'Patient with rigid abdomen and rebound after blunt trauma:', 'exp' => 'Suspect haemoperitoneum/organ injury — ATLS approach, urgent surgery consult.',
             'options' => [['text'=>'Acute abdomen — urgent surgical assessment','correct'=>true],['text'=>'Outpatient follow-up only','correct'=>false],['text'=>'Oral analgesia and discharge','correct'=>false],['text'=>'Ignore if BP normal always','correct'=>false]]],
            ['q' => 'Child with barking cough and stridor at rest may need:', 'exp' => 'Severe croup/epiglottitis differential — nebulised adrenaline/steroids, senior review.',
             'options' => [['text'=>'Urgent paediatric assessment and airway management','correct'=>true],['text'=>'Honey only at home always sufficient if stridor','correct'=>false],['text'=>'Antibiotics alone always viral','correct'=>false],['text'=>'No treatment','correct'=>false]]],
            ['q' => 'Alcoholic with confusion, ataxia, ophthalmoplegia — treat with:', 'exp' => 'Wernicke encephalopathy — thiamine before glucose.',
             'options' => [['text'=>'IV thiamine','correct'=>true],['text'=>'Oral glucose only first always','correct'=>false],['text'=>'Haloperidol alone','correct'=>false],['text'=>'Discharge','correct'=>false]]],
            ['q' => 'Patient with chest pain, ST elevation in leads II, III, aVF has infarct in:', 'exp' => 'Inferior MI — often RCA; consider right-sided leads.',
             'options' => [['text'=>'Inferior wall (often RCA territory)','correct'=>true],['text'=>'Anterior septal only always','correct'=>false],['text'=>'Lateral only always','correct'=>false],['text'=>'Normal variant never reperfusion','correct'=>false]]],
            ['q' => 'Pregnant woman with BP 165/105 and proteinuria at 34 weeks:', 'exp' => 'Preeclampsia — magnesium sulfate if severe features, plan delivery timing.',
             'options' => [['text'=>'Preeclampsia — obstetric emergency management','correct'=>true],['text'=>'Normal pregnancy only bed rest never monitor','correct'=>false],['text'=>'Stop all antihypertensives always','correct'=>false],['text'=>'Ignore proteinuria','correct'=>false]]],
            ['q' => 'Fever, neck stiffness, photophobia in adult suggests:', 'exp' => 'Meningitis until proven otherwise — blood cultures, LP if safe, empiric antibiotics.',
             'options' => [['text'=>'Meningitis — urgent antibiotics and investigation','correct'=>true],['text'=>'Tension headache only always','correct'=>false],['text'=>'No LP ever if fever','correct'=>false],['text'=>'Oral paracetamol only outpatient always','correct'=>false]]],
        ];
    }

    private function examPharmacologyQuestions(): array
    {
        return [
            ['q' => 'Beta-blockers are contraindicated in:', 'exp' => 'Asthma/COPD with bronchospasm, decompensated heart failure, severe bradycardia.',
             'options' => [['text'=>'Uncontrolled bronchospastic airway disease','correct'=>true],['text'=>'Stable angina always contraindicated','correct'=>false],['text'=>'Hypertension never use','correct'=>false],['text'=>'Post-MI always contraindicated','correct'=>false]]],
            ['q' => 'Warfarin mechanism is inhibition of:', 'exp' => 'Vitamin K epoxide reductase — reduces synthesis of clotting factors II, VII, IX, X.',
             'options' => [['text'=>'Vitamin K-dependent clotting factor synthesis','correct'=>true],['text'=>'Platelet aggregation directly always','correct'=>false],['text'=>'Fibrinolysis only','correct'=>false],['text'=>'Thrombin directly always','correct'=>false]]],
            ['q' => 'Metformin reduces hepatic:', 'exp' => 'Metformin decreases hepatic gluconeogenesis and improves insulin sensitivity.',
             'options' => [['text'=>'Gluconeogenesis','correct'=>true],['text'=>'Bile acid synthesis only','correct'=>false],['text'=>'Urea cycle only primary','correct'=>false],['text'=>'Cholesterol absorption only','correct'=>false]]],
            ['q' => 'Aspirin irreversibly inhibits:', 'exp' => 'COX-1/COX-2 — antiplatelet effect via COX-1 in platelets lasts platelet lifespan.',
             'options' => [['text'=>'Cyclooxygenase (COX)','correct'=>true],['text'=>'Phosphodiesterase only always','correct'=>false],['text'=>'HMG-CoA reductase','correct'=>false],['text'=>'ACE','correct'=>false]]],
            ['q' => 'Nitrates cause vasodilation by releasing:', 'exp' => 'Nitric oxide activates guanylate cyclase increasing cGMP.',
             'options' => [['text'=>'Nitric oxide','correct'=>true],['text'=>'Histamine only always','correct'=>false],['text'=>'Acetylcholine at muscarinic receptors only primary','correct'=>false],['text'=>'Dopamine','correct'=>false]]],
            ['q' => 'SSRIs increase synaptic:', 'exp' => 'Selective serotonin reuptake inhibition increases serotonin availability.',
             'options' => [['text'=>'Serotonin','correct'=>true],['text'=>'Dopamine only always primary','correct'=>false],['text'=>'GABA directly always','correct'=>false],['text'=>'Acetylcholine only','correct'=>false]]],
            ['q' => 'Aminophylline toxicity can cause:', 'exp' => 'Narrow therapeutic index — nausea, arrhythmias, seizures.',
             'options' => [['text'=>'Arrhythmias and seizures','correct'=>true],['text'=>'Bradycardia only always benign','correct'=>false],['text'=>'No toxic effects','correct'=>false],['text'=>'Hyperkalaemia only','correct'=>false]]],
            ['q' => 'Local anaesthetics block:', 'exp' => 'Voltage-gated sodium channels in nerve fibres.',
             'options' => [['text'=>'Voltage-gated sodium channels','correct'=>true],['text'=>'Potassium channels only always therapeutic','correct'=>false],['text'=>'Calcium channels primarily all LA','correct'=>false],['text'=>'Muscarinic receptors only','correct'=>false]]],
            ['q' => 'Phenytoin is used for:', 'exp' => 'Antiepileptic; also used in some arrhythmias; zero-order kinetics at high doses.',
             'options' => [['text'=>'Seizure prophylaxis/treatment','correct'=>true],['text'=>'Hypertension first line always','correct'=>false],['text'=>'Asthma','correct'=>false],['text'=>'Depression first line only','correct'=>false]]],
            ['q' => 'Clopidogrel requires metabolic activation to inhibit:', 'exp' => 'P2Y12 receptor on platelets — prodrug needing CYP activation.',
             'options' => [['text'=>'P2Y12 platelet receptor','correct'=>true],['text'=>'COX-1 irreversibly like aspirin always same mechanism','correct'=>false],['text'=>'Warfarin pathway','correct'=>false],['text'=>'Thrombin directly always','correct'=>false]]],
        ];
    }

    private function medicalEthicsQuestions(): array
    {
        return [
            ['q' => 'Valid informed consent requires the patient to:', 'exp' => 'Capacity, voluntariness, and understanding of information including risks/benefits/alternatives.',
             'options' => [['text'=>'Have capacity and understand relevant information','correct'=>true],['text'=>'Sign any form without explanation','correct'=>false],['text'=>'Be over 18 always regardless of capacity','correct'=>false],['text'=>'Accept all recommended treatment','correct'=>false]]],
            ['q' => 'Confidentiality may be breached when:', 'exp' => 'Risk of serious harm to patient or others, legal requirement, public interest.',
             'options' => [['text'=>'Serious risk of harm to others or legal duty','correct'=>true],['text'=>'Colleague curiosity','correct'=>false],['text'=>'Insurance company request without consent always allowed','correct'=>false],['text'=>'Family asks casually without patient permission always','correct'=>false]]],
            ['q' => 'Gillick competence refers to:', 'exp' => 'Minor who has sufficient understanding to consent to treatment.',
             'options' => [['text'=>'A minor\'s ability to consent if Fraser/Gillick competent','correct'=>true],['text'=>'Parental consent never needed under 16 always','correct'=>false],['text'=>'Automatic adult capacity at 14 always','correct'=>false],['text'=>'Court order for all paediatric care','correct'=>false]]],
            ['q' => 'Duty of candour requires doctors to:', 'exp' => 'Openly disclose patient safety incidents and apologise when things go wrong.',
             'options' => [['text'=>'Be open and honest when patient safety incidents occur','correct'=>true],['text'=>'Hide errors to protect reputation always','correct'=>false],['text'=>'Blame patients for complications','correct'=>false],['text'=>'Avoid documentation of adverse events','correct'=>false]]],
            ['q' => 'Advanced directive/living will may:', 'exp' => 'Refusal of specified future treatment if valid and applicable.',
             'options' => [['text'=>'Refuse specified future treatment if valid and applicable','correct'=>true],['text'=>'Force doctors to provide any requested treatment always','correct'=>false],['text'=>'Override all emergency care always illegal','correct'=>false],['text'=>'Replace need for any consent ever','correct'=>false]]],
            ['q' => 'A doctor must not enter a sexual relationship with a current patient because:', 'exp' => 'Professional boundary violation — exploitation of trust.',
             'options' => [['text'=>'It violates professional boundaries and trust','correct'=>true],['text'=>'It is encouraged if consensual','correct'=>false],['text'=>'Only illegal if patient complains always','correct'=>false],['text'=>'Allowed after one appointment','correct'=>false]]],
            ['q' => 'Research ethics require:', 'exp' => 'Ethics committee approval, informed consent, minimising harm.',
             'options' => [['text'=>'Ethics approval and informed consent','correct'=>true],['text'=>'No consent if beneficial always','correct'=>false],['text'=>'Payment removes need for consent','correct'=>false],['text'=>'Only verbal agreement from sponsor','correct'=>false]]],
            ['q' => 'When a colleague is impaired (e.g. substance misuse), you should:', 'exp' => 'Patient safety first — support colleague but escalate per GMC/institutional policy.',
             'options' => [['text'=>'Raise concerns through appropriate channels to protect patients','correct'=>true],['text'=>'Ignore to maintain friendship always','correct'=>false],['text'=>'Publicly shame on social media','correct'=>false],['text'=>'Cover up errors','correct'=>false]]],
            ['q' => 'Best interests decisions for incapacitated adults should:', 'exp' => 'Consider past wishes, beliefs, consult others, least restrictive option.',
             'options' => [['text'=>'Consider patient\'s values and least restrictive option','correct'=>true],['text'=>'Follow cheapest option only always','correct'=>false],['text'=>'Ignore family/ carers always','correct'=>false],['text'=>'Default to no treatment always','correct'=>false]]],
            ['q' => 'Prescribing for yourself or close family is generally:', 'exp' => 'Discouraged except emergencies — lack of objectivity, no formal record.',
             'options' => [['text'=>'Discouraged except minor emergencies','correct'=>true],['text'=>'Mandatory for all relatives','correct'=>false],['text'=>'Preferred practice always','correct'=>false],['text'=>'Required by law daily','correct'=>false]]],
        ];
    }
}
