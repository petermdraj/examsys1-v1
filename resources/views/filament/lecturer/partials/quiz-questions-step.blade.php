@php
    $record      = $this->record ?? null;
    $isEdit      = $record && $record->id;
    $quizId      = $isEdit ? $record->id : null;
    $quizTitle   = $isEdit ? $record->title : '';
    $existingQs  = $isEdit ? $record->questions()->with(['options','fillBlankAnswers'])->get() : collect();
    // Bank collections for filter dropdown
    $bankCollections = \App\Models\QuestionCollection::where('lecturer_id', auth()->id())
        ->orderBy('name')->get(['id','name']);
    $bankCategories = \App\Models\Category::active()->orderBy('name')->get(['id','name']);

    // Load all bank questions server-side — no AJAX needed
    $bankQuestionsRaw = \App\Models\Question::whereNull('quiz_id')
        ->where('lecturer_id', auth()->id())
        ->with(['options', 'collection', 'category'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(fn($q) => [
            'id'         => $q->id,
            'content'    => $q->content,
            'type'       => $q->type,
            'difficulty' => $q->difficulty ?? 'easy',
            'marks'      => (float) $q->marks,
            'collection' => $q->collection ? ['id' => $q->collection->id, 'name' => $q->collection->name] : null,
            'category'   => $q->category ? ['id' => $q->category->id, 'name' => $q->category->name] : null,
            'options'    => $q->options->map(fn($o) => [
                'content'    => $o->content,
                'is_correct' => (bool) $o->is_correct,
            ])->values()->all(),
        ])->values()->all();

    $initialQs = json_encode($existingQs->map(fn($q) => [
        'id'                     => $q->id,
        'type'                   => $q->type,
        'content'                => $q->content,
        'explanation'            => $q->explanation ?? '',
        'marks'                  => (float)$q->marks,
        'negative_marks'         => (float)$q->negative_marks,
        'hint'                   => $q->hint ?? '',
        'sort_order'             => $q->sort_order,
        'source_bank_question_id'=> $q->source_bank_question_id ?? null,
        'options'                => $q->options->map(fn($o) => ['content'=>$o->content,'is_correct'=>(bool)$o->is_correct])->values()->all(),
        'blank_answers'          => $q->fillBlankAnswers->map(fn($a) => ['answer'=>$a->answer])->values()->all(),
    ])->values()->all());

    $typeMap = [
        'mcq_single'   => ['bg'=>'#EEF2FF','text'=>'#4338CA','label'=>'MCQ'],
        'mcq_multiple' => ['bg'=>'#F0FDF4','text'=>'#16A34A','label'=>'Multi'],
        'fill_blank'   => ['bg'=>'#FFFBEB','text'=>'#D97706','label'=>'Fill'],
        'true_false'   => ['bg'=>'#FFF1F2','text'=>'#E11D48','label'=>'T/F'],
        'short_answer' => ['bg'=>'#F8FAFC','text'=>'#64748B','label'=>'Short'],
    ];
@endphp

{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  MAIN ALPINE COMPONENT                                          --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<style>
/* ── Extracted utility classes ── */
.qqm-root           { display:flex; flex-direction:column; gap:20px; }

/* Stats bar */
.qqm-stat-cell      { background:#fff; padding:16px 20px; }
.qqm-stat-label     { font-size:10px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#9CA3AF; margin:0 0 3px; }
.qqm-stat-value     { font-size:26px; font-weight:800; margin:0; line-height:1; }

/* Shared label style (10px uppercase) */
.qqm-field-label-sm { display:block; font-size:10px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:.06em; margin-bottom:5px; }

/* Shared label style (11px uppercase) */
.qqm-field-label    { display:block; font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
.qqm-section-label  { font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:.06em; }

/* Suppress any Filament/browser injected arrows on selects inside this component */
.qqm-root select { appearance:none !important; -webkit-appearance:none !important; -moz-appearance:none !important; background-image:none !important; }

/* Overlay backdrop */
.qqm-overlay        { display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; background:rgba(15,15,30,.55); backdrop-filter:blur(3px); }

/* Add-option / add-answer button */
.qqm-add-btn        { display:flex; align-items:center; gap:5px; padding:5px 12px; background:#EEF2FF; border:1.5px solid #C7D2FE; border-radius:7px; font-size:11px; font-weight:700; color:#4338CA; cursor:pointer; }

/* Remove-item icon button (red ×) */
.qqm-remove-btn     { flex:none; width:24px; height:24px; border-radius:6px; background:#FFF1F2; cursor:pointer; display:flex; align-items:center; justify-content:center; }

/* Flex gap row used in answer-list and option-list */
.qqm-answers-list   { display:flex; flex-direction:column; gap:7px; }

/* Space-between header row */
.qqm-section-hdr    { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }

/* Modal / delete gap row */
.qqm-action-row     { display:flex; gap:10px; }

/* Spinner opacity helpers (already in animate-spin elements) */
.qqm-spin-track     { opacity:.3; }
.qqm-spin-fill      { opacity:.9; }

/* Form inputs (2px border, 9px radius) */
.qqm-input-num      { width:100%; border:2px solid #E5E7EB; border-radius:9px; padding:9px 12px; font-size:14px; font-weight:600; background:#FAFAFA; color:#111827; outline:none; box-sizing:border-box; }

/* Inline icon that sits flush-left with margin-top */
.qqm-icon-flex-top  { flex:none; margin-top:1px; }
</style>

<div
    x-data="quizQuestionsManager($wire)"
    x-init="init()"
    class="qqm-root"
>

{{-- ══ STATS ══════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr) auto;background:#E5E7EB;border-radius:14px;overflow:hidden;border:1px solid #E5E7EB;gap:1px;">
    <div class="qqm-stat-cell">
        <p class="qqm-stat-label">{{ __('lecturer.stat_questions') }}</p>
        <p class="qqm-stat-value" style="color:#6366F1;" x-text="questions.length">{{ $existingQs->count() }}</p>
    </div>
    <div class="qqm-stat-cell">
        <p class="qqm-stat-label">{{ __('lecturer.stat_total_marks') }}</p>
        <p class="qqm-stat-value" style="color:#8B5CF6;" x-text="totalMarks">{{ $existingQs->sum('marks') }}</p>
    </div>
    <div class="qqm-stat-cell" style="display:flex;align-items:center;">
        <button type="button" @click="openAddModal()"
            style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;background:#6366F1;border:none;border-radius:9px;font-size:12px;font-weight:700;color:#fff;cursor:pointer;white-space:nowrap;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
            {{ __('lecturer.add_question') }}
        </button>
    </div>
</div>

{{-- ══ AI GENERATE + BANK ═════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

    {{-- AI card --}}
    <div style="border-radius:14px;border:1.5px solid #DDD6FE;box-shadow:0 1px 6px rgba(109,40,217,.07);overflow:visible;">
        <button type="button" @click="aiOpen=!aiOpen"
            style="width:100%;display:flex;align-items:center;gap:12px;padding:15px 18px;background:linear-gradient(135deg,#7C3AED,#6366F1);border:none;cursor:pointer;text-align:left;border-radius:13px 13px 0 0;">
            <span style="flex:none;width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 001.272 1.272L21 12l-5.816 1.916a2 2 0 00-1.272 1.271L12 21l-1.912-5.813a2 2 0 00-1.272-1.271L3 12l5.816-1.915a2 2 0 001.272-1.272z"/></svg>
            </span>
            <div style="flex:1;">
                <p style="font-size:13px;font-weight:700;color:#fff;margin:0;">{{ __('lecturer.generate_with_ai') }}</p>
                <p style="font-size:11px;color:rgba(255,255,255,.7);margin:2px 0 0;">{{ __('lecturer.ai_auto_create_desc') }}</p>
            </div>
            <svg :style="aiOpen?'transform:rotate(180deg)':''" style="transition:transform .2s;flex:none;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
        </button>

        <div x-show="aiOpen" style="padding:18px 18px 20px;background:#FAFAFA;display:flex;flex-direction:column;gap:13px;border-radius:0 0 13px 13px;">
            <div>
                <label class="qqm-field-label-sm">Topic / Prompt</label>
                <textarea x-model="aiPrompt" rows="2" placeholder="{{ __('lecturer.ai_prompt_placeholder') }}"
                    style="width:100%;border:1.5px solid #E5E7EB;border-radius:8px;padding:9px 11px;font-size:13px;resize:none;background:#fff;color:#111827;outline:none;box-sizing:border-box;font-family:inherit;"
                    onfocus="this.style.borderColor='#7C3AED'" onblur="this.style.borderColor='#E5E7EB'"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label class="qqm-field-label-sm">{{ __('lecturer.ai_count_label') }}</label>
                    <div style="position:relative;">
                        <input type="number" x-model.number="aiCount" min="1" step="1" style="width:100%;border:1.5px solid #E5E7EB;border-radius:8px;padding:8px 11px;font-size:13px;background:#fff;color:#111827;outline:none;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label class="qqm-field-label-sm">{{ __('lecturer.ai_type_label') }}</label>
                    <div style="position:relative;">
                        <select x-model="aiType" style="width:100%;border:1.5px solid #E5E7EB;border-radius:8px;padding:8px 28px 8px 11px;font-size:13px;background:#fff;color:#111827;appearance:none;-webkit-appearance:none;-moz-appearance:none;outline:none;cursor:pointer;box-sizing:border-box;">
                            <option value="mixed">{{ __('lecturer.ai_type_mixed') }}</option>
                            <option value="mcq_single">{{ __('lecturer.ai_type_mcq_single') }}</option>
                            <option value="mcq_multiple">{{ __('lecturer.ai_type_mcq_multiple') }}</option>
                            <option value="true_false">{{ __('lecturer.ai_type_true_false') }}</option>
                            <option value="fill_blank">{{ __('lecturer.ai_type_fill_blank') }}</option>
                        </select>
                        <svg style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                <div style="grid-column:span 2;">
                    <label class="qqm-field-label-sm">{{ __('lecturer.aig_language_label') }}</label>
                    <div style="position:relative;">
                        <select x-model="aiLanguage" style="width:100%;border:1.5px solid #E5E7EB;border-radius:8px;padding:8px 28px 8px 11px;font-size:13px;background:#fff;color:#111827;appearance:none;-webkit-appearance:none;-moz-appearance:none;outline:none;cursor:pointer;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#7C3AED'" onblur="this.style.borderColor='#E5E7EB'">
                            <option value="en">🇬🇧 English</option>
                            <option value="hi">🇮🇳 Hindi — हिन्दी</option>
                            <option value="es">🇪🇸 Spanish — Español</option>
                            <option value="fr">🇫🇷 French — Français</option>
                            <option value="de">🇩🇪 German — Deutsch</option>
                            <option value="ar">🇸🇦 Arabic — العربية</option>
                            <option value="pt">🇧🇷 Portuguese — Português</option>
                            <option value="zh">🇨🇳 Chinese — 中文</option>
                            <option value="ja">🇯🇵 Japanese — 日本語</option>
                            <option value="nl">🇳🇱 Dutch — Nederlands</option>
                            <option value="sv">🇸🇪 Swedish — Svenska</option>
                            <option value="da">🇩🇰 Danish — Dansk</option>
                            <option value="no">🇳🇴 Norwegian — Norsk</option>
                        </select>
                        <svg style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
            </div>

            <div x-show="aiError" style="display:flex;gap:8px;padding:9px 12px;background:#FFF1F2;border:1px solid #FECDD3;border-radius:8px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2" class="qqm-icon-flex-top"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                <span x-text="aiError" style="font-size:12px;color:#9F1239;"></span>
            </div>
            <div x-show="aiDone" style="display:flex;gap:8px;padding:9px 12px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2.5" class="qqm-icon-flex-top"><path d="M20 6L9 17l-5-5"/></svg>
                <span style="font-size:12px;color:#15803D;font-weight:600;"><span x-text="aiAdded"></span> {{ __('lecturer.ai_questions_added') }}</span>
            </div>

            {{-- ★ GENERATE BUTTON --}}
            <div @click="!aiLoading && generateAI()"
                :style="'display:inline-flex;align-items:center;gap:7px;padding:8px 14px;margin-top:12px;border-radius:8px;cursor:pointer;user-select:none;background:linear-gradient(135deg,#7C3AED,#6366F1);box-shadow:0 2px 8px rgba(109,40,217,.3);' + (aiLoading ? 'opacity:.5;cursor:not-allowed;box-shadow:none;' : '')">
                <svg :style="aiLoading?'display:none':''" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M12 3l1.912 5.813a2 2 0 001.272 1.272L21 12l-5.816 1.916a2 2 0 00-1.272 1.271L12 21l-1.912-5.813a2 2 0 00-1.272-1.271L3 12l5.816-1.915a2 2 0 001.272-1.272z"/></svg>
                <svg :style="aiLoading?'':'display:none'" class="animate-spin" width="14" height="14" fill="none" viewBox="0 0 24 24"><circle class="qqm-spin-track" cx="12" cy="12" r="10" stroke="#fff" stroke-width="4"/><path class="qqm-spin-fill" fill="#fff" d="M4 12a8 8 0 018-8v8z"/></svg>
                <span style="font-size:13px;font-weight:700;color:#fff;" x-text="aiLoading ? '{{ __('lecturer.ai_generating') }}' : '{{ __('lecturer.ai_generate_btn') }}'"></span>
            </div>
        </div>
    </div>

    {{-- ═══ IMPORT FROM BANK CARD ═══ --}}
    <div class="rounded-xl overflow-hidden border border-indigo-200 bg-white" style="box-shadow:0 2px 12px rgba(99,102,241,.1);">

        {{-- Header (toggle) --}}
        <button type="button" @click="toggleBankOpen()"
            class="w-full flex flex-row flex-nowrap items-center gap-3 text-left border-0 cursor-pointer"
            style="padding:13px 16px;background:linear-gradient(135deg,#4F46E5 0%,#6366F1 100%);">
            <span class="flex-shrink-0 flex items-center justify-center rounded-lg"
                style="width:30px;height:30px;background:rgba(255,255,255,.18);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2">
                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-white" style="font-size:13px;">{{ __('lecturer.import_from_bank') }}</div>
                <div class="text-white" style="font-size:11px;opacity:.7;margin-top:1px;"
                    x-text="bankAllItems.length + ' questions in your library'"></div>
            </div>
            <span x-show="bankSelected.length > 0"
                class="flex-shrink-0 font-bold text-white rounded-full"
                style="font-size:11px;padding:3px 10px;background:rgba(255,255,255,.25);border:1px solid rgba(255,255,255,.4);"
                x-text="bankSelected.length + ' {{ __('lecturer.bank_selected_suffix') }}'"></span>
            <svg class="flex-shrink-0 text-white" style="transition:transform .2s;opacity:.8;"
                :style="bankOpen ? 'transform:rotate(180deg)' : ''"
                width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>

        {{-- Collapsible body --}}
        <div x-show="bankOpen">

            {{-- Warning: quiz not saved yet --}}
            @if(!$quizId)
            <div class="flex flex-row items-start gap-2 border-b border-amber-200" style="padding:9px 14px;background:#FFFBEB;">
                <svg class="flex-shrink-0 mt-px" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span class="text-amber-800" style="font-size:12px;">{{ __('lecturer.complete_steps_first') }}, then return here to import questions into your quiz.</span>
            </div>
            @endif

            {{-- Filters strip --}}
            <div class="bg-gray-50 border-b border-gray-100" style="padding:10px 12px 8px;">
                {{-- Filter dropdowns --}}
                <div class="flex flex-row flex-wrap gap-2 mb-2">
                    <div style="flex:1;min-width:120px;position:relative;">
                        <select x-model="bankFilter.type"
                            style="width:100%;border-radius:8px;border:1.5px solid #E5E7EB;background:#fff;color:#374151;font-size:11.5px;font-weight:500;padding:6px 24px 6px 9px;appearance:none;-webkit-appearance:none;-moz-appearance:none;outline:none;cursor:pointer;box-sizing:border-box;"
                            :style="bankFilter.type ? 'border-color:#818CF8;color:#4338CA;background:#EEF2FF;' : ''">
                            <option value="">All Types</option>
                            <option value="mcq_single">MCQ Single</option>
                            <option value="mcq_multiple">MCQ Multi</option>
                            <option value="true_false">True / False</option>
                            <option value="fill_blank">Fill Blank</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                        <svg style="position:absolute;right:7px;top:50%;transform:translateY(-50%);pointer-events:none;" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                    <div style="flex:1;min-width:120px;position:relative;">
                        <select x-model="bankFilter.difficulty"
                            style="width:100%;border-radius:8px;border:1.5px solid #E5E7EB;background:#fff;color:#374151;font-size:11.5px;font-weight:500;padding:6px 24px 6px 9px;appearance:none;-webkit-appearance:none;-moz-appearance:none;outline:none;cursor:pointer;box-sizing:border-box;"
                            :style="bankFilter.difficulty ? 'border-color:#818CF8;color:#4338CA;background:#EEF2FF;' : ''">
                            <option value="">All Levels</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                        <svg style="position:absolute;right:7px;top:50%;transform:translateY(-50%);pointer-events:none;" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                    <div style="flex:1;min-width:120px;position:relative;">
                        <select x-model="bankFilter.category_id"
                            style="width:100%;border-radius:8px;border:1.5px solid #E5E7EB;background:#fff;color:#374151;font-size:11.5px;font-weight:500;padding:6px 24px 6px 9px;appearance:none;-webkit-appearance:none;-moz-appearance:none;outline:none;cursor:pointer;box-sizing:border-box;"
                            :style="bankFilter.category_id ? 'border-color:#818CF8;color:#4338CA;background:#EEF2FF;' : ''">
                            <option value="">{{ __('lecturer.qbank_filter_all_subjects') }}</option>
                            @foreach($bankCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <svg style="position:absolute;right:7px;top:50%;transform:translateY(-50%);pointer-events:none;" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                    <div style="flex:1;min-width:120px;position:relative;">
                        <select x-model="bankFilter.collection_id"
                            style="width:100%;border-radius:8px;border:1.5px solid #E5E7EB;background:#fff;color:#374151;font-size:11.5px;font-weight:500;padding:6px 24px 6px 9px;appearance:none;-webkit-appearance:none;-moz-appearance:none;outline:none;cursor:pointer;box-sizing:border-box;"
                            :style="bankFilter.collection_id ? 'border-color:#818CF8;color:#4338CA;background:#EEF2FF;' : ''">
                            <option value="">All Collections</option>
                            @foreach($bankCollections as $col)
                            <option value="{{ $col->id }}">{{ $col->name }}</option>
                            @endforeach
                        </select>
                        <svg style="position:absolute;right:7px;top:50%;transform:translateY(-50%);pointer-events:none;" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                {{-- Search + clear --}}
                <div class="flex flex-row items-center gap-2">
                    <div class="relative flex-1">
                        <svg class="absolute pointer-events-none text-gray-400" style="left:9px;top:50%;transform:translateY(-50%);" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" x-model="bankFilter.search" placeholder="Search questions…"
                            class="w-full rounded-lg border border-gray-200 bg-white text-gray-700 outline-none"
                            style="font-size:12px;padding:6px 28px 6px 28px;"
                            onfocus="this.style.borderColor='#6366F1'" onblur="this.style.borderColor='#E5E7EB'">
                        <button x-show="bankFilter.search" @click="bankFilter.search=''" type="button"
                            class="absolute bg-transparent border-0 cursor-pointer text-gray-400 p-0"
                            style="right:8px;top:50%;transform:translateY(-50%);">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <button x-show="bankFilter.type||bankFilter.difficulty||bankFilter.category_id||bankFilter.collection_id||bankFilter.search"
                        @click="bankFilter={type:'',difficulty:'',category_id:'',collection_id:'',search:''}" type="button"
                        class="flex-shrink-0 rounded-lg border border-indigo-200 bg-indigo-50 font-semibold text-indigo-600 cursor-pointer whitespace-nowrap"
                        style="font-size:11px;padding:6px 10px;">× Clear</button>
                </div>
                {{-- Count + select-all row --}}
                <div class="flex flex-row items-center justify-between mt-2">
                    <span class="text-gray-400" style="font-size:11px;">
                        <span class="font-bold text-indigo-600" x-text="bankFiltered.length"></span>
                        <span x-text="bankFiltered.length !== bankAllItems.length ? ' of '+bankAllItems.length+' questions' : ' questions'"></span>
                    </span>
                    <div class="flex flex-row items-center gap-3">
                        <button x-show="bankSelected.length > 0" @click="bankSelected=[]" type="button"
                            class="border-0 bg-transparent cursor-pointer text-gray-400 underline p-0" style="font-size:11px;">Deselect all</button>
                        <button x-show="bankFiltered.length > 0 && bankSelected.length < bankFiltered.length"
                            @click="bankSelected=[...new Set([...bankSelected,...bankFiltered.map(q=>q.id)])]" type="button"
                            class="border-0 bg-transparent cursor-pointer font-semibold text-indigo-500 p-0" style="font-size:11px;">
                            Select all <span x-text="bankFiltered.length"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- List --}}
            <div class="overflow-y-auto" style="max-height:280px;">

                {{-- Empty state --}}
                <template x-if="bankFiltered.length === 0">
                    <div class="text-center" style="padding:30px 16px;">
                        <div class="flex items-center justify-center rounded-xl bg-indigo-50 mx-auto mb-3" style="width:40px;height:40px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        </div>
                        <div class="font-semibold text-gray-700 mb-1" style="font-size:13px;"
                            x-text="bankAllItems.length===0 ? 'Your bank is empty' : 'No matches'"></div>
                        <div class="text-gray-400" style="font-size:12px;">
                            <template x-if="bankAllItems.length===0">
                                <span>Add questions via <a href="/lecturer/question-banks" class="text-indigo-500 font-semibold">Question Bank</a></span>
                            </template>
                            <template x-if="bankAllItems.length>0">
                                <span>Try adjusting your filters</span>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Question rows --}}
                <template x-for="bq in bankFiltered" :key="bq.id">
                    <div class="border-b border-gray-100">
                        {{-- Main row: Tailwind flex so Filament can't override display --}}
                        <div @click="toggleBankSelect(bq.id)"
                            class="flex flex-row flex-nowrap items-center gap-2 cursor-pointer"
                            style="padding:11px 14px;"
                            :class="bankSelected.includes(bq.id) ? 'bg-indigo-50' : 'bg-white hover:bg-gray-50'">

                            {{-- Checkbox --}}
                            <div class="flex-shrink-0 flex items-center justify-center"
                                style="width:20px;height:20px;border-radius:5px;border:2px solid;transition:background .1s,border-color .1s;"
                                :style="{background: bankSelected.includes(bq.id)?'#6366F1':'#fff', borderColor: bankSelected.includes(bq.id)?'#6366F1':'#CBD5E1'}">
                                <svg x-show="bankSelected.includes(bq.id)" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5">
                                    <path d="M20 6L9 17l-5-5"/>
                                </svg>
                            </div>

                            {{-- Type badge --}}
                            <span x-text="typeLabel(bq.type)" class="flex-shrink-0"
                                style="font-size:11px;font-weight:700;padding:4px 8px;border-radius:6px;white-space:nowrap;letter-spacing:.02em;"
                                :style="{background: typeBg(bq.type), color: typeText(bq.type)}"></span>

                            {{-- Question text --}}
                            <span x-text="bq.content" class="flex-1 min-w-0 truncate"
                                style="font-size:13px;font-weight:500;color:#1F2937;"></span>

                            {{-- Difficulty badge --}}
                            <span x-text="bq.difficulty" class="flex-shrink-0"
                                style="font-size:11px;font-weight:600;padding:4px 9px;border-radius:20px;white-space:nowrap;text-transform:capitalize;"
                                :style="{background: bq.difficulty==='hard'?'#FFF1F2':bq.difficulty==='medium'?'#FFFBEB':'#DCFCE7', color: bq.difficulty==='hard'?'#E11D48':bq.difficulty==='medium'?'#D97706':'#16A34A'}"></span>

                            {{-- Marks --}}
                            <span x-text="bq.marks+'m'" class="flex-shrink-0"
                                style="font-size:11px;color:#9CA3AF;white-space:nowrap;"></span>

                            {{-- Eye button --}}
                            <button type="button" @click.stop="previewId=(previewId===bq.id?null:bq.id)"
                                class="flex-shrink-0 flex items-center justify-center"
                                style="width:28px;height:28px;padding:0;border-radius:8px;border:1.5px solid;cursor:pointer;transition:background .1s,border-color .1s;"
                                :style="{background: previewId===bq.id?'#6366F1':'#fff', borderColor: previewId===bq.id?'#6366F1':'#E2E8F0'}">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke-width="2.5"
                                    :stroke="previewId===bq.id?'#fff':'#94A3B8'">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Options preview --}}
                        <template x-if="previewId===bq.id">
                            <div style="padding:10px 14px 14px 50px;background:#F5F3FF;border-top:1px solid #DDD6FE;">
                                <template x-for="(opt,oi) in bq.options" :key="oi">
                                    {{-- Tailwind flex for options row --}}
                                    <div class="flex flex-row items-center gap-2" style="padding:4px 0;">
                                        <div class="flex-shrink-0 flex items-center justify-center"
                                            style="width:18px;height:18px;border-radius:50%;border:2px solid;"
                                            :style="{background: opt.is_correct?'#22C55E':'#fff', borderColor: opt.is_correct?'#22C55E':'#D1D5DB'}">
                                            <svg x-show="opt.is_correct" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5">
                                                <path d="M20 6L9 17l-5-5"/>
                                            </svg>
                                        </div>
                                        <span x-text="opt.content" class="flex-1"
                                            style="font-size:13px;line-height:1.4;"
                                            :style="{color: opt.is_correct?'#166534':'#374151', fontWeight: opt.is_correct?'600':'400'}"></span>
                                    </div>
                                </template>
                                <template x-if="bq.options.length===0">
                                    <span style="font-size:11px;color:#9CA3AF;font-style:italic;">Fill blank / short answer — no options</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- ── FOOTER ── --}}
            <div class="border-t border-gray-100">

                {{-- Nothing selected: hint --}}
                <div x-show="bankSelected.length === 0"
                    class="flex flex-row items-center justify-center gap-2 text-gray-400"
                    style="padding:11px 14px;font-size:12px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 12l2 2 4-4"/></svg>
                    Tick questions above to select them
                </div>

                {{-- Selected: prominent CTA --}}
                {{-- x-show wrapper is block; inner div handles flex so Filament CSS doesn't fight it --}}
                <div x-show="bankSelected.length > 0">
                    <div style="display:flex;flex-direction:row;align-items:center;gap:12px;padding:10px 14px;">

                        {{-- Left: count + deselect --}}
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;flex-direction:row;align-items:baseline;gap:5px;">
                                <span style="font-size:22px;font-weight:900;color:#4F46E5;line-height:1;" x-text="bankSelected.length"></span>
                                <span style="font-size:13px;font-weight:600;color:#374151;">
                                    question<span x-show="bankSelected.length !== 1">s</span> selected
                                </span>
                            </div>
                            <div x-show="!quizId" style="font-size:11px;color:#F59E0B;font-weight:500;margin-top:2px;">
                                ⚠ Complete steps 1 &amp; 2 first
                            </div>
                            <button @click.stop="bankSelected=[]" type="button"
                                style="font-size:11px;color:#9CA3AF;background:none;border:none;padding:0;cursor:pointer;text-decoration:underline;margin-top:1px;">
                                Deselect all
                            </button>
                        </div>

                        {{-- Right: Add to Quiz button --}}
                        <button type="button"
                            @click="quizId && !bankImporting && runImportBank()"
                            class="flex-shrink-0 flex flex-row items-center gap-2"
                            style="padding:10px 20px;font-size:13px;font-weight:700;color:#fff;background:linear-gradient(135deg,#6366F1 0%,#4F46E5 100%);border:none;border-radius:10px;cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.45);letter-spacing:.01em;"
                            :style="{opacity: (!quizId||bankImporting)?'0.45':'1', cursor: (!quizId||bankImporting)?'not-allowed':'pointer'}">
                            <svg x-show="bankImporting" class="animate-spin" width="14" height="14" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="#fff" stroke-width="4"/>
                                <path class="qqm-spin-fill" fill="#fff" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <svg x-show="!bankImporting" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                                <path d="M12 5v14M5 12l7 7 7-7"/>
                            </svg>
                            <span x-text="bankImporting ? 'Adding…' : 'Add to Quiz'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /.grid AI+Bank --}}

{{-- ══ QUESTIONS LIST ══════════════════════════════════════════════ --}}
<div style="border:1.5px solid #E5E7EB;border-radius:14px;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 18px;background:#F9FAFB;border-bottom:1px solid #E5E7EB;">
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:26px;height:26px;border-radius:7px;background:#EEF2FF;display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span style="font-size:13px;font-weight:700;color:#374151;">Questions <span style="color:#9CA3AF;font-weight:500;" x-text="'(' + questions.length + ')'"></span></span>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="questions.length === 0" style="padding:36px 20px;text-align:center;background:#fff;">
        <div style="width:50px;height:50px;border-radius:14px;background:#EEF2FF;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3m0 4h.01"/></svg>
        </div>
        <p style="font-size:14px;font-weight:700;color:#374151;margin:0 0 6px;">No questions yet</p>
        <p style="font-size:12px;color:#9CA3AF;margin:0 0 16px;">Generate with AI above, import from your bank, or add manually.</p>
        <button type="button" @click="openAddModal()" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:#6366F1;border:none;border-radius:9px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>Add First Question
        </button>
    </div>

    {{-- Question rows --}}
    <template x-for="(q, idx) in questions" :key="q.id">
        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 18px;border-bottom:1px solid #F3F4F6;background:#fff;">
            {{-- Number + type badge --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;flex:none;min-width:36px;">
                <span x-text="idx+1" style="width:26px;height:26px;border-radius:7px;background:#EEF2FF;color:#4338CA;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;"></span>
                <span x-text="typeLabel(q.type)" :style="'font-size:9px;font-weight:700;padding:2px 5px;border-radius:4px;text-transform:uppercase;letter-spacing:.04em;background:'+typeBg(q.type)+';color:'+typeText(q.type)"></span>
            </div>
            {{-- Content --}}
            <p x-text="q.content" style="flex:1;font-size:13px;color:#374151;margin:0;line-height:1.55;padding-top:4px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"></p>
            {{-- Right: marks + actions --}}
            <div style="flex:none;display:flex;align-items:center;gap:8px;padding-top:3px;">
                <span x-text="q.marks+'m'" style="font-size:11px;font-weight:600;color:#9CA3AF;min-width:28px;text-align:right;"></span>
                <button type="button" @click="openEditModal(q)"
                    style="width:28px;height:28px;border-radius:7px;background:#EEF2FF;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" @click="confirmDelete(q)"
                    style="width:28px;height:28px;border-radius:7px;background:#FFF1F2;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                </button>
            </div>
        </div>
    </template>
</div>

{{-- ══ QUESTION MODAL --}}
<div id="qqm-modal-el" class="qqm-overlay" @click.self="closeModal()">
<div role="dialog" style="border:none;padding:0;border-radius:20px;width:calc(100% - 32px);max-width:660px;max-height:92vh;overflow-y:auto;box-shadow:0 32px 80px rgba(0,0,0,.22);background:#fff;">
    {{-- Coloured top accent + header --}}
    <div style="background:linear-gradient(135deg,#7C3AED,#6366F1);padding:20px 24px 20px;border-radius:20px 20px 0 0;position:sticky;top:0;z-index:2;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                    <div style="width:28px;height:28px;border-radius:7px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex:none;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 x-text="editingId ? 'Edit Question' : 'Add New Question'" style="font-size:16px;font-weight:800;color:#fff;margin:0;"></h3>
                </div>
                <p style="font-size:12px;color:rgba(255,255,255,.7);margin:0;padding-left:36px;" x-text="editingId ? 'Update the question details below' : 'Fill in the details for your new question'"></p>
            </div>
            <div @click="closeModal()" style="flex:none;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);cursor:pointer;display:flex;align-items:center;justify-content:center;margin-top:2px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </div>
        </div>

        {{-- Type pills inside header — :style uses OBJECT so it merges with static style --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:14px;">
            <template x-for="opt in typeOptions" :key="opt.value">
                <div @click="form.type = opt.value; onTypeChange()"
                    :style="{
                        background:   form.type===opt.value ? '#fff' : 'rgba(255,255,255,.13)',
                        color:        form.type===opt.value ? '#6366F1' : 'rgba(255,255,255,.82)',
                        borderColor:  form.type===opt.value ? '#fff' : 'rgba(255,255,255,.28)',
                        fontWeight:   form.type===opt.value ? '800' : '600'
                    }"
                    style="padding:6px 14px;border-radius:20px;border:1.5px solid;font-size:11px;cursor:pointer;letter-spacing:.02em;user-select:none;"
                    x-text="opt.label"></div>
            </template>
        </div>
    </div>

    {{-- Modal body --}}
    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

        {{-- Question text --}}
        <div>
            <label class="qqm-field-label">Question Text <span style="color:#E11D48;">*</span></label>
            <textarea x-model="form.content" rows="3" placeholder="Enter your question here…"
                style="width:100%;border:2px solid #E5E7EB;border-radius:10px;padding:11px 14px;font-size:14px;resize:vertical;background:#FAFAFA;color:#111827;outline:none;box-sizing:border-box;font-family:inherit;line-height:1.55;transition:border-color .15s;"
                onfocus="this.style.borderColor='#6366F1';this.style.background='#fff'" onblur="this.style.borderColor='#E5E7EB';this.style.background='#FAFAFA'"></textarea>
        </div>

        {{-- Marks / neg marks / hint --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div>
                <label class="qqm-field-label">Marks</label>
                <input type="number" x-model.number="form.marks" min="0" step="0.5"
                    class="qqm-input-num"
                    onfocus="this.style.borderColor='#6366F1';this.style.background='#fff'" onblur="this.style.borderColor='#E5E7EB';this.style.background='#FAFAFA'">
            </div>
            <div>
                <label class="qqm-field-label">Neg. Marks</label>
                <input type="number" x-model.number="form.negative_marks" min="0" step="0.25"
                    class="qqm-input-num"
                    onfocus="this.style.borderColor='#6366F1';this.style.background='#fff'" onblur="this.style.borderColor='#E5E7EB';this.style.background='#FAFAFA'">
            </div>
            <div>
                <label class="qqm-field-label">Hint</label>
                <input type="text" x-model="form.hint" placeholder="Optional…"
                    style="width:100%;border:2px solid #E5E7EB;border-radius:9px;padding:9px 12px;font-size:13px;background:#FAFAFA;color:#111827;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#6366F1';this.style.background='#fff'" onblur="this.style.borderColor='#E5E7EB';this.style.background='#FAFAFA'">
            </div>
        </div>

        {{-- MCQ Options --}}
        <div x-show="['mcq_single','mcq_multiple','true_false'].includes(form.type)">
            <div class="qqm-section-hdr">
                <label class="qqm-section-label">Answer Options</label>
                <button type="button" @click="addOption()"
                    class="qqm-add-btn">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>Add Option
                </button>
            </div>
            <div class="qqm-answers-list">
                <template x-for="(opt, oi) in form.options" :key="oi">
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:2px solid;transition:all .15s;"
                        :style="{ background: opt.is_correct ? '#F0FDF4' : '#F9FAFB', borderColor: opt.is_correct ? '#22C55E' : '#E5E7EB' }">
                        {{-- correct toggle (object :style so width/height survive) --}}
                        <div @click="toggleCorrect(oi)"
                            style="flex:none;width:22px;height:22px;border-radius:50%;border:2px solid;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;"
                            :style="{ background: opt.is_correct ? '#22C55E' : '#fff', borderColor: opt.is_correct ? '#22C55E' : '#D1D5DB' }">
                            <svg :style="{ display: opt.is_correct ? '' : 'none' }" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5"><path d="M20 6L9 17l-5-5"/></svg>
                        </div>
                        <input type="text" x-model="opt.content" placeholder="Option text…"
                            style="flex:1;border:none;background:transparent;font-size:13px;color:#374151;outline:none;padding:0;">
                        <div @click="removeOption(oi)"
                            class="qqm-remove-btn"
                            :style="{ display: form.options.length > 2 ? 'flex' : 'none' }">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Fill blank answers --}}
        <div x-show="form.type === 'fill_blank'">
            <div class="qqm-section-hdr">
                <label class="qqm-section-label">Accepted Answers</label>
                <button type="button" @click="form.blank_answers.push({answer:''})"
                    class="qqm-add-btn">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>Add Answer
                </button>
            </div>
            <div class="qqm-answers-list">
                <template x-for="(ans, ai) in form.blank_answers" :key="ai">
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#F9FAFB;border:2px solid #E5E7EB;border-radius:10px;">
                        <input type="text" x-model="ans.answer" placeholder="Accepted answer (case-insensitive)…"
                            style="flex:1;border:none;background:transparent;font-size:13px;color:#374151;outline:none;">
                        <div @click="form.blank_answers.splice(ai,1)"
                            class="qqm-remove-btn"
                            :style="{ display: form.blank_answers.length > 1 ? 'flex' : 'none' }">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Explanation --}}
        <div>
            <label class="qqm-field-label">
                Explanation <span style="font-weight:400;color:#9CA3AF;text-transform:none;">(shown to student after attempt)</span>
            </label>
            <textarea x-model="form.explanation" rows="2" placeholder="Why is this the correct answer? (optional)"
                style="width:100%;border:2px solid #E5E7EB;border-radius:10px;padding:11px 14px;font-size:13px;resize:none;background:#FAFAFA;color:#111827;outline:none;box-sizing:border-box;font-family:inherit;line-height:1.5;"
                onfocus="this.style.borderColor='#6366F1';this.style.background='#fff'" onblur="this.style.borderColor='#E5E7EB';this.style.background='#FAFAFA'"></textarea>
        </div>

        {{-- Error --}}
        <div x-show="modalError" style="display:flex;gap:10px;padding:11px 14px;background:#FFF1F2;border:1.5px solid #FECDD3;border-radius:9px;align-items:flex-start;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2" class="qqm-icon-flex-top"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            <span x-text="modalError" style="font-size:12px;color:#9F1239;font-weight:500;"></span>
        </div>
    </div>

    {{-- Modal footer --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 24px 20px;border-top:1px solid #F3F4F6;position:sticky;bottom:0;background:#fff;border-radius:0 0 20px 20px;">
        <div style="font-size:11px;color:#9CA3AF;" x-text="'Type: ' + typeOptions.find(t=>t.value===form.type)?.label"></div>
        <div class="qqm-action-row">
            <div @click="closeModal()"
                style="padding:10px 20px;background:#fff;border:2px solid #E5E7EB;border-radius:10px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;user-select:none;">Cancel</div>
            <div @click="!saving && submitQuestion()"
                style="display:flex;align-items:center;gap:7px;padding:10px 22px;background:linear-gradient(135deg,#7C3AED,#6366F1);border-radius:10px;font-size:13px;font-weight:700;color:#fff;box-shadow:0 3px 10px rgba(109,40,217,.25);user-select:none;"
                :style="{ opacity: saving ? '0.6' : '1', cursor: saving ? 'not-allowed' : 'pointer' }">
                <svg :style="saving?'':'display:none'" class="animate-spin" width="13" height="13" fill="none" viewBox="0 0 24 24"><circle class="qqm-spin-track" cx="12" cy="12" r="10" stroke="#fff" stroke-width="4"/><path class="qqm-spin-fill" fill="#fff" d="M4 12a8 8 0 018-8v8z"/></svg>
                <span x-text="saving ? 'Saving…' : (editingId ? 'Update Question' : 'Add Question')"></span>
            </div>
        </div>
    </div>
</div>
</div>{{-- /qqm-modal-el overlay --}}

{{-- Delete confirm --}}
<div id="qqm-delete-el" class="qqm-overlay" @click.self="closeDelete()">
<div role="dialog" style="border:none;padding:28px 24px;border-radius:16px;width:calc(100% - 40px);max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.15);background:#fff;">
    <div style="width:44px;height:44px;border-radius:12px;background:#FFF1F2;display:flex;align-items:center;justify-content:center;margin:0 0 14px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
    </div>
    <h4 style="font-size:16px;font-weight:800;color:#111827;margin:0 0 6px;">Delete Question?</h4>
    <p style="font-size:13px;color:#6B7280;margin:0 0 20px;line-height:1.5;" x-text="'&quot;' + (deleteTarget?.content?.substring(0,80) ?? '') + (deleteTarget?.content?.length > 80 ? '…' : '') + '&quot;'"></p>
    <div class="qqm-action-row">
        <div @click="closeDelete()" style="flex:1;padding:10px;background:#F9FAFB;border:1.5px solid #E5E7EB;border-radius:9px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;text-align:center;user-select:none;">Cancel</div>
        <div @click="!saving && runDelete()"
            style="flex:1;padding:10px;background:#E11D48;border-radius:9px;font-size:13px;font-weight:700;color:#fff;text-align:center;user-select:none;"
            :style="{ opacity: saving ? '0.6' : '1', cursor: saving ? 'not-allowed' : 'pointer' }">
            <span x-text="saving?'Deleting…':'Yes, Delete'"></span>
        </div>
    </div>
</div>
</div>{{-- /qqm-delete-el overlay --}}

<script>
'use strict';
function quizQuestionsManager(wire) {
    return {
        _wire:       wire,
        quizId:      @js($quizId),
        questions:   {!! $initialQs !!},

        /* AI */
        aiOpen:    true,
        aiPrompt:    @js($quizTitle),
        aiCount:     10,
        aiType:      'mixed',
        aiLanguage:  'en',
        aiLoading: false,
        aiError:   '',
        aiDone:    false,
        aiAdded:   0,

        /* Bank — server-side data, client-side filter */
        bankOpen:      true,
        bankAllItems:  {!! json_encode($bankQuestionsRaw) !!},
        bankImporting: false,
        bankSelected:  [],
        previewId:     null,
        bankFilter:    { type: '', difficulty: '', category_id: '', collection_id: '', search: '' },

        /* Modal */
        modalOpen:    false,
        editingId:    null,
        saving:       false,
        modalError:   '',
        deleteConfirm: false,
        deleteTarget:  null,

        form: {
            type: 'mcq_single', content: '', explanation: '',
            marks: 1, negative_marks: 0, hint: '',
            options: [{content:'',is_correct:false},{content:'',is_correct:false},{content:'',is_correct:false},{content:'',is_correct:false}],
            blank_answers: [{answer:''}],
        },

        typeOptions: [
            {value:'mcq_single',   label:'MCQ Single'},
            {value:'mcq_multiple', label:'MCQ Multi'},
            {value:'true_false',   label:'True / False'},
            {value:'fill_blank',   label:'Fill Blank'},
            {value:'short_answer', label:'Short Answer'},
        ],

        get totalMarks() {
            return this.questions.reduce((s, q) => s + (parseFloat(q.marks)||0), 0);
        },

        get bankFiltered() {
            // Exclude questions already imported into the quiz (matched by direct id or source_bank_question_id copy link)
            const addedIds = new Set(this.questions.map(q => q.id));
            const addedSourceIds = new Set(this.questions.map(q => q.source_bank_question_id).filter(Boolean));
            return this.bankAllItems.filter(bq => {
                if (addedIds.has(bq.id) || addedSourceIds.has(bq.id)) return false;
                if (this.bankFilter.type && bq.type !== this.bankFilter.type) return false;
                if (this.bankFilter.difficulty && bq.difficulty !== this.bankFilter.difficulty) return false;
                if (this.bankFilter.category_id && (!bq.category || bq.category.id !== this.bankFilter.category_id)) return false;
                if (this.bankFilter.collection_id && (!bq.collection || bq.collection.id !== this.bankFilter.collection_id)) return false;
                if (this.bankFilter.search) {
                    const s = this.bankFilter.search.toLowerCase();
                    if (!bq.content.toLowerCase().includes(s)) return false;
                }
                return true;
            });
        },

        init() {},

        /* Type helpers */
        typeLabel(t) { return {mcq_single:'MCQ',mcq_multiple:'Multi',fill_blank:'Fill',true_false:'T/F',short_answer:'Short'}[t]||t; },
        typeBg(t)    { return {mcq_single:'#EEF2FF',mcq_multiple:'#F0FDF4',fill_blank:'#FFFBEB',true_false:'#FFF1F2',short_answer:'#F8FAFC'}[t]||'#F3F4F6'; },
        typeText(t)  { return {mcq_single:'#4338CA',mcq_multiple:'#16A34A',fill_blank:'#D97706',true_false:'#E11D48',short_answer:'#64748B'}[t]||'#374151'; },

        /* Modal */
        blankForm() {
            return {
                type:'mcq_single', content:'', explanation:'', marks:1, negative_marks:0, hint:'',
                options:[{content:'',is_correct:false},{content:'',is_correct:false},{content:'',is_correct:false},{content:'',is_correct:false}],
                blank_answers:[{answer:''}],
            };
        },
        _showEl(id)  { const el=document.getElementById(id); if(el){el.style.display='flex';document.body.style.overflow='hidden';} },
        _hideEl(id)  { const el=document.getElementById(id); if(el){el.style.display='none';document.body.style.overflow='';} },
        openAddModal()  {
            this.editingId=null; this.form=this.blankForm(); this.modalError='';
            this._showEl('qqm-modal-el');
        },
        openEditModal(q) {
            this.editingId = q.id;
            this.form = {
                type: q.type, content: q.content, explanation: q.explanation||'',
                marks: q.marks, negative_marks: q.negative_marks, hint: q.hint||'',
                options: q.options?.length ? q.options.map(o=>({content:o.content,is_correct:o.is_correct}))
                    : [{content:'',is_correct:false},{content:'',is_correct:false}],
                blank_answers: q.blank_answers?.length ? q.blank_answers.map(a=>({answer:a.answer})) : [{answer:''}],
            };
            this.modalError = '';
            this._showEl('qqm-modal-el');
        },
        closeModal() { this.editingId=null; this.modalError=''; this._hideEl('qqm-modal-el'); },

        onTypeChange() {
            if (this.form.type === 'true_false') {
                this.form.options = [{content:'True',is_correct:true},{content:'False',is_correct:false}];
            } else if (['mcq_single','mcq_multiple'].includes(this.form.type) && this.form.options.length < 2) {
                this.form.options = [{content:'',is_correct:false},{content:'',is_correct:false}];
            }
        },
        addOption()          { this.form.options.push({content:'',is_correct:false}); },
        removeOption(i)      { this.form.options.splice(i,1); },
        toggleCorrect(i) {
            if (this.form.type === 'mcq_single' || this.form.type === 'true_false') {
                this.form.options.forEach((o,j) => o.is_correct = (j===i));
            } else {
                this.form.options[i].is_correct = !this.form.options[i].is_correct;
            }
        },

        async submitQuestion() {
            if (!this.form.content.trim()) { this.modalError = 'Question text is required.'; return; }
            if (['mcq_single','mcq_multiple','true_false'].includes(this.form.type)) {
                if (!this.form.options.some(o=>o.is_correct)) { this.modalError = 'Mark at least one correct answer.'; return; }
                if (this.form.options.some(o=>!o.content.trim())) { this.modalError = 'All option fields must be filled.'; return; }
            }
            this.saving = true; this.modalError = '';
            try {
                const updated = await this._wire.call('saveQuestion', this.form, this.editingId);
                this.questions = updated;
                this.closeModal();
            } catch(e) { this.modalError = 'Failed to save. Please try again.'; }
            this.saving = false;
        },

        confirmDelete(q) { this.deleteTarget=q; this._showEl('qqm-delete-el'); },
        closeDelete()    { this.deleteTarget=null; this._hideEl('qqm-delete-el'); },
        async runDelete() {
            if (!this.deleteTarget) return;
            this.saving = true;
            try {
                const updated = await this._wire.call('deleteQuestion', this.deleteTarget.id);
                this.questions = updated;
                this._hideEl('qqm-delete-el'); this.deleteTarget = null;
            } catch(e) { alert('Failed to delete.'); }
            this.saving = false;
        },

        /* Bank */
        toggleBankOpen() { this.bankOpen = !this.bankOpen; this.previewId = null; },
        toggleBankSelect(id) {
            const i = this.bankSelected.indexOf(id);
            i === -1 ? this.bankSelected.push(id) : this.bankSelected.splice(i,1);
        },
        async runImportBank() {
            if (!this.bankSelected.length || !this.quizId || this.bankImporting) return;
            this.bankImporting = true;
            try {
                const updated = await this._wire.call('importFromBank', this.bankSelected);
                if (updated && updated.length !== undefined) this.questions = updated;
                this.bankSelected = [];
            } catch(e) { alert('Import failed. Please try again.'); }
            this.bankImporting = false;
        },

        /* AI */
        generateAI() {
            if (!this.aiPrompt.trim()) { this.aiError='Please enter a topic.'; return; }
            this.aiLoading=true; this.aiError=''; this.aiDone=false; this.aiAdded=0;
            const p = new URLSearchParams({
                quiz_id:this.quizId, prompt:this.aiPrompt, count:this.aiCount,
                type:this.aiType, difficulty:'medium', options_count:4,
                marks_per_question:1, negative_marking:0,
                language:this.aiLanguage,
                _token:'{{ csrf_token() }}',
            });
            const es = new EventSource('/api/ai/generate?'+p.toString());
            es.onmessage = (e) => {
                const d = JSON.parse(e.data);
                if (d.error) { this.aiError=d.error; this.aiLoading=false; es.close(); return; }
                if (d.done) {
                    this.aiAdded=d.questions_saved??d.question_count??0;
                    this.aiDone=true; this.aiLoading=false;
                    es.close();
                    /* Reload questions list via Livewire */
                    setTimeout(() => window.location.reload(), 900);
                }
            };
            es.onerror = () => { this.aiError='Connection error. Try again.'; this.aiLoading=false; es.close(); };
        },
    };
}
</script>
</div>{{-- end x-data --}}
