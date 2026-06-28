<x-filament-panels::page>

@push('styles')
<style>
/* ── Dark mode CSS variables ── */
:root {
    --aig-card: #ffffff;
    --aig-border: #e5e7eb;
    --aig-text: #374151;
    --aig-text-sub: #9ca3af;
    --aig-card-sub: #f9fafb;
}
.dark {
    --aig-card: #1e1b4b;
    --aig-border: #312e81;
    --aig-text: #f9fafb;
    --aig-text-sub: #a5b4fc;
    --aig-card-sub: #1a1740;
}

/* ── Reset Filament overrides inside our panel ── */
.aig-wrap *, .aig-wrap *::before, .aig-wrap *::after { box-sizing: border-box; }
.aig-wrap button { font-family: inherit; }
.aig-wrap select { font-family: inherit; }
.aig-wrap textarea { font-family: inherit; }

/* ── Field label ── */
.aig-label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--aig-text-sub);
    margin-bottom: 7px;
}

/* ── Select ── */
.aig-select {
    width: 100%;
    padding: 9px 30px 9px 11px;
    border: 1.5px solid var(--aig-border) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    color: var(--aig-text) !important;
    background-color: var(--aig-card) !important;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>") !important;
    background-repeat: no-repeat !important;
    background-position: right 9px center !important;
    background-size: 15px !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    outline: none !important;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
}
.aig-select:focus { border-color: #a78bfa !important; box-shadow: 0 0 0 3px rgba(167,139,250,.15) !important; }

/* ── Text area ── */
.aig-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid var(--aig-border) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    color: var(--aig-text) !important;
    background: var(--aig-card) !important;
    resize: none;
    outline: none !important;
    transition: border-color .15s, box-shadow .15s;
    line-height: 1.55;
}
.aig-textarea:focus { border-color: #a78bfa !important; box-shadow: 0 0 0 3px rgba(167,139,250,.15) !important; }
.aig-textarea::placeholder { color: #c4b5fd !important; }

/* ── Generate button ── */
.aig-btn-generate {
    width: 100%;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px;
    padding: 13px 20px !important;
    border-radius: 12px !important;
    border: none !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #fff !important;
    background: linear-gradient(135deg, #6C2E63 0%, #9333ea 100%) !important;
    box-shadow: 0 4px 18px rgba(108,46,99,.4) !important;
    cursor: pointer !important;
    transition: opacity .15s, transform .1s !important;
    outline: none !important;
    text-decoration: none !important;
}
.aig-btn-generate:hover:not(:disabled) { opacity: .92 !important; transform: translateY(-1px); }
.aig-btn-generate:active:not(:disabled) { transform: scale(.98); }
.aig-btn-generate:disabled { opacity: .6 !important; cursor: not-allowed !important; }

/* ── Toggle switch ── */
.aig-toggle-track {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
    flex-shrink: 0;
}
.aig-toggle-track input { opacity: 0; width: 0; height: 0; position: absolute; }
.aig-toggle-slider {
    position: absolute;
    inset: 0;
    border-radius: 11px;
    background: #d1d5db;
    transition: background .2s;
    cursor: pointer;
}
.aig-toggle-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 1px 4px rgba(0,0,0,.25);
    transition: transform .2s;
}
.aig-toggle-track input:checked + .aig-toggle-slider { background: #7c3aed; }
.aig-toggle-track input:checked + .aig-toggle-slider::after { transform: translateX(18px); }

/* ── Tab toggle ── */
.aig-tab-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    background: var(--aig-card-sub);
    border-radius: 12px;
    padding: 4px;
}
.aig-tab {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px;
    padding: 9px 12px !important;
    border-radius: 9px !important;
    border: none !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all .15s !important;
    outline: none !important;
    color: var(--aig-text-sub) !important;
    background: transparent !important;
    text-decoration: none !important;
}
.aig-tab.active {
    background: var(--aig-card) !important;
    color: #6d28d9 !important;
    box-shadow: 0 1px 5px rgba(0,0,0,.13) !important;
}
.aig-tab svg { flex-shrink: 0; }

/* ── Card ── */
.aig-card {
    background: var(--aig-card);
    border-radius: 20px;
    border: 1.5px solid var(--aig-border);
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
}

/* ── Divider ── */
.aig-divider { height: 1px; background: var(--aig-card-sub); margin: 2px -20px; }

/* ── Options list wrapper (x-show safe — no inline display) ── */
.aig-options-list { display: flex; flex-direction: column; gap: 6px; }
.aig-fill-answer  { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border-radius: 10px; background: #eff6ff; border: 1.5px solid #bfdbfe; font-size: 12px; font-weight: 600; color: #1d4ed8; align-self: flex-start; }
.aig-explanation  { padding: 9px 14px; border-radius: 10px; background: #f5f3ff; border: 1px solid #ddd6fe; font-size: 12px; color: #6d28d9; line-height: 1.55; }

/* ── Scrollbar ── */
.aig-scroll::-webkit-scrollbar { width: 5px; }
.aig-scroll::-webkit-scrollbar-track { background: transparent; }
.aig-scroll::-webkit-scrollbar-thumb { background: var(--aig-border); border-radius: 3px; }

/* ── Utility layout helpers ── */
.aig-flex-center   { display:flex; align-items:center; }
.aig-flex-between  { display:flex; align-items:center; justify-content:space-between; }
.aig-inline-center { display:inline-flex; align-items:center; }
.aig-col           { display:flex; flex-direction:column; }
.aig-flex-1        { flex:1; }
.aig-flex-shrink-0 { flex-shrink:0; }

/* ── Icon circle / square helpers ── */
/* Purple gradient icon box — used many times in various sizes */
.aig-icon-purple {
    background: linear-gradient(135deg,#6C2E63,#9333ea);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}

/* ── SVG spinner opacity helpers ── */
.aig-spin-track { opacity:.25; }
.aig-spin-fill  { opacity:.75; }

/* ── Sub-text helpers ── */
.aig-subtext     { font-size:11px; color:#9ca3af; margin-top:2px; }
.aig-subtext-vio { font-size:11px; color:#818cf8; margin-top:2px; }

/* ── Quiz info card (indigo tinted) ── */
.aig-quiz-info-card {
    padding:10px 12px; border-radius:10px;
    background:linear-gradient(135deg,#f5f3ff,#eef2ff);
    border:1.5px solid #c7d2fe;
    display:flex; align-items:center; justify-content:space-between; gap:8px;
}
.aig-quiz-info-title { font-size:12px; font-weight:700; color:#3730a3; }

/* ── Status badge (small pill) ── */
.aig-status-badge { font-size:10px; font-weight:700; padding:3px 8px; border-radius:20px; flex-shrink:0; }

/* ── Error box ── */
.aig-error-box {
    display:flex; align-items:flex-start; gap:8px;
    padding:10px 12px; background:#fef2f2; border:1.5px solid #fecaca;
    border-radius:10px; font-size:13px; color:#b91c1c; line-height:1.4;
}

/* ── Hint chip (explanation badge) ── */
.aig-hint-chip {
    display:inline-flex; align-items:center; gap:3px;
    font-size:10px; font-weight:600; color:#7c3aed;
    padding:3px 8px; border-radius:20px;
    background:#f5f3ff; border:1px solid #ddd6fe; flex-shrink:0;
}

/* ── Removed badge ── */
.aig-removed-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:700; color:#ef4444;
    padding:4px 10px; border-radius:20px; background:#fee2e2; flex-shrink:0;
}

/* ── Chevron toggle button ── */
.aig-chevron {
    display:inline-flex; align-items:center; justify-content:center;
    width:22px; height:22px; border-radius:6px;
    background:var(--aig-card-sub); flex-shrink:0; transition:transform .2s;
}

/* ── Question preview (collapsed state) ── */
.aig-qpreview {
    flex:1; min-width:0; font-size:12px; color:#6b7280;
    overflow:hidden; white-space:nowrap; text-overflow:ellipsis; padding:0 10px;
}

/* ── Empty state hero icon ── */
.aig-empty-icon-box {
    width:72px; height:72px; border-radius:22px;
    background:linear-gradient(135deg,#6C2E63,#9333ea);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 24px rgba(108,46,99,.3);
}

/* ── Topic chip (empty state) ── */
.aig-topic-chip {
    font-size:12px; font-weight:500; padding:6px 13px; border-radius:20px;
    border:1.5px solid #e9d5ff; background:white; color:#7c3aed;
    cursor:pointer; transition:all .15s; box-shadow:0 1px 3px rgba(0,0,0,.05);
}

/* ── Generate-from-button (inline badge in empty state text) ── */
.aig-gen-badge {
    display:inline-flex; align-items:center; gap:3px;
    padding:3px 9px; border-radius:6px;
    background:linear-gradient(135deg,#f3e8ff,#ede9fe);
    border:1px solid #ddd6fe; font-size:12px; font-weight:700;
    color:#7c3aed; vertical-align:middle; white-space:nowrap;
}

/* ── Action btn: collapse/expand all ── */
.aig-btn-expand {
    font-size:12px; font-weight:600; padding:8px 14px; border-radius:10px;
    border:1.5px solid #c7d2fe; background:white; color:#4338ca;
    cursor:pointer; white-space:nowrap; transition:background .15s;
}

/* ── Action btn: save done (green) ── */
.aig-btn-save-done {
    font-size:12px; font-weight:700; padding:8px 18px; border-radius:10px;
    border:none; background:linear-gradient(135deg,#16a34a,#22c55e);
    color:white; cursor:pointer; white-space:nowrap;
    box-shadow:0 3px 10px rgba(34,197,94,.3); transition:opacity .15s;
}

/* ── Min-width:0 container (flex overflow guard) ── */
.aig-min0 { min-width:0; }

/* ── Question card ── */
.aig-qcard {
    background: var(--aig-card);
    border-radius: 16px;
    border: 1.5px solid #ede9fe;
    box-shadow: 0 1px 6px rgba(109,40,217,.06);
    overflow: hidden;
    transition: box-shadow .2s, border-color .2s;
}
.dark .aig-qcard { border-color: var(--aig-border); }
.aig-qcard:hover { box-shadow: 0 4px 18px rgba(109,40,217,.1); border-color: #c4b5fd; }
.aig-qcard.removed { opacity: .45; border-color: #fecaca !important; box-shadow: none; }

.aig-qcard-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 16px;
    background: linear-gradient(135deg,#faf5ff,#f5f3ff);
    border-bottom: 1px solid #ede9fe;
    user-select: none;
    -webkit-user-select: none;
}

.aig-qnum {
    width: 28px; height: 28px; border-radius: 8px;
    background: linear-gradient(135deg,#6C2E63,#9333ea);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: white; flex-shrink: 0;
}
.aig-qtype {
    font-size: 11px; font-weight: 700;
    padding: 3px 10px; border-radius: 20px; flex-shrink: 0;
}
.aig-qmarks {
    font-size: 11px; font-weight: 600; color: #9ca3af;
    margin-left: 2px; flex-shrink: 0;
}
.aig-qbody {
    padding: 14px 16px 16px;
}
.aig-qtext {
    font-size: 14px; font-weight: 600; color: #1e1b4b;
    line-height: 1.65; margin: 0;
}
.aig-opt {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 10px;
    font-size: 13px; transition: background .15s;
}
.aig-opt.correct { background: #f0fdf4; border: 1.5px solid #86efac; }
.aig-opt.wrong   { background: var(--aig-card-sub); border: 1.5px solid var(--aig-border); }
.aig-opt-badge {
    width: 24px; height: 24px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800; flex-shrink: 0;
}
.aig-opt-badge.correct { background: #22c55e; color: white; }
.aig-opt-badge.wrong   { background: var(--aig-border); color: var(--aig-text-sub); }

/* ── Remove btn (in card header) ── */
.aig-btn-remove {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 8px;
    border: 1.5px solid #fecaca !important;
    font-size: 11px; font-weight: 600;
    background: rgba(254,202,202,.15) !important; color: #ef4444 !important;
    cursor: pointer; flex-shrink: 0; line-height: 1;
    transition: background .15s, border-color .15s;
}
.aig-btn-remove:hover { background: #fee2e2 !important; border-color: #f87171 !important; }

/* ── Modal overlay ── */
.aig-modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.aig-modal-overlay {
    position: absolute; inset: 0;
    background: rgba(15,10,30,.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.aig-modal-box {
    position: relative; z-index: 1;
    background: var(--aig-card);
    border-radius: 20px;
    padding: 28px 28px 24px;
    max-width: 380px; width: 100%;
    box-shadow: 0 24px 60px rgba(0,0,0,.22), 0 4px 12px rgba(0,0,0,.08);
    animation: aig-modal-in .18s ease;
}
@keyframes aig-modal-in {
    from { opacity:0; transform: scale(.94) translateY(8px); }
    to   { opacity:1; transform: scale(1)  translateY(0); }
}
.aig-modal-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: linear-gradient(135deg,#fee2e2,#fecaca);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.aig-modal-title {
    font-size: 17px; font-weight: 800; color: var(--aig-text);
    text-align: center; margin-bottom: 8px;
}
.aig-modal-sub {
    font-size: 13px; color: var(--aig-text-sub); text-align: center;
    line-height: 1.55; margin-bottom: 20px;
}
.aig-modal-preview {
    background: var(--aig-card-sub); border: 1.5px solid var(--aig-border);
    border-radius: 10px; padding: 10px 14px;
    font-size: 12px; color: var(--aig-text); line-height: 1.5;
    margin-bottom: 20px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.aig-modal-actions { display: flex; gap: 10px; }
.aig-modal-cancel {
    flex: 1; padding: 11px; border-radius: 11px;
    border: 1.5px solid var(--aig-border) !important;
    background: var(--aig-card) !important; color: var(--aig-text) !important;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background .15s;
}
.aig-modal-cancel:hover { background: var(--aig-card-sub) !important; }
.aig-modal-confirm {
    flex: 1; padding: 11px; border-radius: 11px;
    border: none !important;
    background: linear-gradient(135deg,#ef4444,#dc2626) !important;
    color: white !important;
    font-size: 14px; font-weight: 700; cursor: pointer;
    box-shadow: 0 4px 14px rgba(239,68,68,.35);
    transition: opacity .15s;
}
.aig-modal-confirm:hover { opacity: .9; }
</style>
{{-- Alpine registration MUST be in <head> (styles stack) because @stack('scripts') in
     Filament's layout comes AFTER @filamentScripts which fires alpine:init. --}}
<script>
'use strict';
const _aigI18n = {
    errSelectQuiz:   @js(__('creator.aig_err_select_quiz')),
    errEnterPrompt:  @js(__('creator.aig_err_enter_prompt')),
    errConnection:   @js(__('creator.aig_err_connection')),
    questionsSaved:  @js(__('creator.aig_questions_saved')),
    removedNote:     @js(__('creator.aig_removed_note')),
    allSavedNote:    @js(__('creator.aig_all_saved_note')),
    collapseAll:     @js(__('creator.aig_collapse_all')),
    expandAll:       @js(__('creator.aig_expand_all')),
    streamingMore:   @js(__('creator.aig_streaming_more')),
    answerLabel:     @js(__('creator.aig_answer_label')),
    explanationLabel:@js(__('creator.aig_explanation_label')),
    streamingThinking: @js(__('creator.aig_streaming_thinking')),
    streamingDoneOf: @js(__('creator.aig_streaming_done_of')),
};
const _quizMeta = @js(
    \App\Models\Quiz::where('creator_id', auth()->id())
        ->get(['id','title','total_questions','status','negative_marking_enabled'])
        ->keyBy('id')
        ->map(fn($q) => [
            'title'            => $q->title,
            'total_questions'  => $q->total_questions,
            'status'           => $q->status,
            'negative_marking' => (bool) $q->negative_marking_enabled,
        ])
);
document.addEventListener('alpine:init', () => {
    Alpine.data('aiGen', () => ({
        quizId: @js($quiz_id ?? ''),
        quizMeta: _quizMeta,
        saveMode: 'quiz',
        bankCollectionId: '',
        bankCollections: @js($bankCollections),
        prompt: '',
        count: 10,
        type: 'mixed',
        difficulty: 'medium',
        optionsCount: 4,
        marksPerQ: 1,
        negativeMarking: false,
        language: 'en',
        loading: false,
        output: '',
        questions: [],
        removedIds: [],
        streamedCount: 0,
        error: '',
        credits: {{ auth()->user()->ai_credits_free_remaining }},

        showRemoveModal: false,
        removeTargetId: null,
        removeTargetContent: '',

        get selectedQuiz() { return this.quizId ? (this.quizMeta[this.quizId] || null) : null; },
        get totalAfter()   { return (this.selectedQuiz?.total_questions ?? 0) + parseInt(this.count); },

        generate() {
            if (this.saveMode === 'quiz' && !this.quizId) { this.error = _aigI18n.errSelectQuiz; return; }
            if (!this.prompt.trim()) { this.error = _aigI18n.errEnterPrompt; return; }
            this.loading = true; this.output = ''; this.questions = [];
            this.removedIds = []; this.streamedCount = 0; this.error = '';
            const p = new URLSearchParams({
                prompt: this.prompt, count: this.count, type: this.type,
                difficulty: this.difficulty, options_count: this.optionsCount,
                marks_per_question: this.marksPerQ, negative_marking: this.negativeMarking ? 1 : 0,
                language: this.language,
            });
            if (this.saveMode === 'quiz') p.set('quiz_id', this.quizId);
            else { p.set('save_to_bank','1'); if (this.bankCollectionId) p.set('collection_id', this.bankCollectionId); }
            const es = new EventSource('/api/ai/generate?' + p + '&_token={{ csrf_token() }}');
            es.onmessage = e => {
                const d = JSON.parse(e.data);
                if (d.error)  { this.error = d.error; this.loading = false; es.close(); return; }
                if (d.chunk)  {
                    this.output += d.chunk;
                    const m = this.output.match(/"explanation"\s*:/g);
                    this.streamedCount = m ? m.length : 0;
                }
                if (d.done)   {
                    this.questions = d.questions || [];
                    this.loading = false;
                    if (this.credits > 0) this.credits--;
                    // Start with first card expanded so options are immediately visible
                    this.expandedCards = {};
                    if (this.questions.length > 0) {
                        this.expandedCards[this.questions[0].id] = true;
                    }
                    es.close();
                }
            };
            es.onerror = () => { this.error = _aigI18n.errConnection; this.loading = false; es.close(); };
        },

        askRemove(id, content) {
            this.removeTargetId = id;
            this.removeTargetContent = content;
            this.showRemoveModal = true;
        },
        confirmRemove() {
            const id = this.removeTargetId;
            this.removedIds = [...this.removedIds, id];
            fetch(`/api/questions/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content??''} });
            this.showRemoveModal = false;
            this.removeTargetId = null;
            this.removeTargetContent = '';
        },

        expandedCards: {},
        isCorrect(opt) { return opt.is_correct===true||opt.is_correct===1||opt.is_correct==='1'; },
        toggleCard(id) { this.expandedCards[id] = !this.expandedCards[id]; },
        isExpanded(id) { return !!this.expandedCards[id]; },

        typeBadge(t) {
            return {mcq_single:'MCQ Single',mcq_multiple:'MCQ Multi',fill_blank:'Fill Blank',true_false:'True/False',short_answer:'Short Ans.'}[t]||t;
        },
        typeBadgeStyle(t) {
            return {
                mcq_single:   'background:#eef2ff;color:#4338ca;',
                mcq_multiple: 'background:#f5f3ff;color:#6d28d9;',
                true_false:   'background:#fffbeb;color:#92400e;',
                fill_blank:   'background:#eff6ff;color:#1d4ed8;',
                short_answer: 'background:#f0fdf4;color:#15803d;',
            }[t] || 'background:var(--aig-card-sub);color:var(--aig-text);';
        },

        renderBody(q) {
            // All styles inline — avoids Filament/Tailwind CSS specificity conflicts
            const esc = s => s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : '';
            let html = '<div style="display:flex;flex-direction:column;gap:10px;">';

            // Question text
            html += '<p style="font-size:14px;font-weight:600;color:var(--aig-text);line-height:1.65;margin:0;">' + esc(q.content) + '</p>';

            // MCQ / True-False options
            if (q.options && q.options.length) {
                html += '<div style="display:flex;flex-direction:column;gap:6px;">';
                q.options.forEach((opt, i) => {
                    const correct   = opt.is_correct === true || opt.is_correct === 1 || opt.is_correct === '1';
                    const rowSt     = correct
                        ? 'display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;background:#f0fdf4;border:1.5px solid #86efac;'
                        : 'display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;background:var(--aig-card-sub);border:1.5px solid var(--aig-border);';
                    const badgeSt   = correct
                        ? 'min-width:24px;width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;background:#22c55e;color:white;flex-shrink:0;'
                        : 'min-width:24px;width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;background:var(--aig-border);color:var(--aig-text-sub);flex-shrink:0;';
                    const txtSt     = correct
                        ? 'color:#15803d;font-weight:600;font-size:13px;line-height:1.4;'
                        : 'color:var(--aig-text);font-size:13px;line-height:1.4;';
                    const label     = correct ? '✓' : String.fromCharCode(65 + i);
                    html += '<div style="' + rowSt + '"><span style="' + badgeSt + '">' + label + '</span><span style="' + txtSt + '">' + esc(opt.content) + '</span></div>';
                });
                html += '</div>';
            }

            // Fill blank answer
            if (q.type === 'fill_blank' && q.blank_answers && q.blank_answers.length) {
                const ans = q.blank_answers.map(a => esc(a)).join(' / ');
                html += '<div style="display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:10px;background:#eff6ff;border:1.5px solid #bfdbfe;font-size:12px;font-weight:600;color:#1d4ed8;">';
                html += '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#1d4ed8" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
                html += '<span>' + _aigI18n.answerLabel + ' ' + ans + '</span></div>';
            }

            // Explanation
            if (q.explanation) {
                html += '<div style="padding:9px 14px;border-radius:10px;background:#f5f3ff;border:1px solid #ddd6fe;font-size:12px;color:#6d28d9;line-height:1.55;">';
                html += '<span style="font-weight:700;margin-right:4px;">' + _aigI18n.explanationLabel + '</span>' + esc(q.explanation);
                html += '</div>';
            }

            html += '</div>';
            return html;
        },
    }));
});
</script>
@endpush

<div x-data="aiGen" class="aig-wrap" style="font-family:'Inter',system-ui,sans-serif;">

    {{-- ── Credits bar ── --}}
    <div class="aig-flex-center" style="gap:14px;margin-bottom:22px;flex-wrap:wrap;">
        <div class="aig-inline-center" style="gap:8px;padding:7px 14px 7px 7px;border-radius:50px;background:linear-gradient(135deg,#f3e8ff,#ede9fe);border:1.5px solid #c4b5fd;">
            <span class="aig-icon-purple" style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            </span>
            <span style="font-size:13px;font-weight:700;color:#6d28d9;white-space:nowrap;"><span x-text="credits"></span> {{ __('creator.aig_credits_left') }}</span>
        </div>
        <span style="font-size:12px;color:#9ca3af;">{{ __('creator.aig_credits_note') }}</span>
    </div>

    {{-- ── Two-column layout ── --}}
    <div style="display:grid;grid-template-columns:370px 1fr;gap:20px;align-items:start;">

        {{-- ════ LEFT PANEL ════ --}}
        <div class="aig-card">

            {{-- Header --}}
            <div class="aig-flex-center" style="gap:12px;padding:16px 20px;background:linear-gradient(135deg,#faf5ff,#f5f3ff);border-bottom:1.5px solid #ede9fe;">
                <span class="aig-icon-purple" style="width:36px;height:36px;border-radius:11px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                </span>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#1e1b4b;line-height:1.2;">{{ __('creator.aig_settings_heading') }}</div>
                    <div style="font-size:11px;color:#a78bfa;margin-top:2px;">{{ __('creator.aig_settings_sub') }}</div>
                </div>
            </div>

            <div class="aig-col" style="padding:20px;gap:16px;">

                {{-- Save To tabs --}}
                <div>
                    <span class="aig-label">{{ __('creator.aig_save_to') }}</span>
                    <div class="aig-tab-wrap">
                        <button type="button" @click="saveMode='quiz'" class="aig-tab" :class="saveMode==='quiz' ? 'active' : ''">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            {{ __('creator.aig_tab_quiz') }}
                        </button>
                        <button type="button" @click="saveMode='bank'" class="aig-tab" :class="saveMode==='bank' ? 'active' : ''">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            {{ __('creator.aig_tab_bank') }}
                        </button>
                    </div>
                </div>

                {{-- Quiz selector --}}
                @if(!$quiz)
                <div x-show="saveMode==='quiz'" x-transition>
                    <span class="aig-label">{{ __('creator.aig_select_quiz') }}</span>
                    <select x-model="quizId" @change="negativeMarking = selectedQuiz?.negative_marking ?? false" class="aig-select">
                        <option value="">— Choose a quiz —</option>
                        @foreach(\App\Models\Quiz::where('creator_id', auth()->id())->get(['id','title','total_questions','status']) as $q)
                            <option value="{{ $q->id }}">{{ $q->title }} ({{ $q->total_questions }}q)</option>
                        @endforeach
                    </select>
                    <div x-show="selectedQuiz" x-transition
                         class="aig-quiz-info-card" style="margin-top:8px;">
                        <div class="aig-min0">
                            <div class="aig-quiz-info-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="selectedQuiz?.title"></div>
                            <div class="aig-subtext-vio">
                                <span x-text="selectedQuiz?.total_questions"></span> now → <strong style="color:#6366f1;" x-text="totalAfter"></strong> after
                            </div>
                        </div>
                        <span class="aig-status-badge"
                              :style="selectedQuiz?.status==='published'?'background:#dcfce7;color:#15803d;':'background:#fef3c7;color:#92400e;'"
                              x-text="selectedQuiz?.status"></span>
                    </div>
                </div>

                <div x-show="saveMode==='bank'" x-transition x-data="{ adding: false, newName: '', saving: false }">
                    <div class="aig-flex-between" style="margin-bottom:7px;">
                        <span class="aig-label" style="margin-bottom:0;">{{ __('creator.qbank_collection_optional') }}</span>
                        <button type="button" @click="adding=!adding; newName=''"
                            class="aig-inline-center" style="gap:4px;font-size:11px;font-weight:600;color:#7c3aed;background:none;border:none;cursor:pointer;padding:0;">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span x-text="adding ? 'Cancel' : 'New collection'"></span>
                        </button>
                    </div>

                    {{-- Existing collection select --}}
                    <select x-show="!adding" x-model="bankCollectionId" class="aig-select">
                        <option value="">— No collection —</option>
                        <template x-for="col in bankCollections" :key="col.id">
                            <option :value="col.id" x-text="col.name"></option>
                        </template>
                    </select>

                    {{-- Inline create form --}}
                    <div x-show="adding" x-transition class="aig-flex-center" style="gap:8px;margin-top:6px;">
                        <input type="text" x-model="newName" placeholder="Collection name…"
                            @keydown.enter.prevent="
                                if (!newName.trim() || saving) return;
                                saving = true;
                                fetch('/creator/api/bank-collections', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' },
                                    body: JSON.stringify({ name: newName.trim() })
                                })
                                .then(r => r.json())
                                .then(col => {
                                    bankCollections = [...bankCollections, col];
                                    bankCollectionId = col.id;
                                    adding = false;
                                    newName = '';
                                })
                                .finally(() => saving = false);
                            "
                            style="flex:1;padding:9px 11px;border:1.5px solid #c4b5fd;border-radius:10px;font-size:13px;color:var(--aig-text);outline:none;background:var(--aig-card);font-family:inherit;">
                        <button type="button"
                            :disabled="!newName.trim() || saving"
                            @click="
                                if (!newName.trim() || saving) return;
                                saving = true;
                                fetch('/creator/api/bank-collections', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' },
                                    body: JSON.stringify({ name: newName.trim() })
                                })
                                .then(r => r.json())
                                .then(col => {
                                    bankCollections = [...bankCollections, col];
                                    bankCollectionId = col.id;
                                    adding = false;
                                    newName = '';
                                })
                                .finally(() => saving = false);
                            "
                            :style="'flex-shrink:0;padding:9px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,#6C2E63,#9333ea);color:white;font-size:13px;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;box-shadow:0 3px 10px rgba(108,46,99,.35);font-family:inherit;' + ((!newName.trim()||saving) ? 'opacity:.5;cursor:not-allowed;' : 'cursor:pointer;')"
                            >
                            <template x-if="!saving">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </template>
                            <template x-if="saving">
                                <svg class="animate-spin" width="13" height="13" fill="none" viewBox="0 0 24 24"><circle class="aig-spin-track" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path class="aig-spin-fill" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </template>
                            <span x-text="saving ? 'Saving…' : 'Save'"></span>
                        </button>
                    </div>
                </div>
                @else
                <div class="aig-quiz-info-card">
                    <div>
                        <div class="aig-quiz-info-title">{{ $quiz->title }}</div>
                        <div class="aig-subtext-vio">{{ $quiz->total_questions }} now → <strong style="color:#6366f1;" x-text="totalAfter"></strong> after</div>
                    </div>
                    <span class="aig-status-badge" style="background:#dcfce7;color:#15803d;">{{ $quiz->status }}</span>
                </div>
                @endif

                <div class="aig-divider"></div>

                {{-- Prompt --}}
                <div>
                    <span class="aig-label">{{ __('creator.aig_prompt_label') }}</span>
                    <textarea x-model="prompt" rows="3" class="aig-textarea"
                        placeholder="{{ __('creator.aig_prompt_placeholder') }}"></textarea>
                </div>

                {{-- 2×2 settings grid --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <span class="aig-label">{{ __('creator.aig_questions_label') }}</span>
                        <select x-model="count" class="aig-select">
                            <option value="5">5</option><option value="10" selected>10</option><option value="15">15</option>
                            <option value="20">20</option><option value="25">25</option><option value="30">30</option>
                            <option value="40">40</option><option value="50">50</option>
                        </select>
                    </div>
                    <div>
                        <span class="aig-label">{{ __('creator.aig_difficulty_label') }}</span>
                        <select x-model="difficulty" class="aig-select">
                            <option value="easy">{{ __('creator.qbank_diff_easy') }}</option><option value="medium" selected>{{ __('creator.qbank_diff_medium') }}</option>
                            <option value="hard">{{ __('creator.qbank_diff_hard') }}</option><option value="mixed">{{ __('creator.ai_type_mixed') }}</option>
                        </select>
                    </div>
                    <div>
                        <span class="aig-label">{{ __('creator.aig_type_label') }}</span>
                        <select x-model="type" class="aig-select">
                            <option value="mixed">{{ __('creator.ai_type_mixed') }}</option><option value="mcq_single">{{ __('creator.ai_type_mcq_single') }}</option>
                            <option value="mcq_multiple">{{ __('creator.ai_type_mcq_multiple') }}</option><option value="fill_blank">{{ __('creator.ai_type_fill_blank') }}</option>
                            <option value="true_false">{{ __('creator.ai_type_true_false') }}</option>
                        </select>
                    </div>
                    <div>
                        <span class="aig-label">{{ __('creator.aig_marks_label') }}</span>
                        <select x-model="marksPerQ" class="aig-select">
                            <option value="0.5">0.5</option><option value="1" selected>1</option>
                            <option value="2">2</option><option value="3">3</option>
                            <option value="4">4</option><option value="5">5</option>
                        </select>
                    </div>
                </div>

                {{-- Language --}}
                <div>
                    <span class="aig-label">{{ __('creator.aig_language_label') }}</span>
                    <select x-model="language" class="aig-select">
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
                </div>

                {{-- Options count --}}
                <div x-show="['mcq_single','mcq_multiple','mixed'].includes(type)" x-transition>
                    <span class="aig-label">{{ __('creator.aig_options_label') }}</span>
                    <select x-model="optionsCount" class="aig-select">
                        <option value="2">2 options</option><option value="3">3 options</option>
                        <option value="4" selected>4 options</option><option value="5">5 options</option>
                        <option value="6">6 options</option>
                    </select>
                </div>

                {{-- Negative marking toggle --}}
                <label class="aig-flex-center" style="gap:12px;cursor:pointer;padding:12px 14px;border-radius:11px;background:var(--aig-card-sub);border:1.5px solid var(--aig-border);">
                    <div class="aig-toggle-track">
                        <input type="checkbox" x-model="negativeMarking">
                        <div class="aig-toggle-slider"></div>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--aig-text);line-height:1.2;">{{ __('creator.aig_neg_marking_label') }}</div>
                        <div class="aig-subtext">{{ __('creator.aig_neg_marking_sub') }}</div>
                    </div>
                </label>

                {{-- Generate button --}}
                <button type="button" @click="generate()" :disabled="loading" class="aig-btn-generate">
                    <template x-if="!loading">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </template>
                    <template x-if="loading">
                        <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                            <circle class="aig-spin-track" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                            <path class="aig-spin-fill" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? @js(__('creator.aig_generating')) : @js(__('creator.aig_generate_btn'))"></span>
                </button>

                {{-- Error --}}
                <div x-show="error" x-transition class="aig-error-box">
                    <svg class="aig-flex-shrink-0" style="margin-top:1px;" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                    <span x-text="error"></span>
                </div>

            </div>{{-- /body --}}
        </div>{{-- /left --}}

        {{-- ════ RIGHT PANEL ════ --}}
        <div class="aig-min0">

            {{-- Empty state --}}
            <div x-show="!loading && !output && !questions.length" x-transition
                 style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:500px;border-radius:20px;border:2px dashed #e9d5ff;background:linear-gradient(160deg,#fdf4ff 0%,#fafafa 60%);padding:40px 24px;text-align:center;position:relative;overflow:hidden;">

                {{-- Decorative blobs --}}
                <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(167,139,250,.12) 0%,transparent 70%);pointer-events:none;"></div>
                <div style="position:absolute;bottom:-30px;left:-30px;width:140px;height:140px;border-radius:50%;background:radial-gradient(circle,rgba(192,132,252,.1) 0%,transparent 70%);pointer-events:none;"></div>

                {{-- Icon stack --}}
                <div style="position:relative;width:72px;height:72px;margin:0 auto 20px;">
                    <div class="aig-empty-icon-box">
                        <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div style="position:absolute;top:-5px;right:-7px;width:10px;height:10px;border-radius:50%;background:#c4b5fd;opacity:.8;"></div>
                    <div style="position:absolute;bottom:-3px;left:-7px;width:7px;height:7px;border-radius:50%;background:#e879f9;opacity:.6;"></div>
                </div>

                <div style="font-size:18px;font-weight:800;color:#1e1b4b;margin-bottom:6px;letter-spacing:-.01em;">{{ __('creator.aig_empty_heading') }}</div>
                <div style="font-size:13px;color:#9ca3af;line-height:2;text-align:center;">
                    {{ __('creator.aig_empty_sub') }}&nbsp;<span class="aig-gen-badge">
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                        {{ __('creator.aig_generate_btn') }}
                    </span>
                </div>

                {{-- Divider --}}
                <div class="aig-flex-center" style="gap:10px;width:100%;max-width:340px;margin:22px auto 16px;">
                    <div class="aig-flex-1" style="height:1px;background:linear-gradient(to right,transparent,#e9d5ff);"></div>
                    <span style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#c4b5fd;white-space:nowrap;">{{ __('creator.aig_sample_divider') }}</span>
                    <div class="aig-flex-1" style="height:1px;background:linear-gradient(to left,transparent,#e9d5ff);"></div>
                </div>

                {{-- Topic chips --}}
                <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:7px;max-width:400px;margin:0 auto;">
                    @foreach(['PHP OOP','JavaScript','World History','Data Structures','Python Basics','General Science'] as $eg)
                    <button type="button" @click="prompt='{{ $eg }}'"
                        class="aig-topic-chip"
                        onmouseover="this.style.background='linear-gradient(135deg,#6C2E63,#9333ea)';this.style.color='white';this.style.borderColor='transparent';this.style.boxShadow='0 3px 10px rgba(108,46,99,.3)';"
                        onmouseout="this.style.background='white';this.style.color='#7c3aed';this.style.borderColor='#e9d5ff';this.style.boxShadow='0 1px 3px rgba(0,0,0,.05)';">
                        {{ $eg }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Streaming --}}
            <div x-show="loading || (output && !questions.length)" x-transition
                 style="border-radius:20px;overflow:hidden;border:1.5px solid #ddd6fe;background:linear-gradient(135deg,#fdf4ff 0%,#f5f3ff 60%,#eef2ff 100%);">
                <div style="padding:24px;">
                    <div class="aig-flex-between" style="margin-bottom:18px;">
                        <div class="aig-flex-center" style="gap:14px;">
                            <div class="aig-icon-purple" style="width:44px;height:44px;border-radius:13px;">
                                <svg class="animate-spin" width="20" height="20" fill="none" viewBox="0 0 24 24">
                                    <circle class="aig-spin-track" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                                    <path class="aig-spin-fill" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size:15px;font-weight:700;color:#1e1b4b;">{{ __('creator.aig_streaming_heading') }}</div>
                                <div style="font-size:12px;color:#a78bfa;margin-top:3px;"
                                     x-text="streamedCount > 0 ? _aigI18n.streamingDoneOf.replace(':n', streamedCount).replace(':total', count) : _aigI18n.streamingThinking"></div>
                            </div>
                        </div>
                        <div class="aig-flex-shrink-0" style="text-align:right;">
                            <div style="font-size:36px;font-weight:900;color:#7c3aed;line-height:1;" x-text="streamedCount"></div>
                            <div style="font-size:11px;color:#c4b5fd;" x-text="'/ ' + count"></div>
                        </div>
                    </div>
                    <div style="width:100%;height:8px;background:rgba(255,255,255,.6);border-radius:8px;overflow:hidden;">
                        <div :style="'height:100%;border-radius:8px;background:linear-gradient(90deg,#7c3aed,#a855f7,#ec4899);transition:width .5s ease;width:' + Math.max(4, Math.min(100, Math.round((streamedCount/count)*100))) + '%'"></div>
                    </div>
                </div>
                <div style="padding:0 16px 16px;" x-show="streamedCount > 0">
                    <template x-for="n in Math.min(streamedCount, 5)" :key="n">
                        <div class="aig-flex-center" style="gap:10px;padding:10px 12px;background:rgba(255,255,255,.75);border-radius:10px;margin-bottom:6px;">
                            <span class="aig-icon-purple" style="width:24px;height:24px;border-radius:7px;font-size:10px;font-weight:800;color:white;" x-text="n"></span>
                            <div class="aig-flex-1">
                                <div :style="'height:9px;border-radius:5px;background:#e9d5ff;margin-bottom:5px;width:' + (55 + (n*13)%35) + '%'"></div>
                                <div :style="'height:7px;border-radius:4px;background:#f3e8ff;width:' + (35 + (n*9)%30) + '%'"></div>
                            </div>
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#a78bfa" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </div>
                    </template>
                    <div x-show="streamedCount > 5" style="text-align:center;font-size:12px;color:#a78bfa;padding-top:4px;"
                         x-text="_aigI18n.streamingMore.replace(':n', streamedCount - 5)"></div>
                </div>
            </div>

            {{-- Results --}}
            <div x-show="questions.length > 0" x-transition>

                {{-- Summary bar --}}
                <div class="aig-flex-between" style="padding:14px 20px;border-radius:16px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;margin-bottom:18px;gap:12px;">
                    <div class="aig-flex-center" style="gap:14px;">
                        <div class="aig-flex-center aig-flex-shrink-0" style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#16a34a,#22c55e);justify-content:center;box-shadow:0 4px 10px rgba(34,197,94,.3);">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </div>
                        <div>
                            <div style="font-size:15px;font-weight:800;color:#14532d;" x-text="_aigI18n.questionsSaved.replace(':n', questions.length - removedIds.length)"></div>
                            <div style="font-size:12px;color:#16a34a;margin-top:2px;"
                                 x-text="removedIds.length > 0 ? _aigI18n.removedNote.replace(':n', removedIds.length) : _aigI18n.allSavedNote"></div>
                        </div>
                    </div>
                    <div class="aig-flex-shrink-0" style="display:flex;gap:8px;">
                        <button type="button"
                            @click="let allExpanded = questions.filter(q=>!removedIds.includes(q.id)).every(q=>expandedCards[q.id]); let next={}; questions.forEach(q=>{ next[q.id]=!allExpanded; }); expandedCards=next;"
                            class="aig-btn-expand"
                            onmouseover="this.style.background='#eef2ff'" onmouseout="this.style.background='white'"
                            x-text="questions.filter(q=>!removedIds.includes(q.id)).every(q=>expandedCards[q.id]) ? _aigI18n.collapseAll : _aigI18n.expandAll">
                        </button>
                        <button type="button"
                            @click="
                                const count = questions.length - removedIds.length;
                                const colName = saveMode === 'bank'
                                    ? (bankCollections.find(c => c.id === bankCollectionId)?.name ?? '')
                                    : (selectedQuiz?.title ?? '');
                                const dest = saveMode === 'bank'
                                    ? '{{ route('filament.creator.resources.question-banks.index') }}'
                                    : '{{ route('filament.creator.resources.quizzes.index') }}';
                                $wire.saveAndRedirect(count, colName, dest);
                            "
                            class="aig-btn-save-done"
                            onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                            {{ __('creator.aig_save_done') }}
                        </button>
                    </div>
                </div>

                {{-- Question cards --}}
                <div class="aig-scroll aig-col" style="gap:10px;">
                    <template x-for="(q, i) in questions" :key="q.id">
                        <div class="aig-qcard" :class="removedIds.includes(q.id) ? 'removed' : ''">

                            {{-- Card header (always visible, click to toggle) --}}
                            <div class="aig-qcard-head"
                                 :style="removedIds.includes(q.id) ? 'background:#fff5f5;border-bottom-color:#fecaca;' : (isExpanded(q.id) ? 'border-bottom:1px solid #ede9fe;' : 'border-bottom:none;')"
                                 @click="if(!removedIds.includes(q.id)) toggleCard(q.id)"
                                 style="cursor:pointer;">

                                <span class="aig-qnum" x-text="i + 1"></span>
                                <span class="aig-qtype" :style="typeBadgeStyle(q.type)" x-text="typeBadge(q.type)"></span>
                                <span class="aig-qmarks" x-text="q.marks + ' pt' + (q.marks == 1 ? '' : 's')"></span>

                                {{-- Question preview (collapsed) --}}
                                <span x-show="!isExpanded(q.id) && !removedIds.includes(q.id)"
                                      class="aig-qpreview"
                                      x-text="q.content"></span>

                                <div x-show="isExpanded(q.id) || removedIds.includes(q.id)" class="aig-flex-1"></div>

                                {{-- Hint chip --}}
                                <span x-show="q.explanation && !removedIds.includes(q.id)"
                                      class="aig-hint-chip">
                                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                                    {{ __('creator.aig_hint_chip') }}
                                </span>

                                {{-- Chevron --}}
                                <span x-show="!removedIds.includes(q.id)"
                                      class="aig-chevron"
                                      :style="isExpanded(q.id) ? 'transform:rotate(180deg);background:#ede9fe;' : ''"
                                      @click.stop="toggleCard(q.id)">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#6b7280" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>

                                {{-- Remove button --}}
                                <button x-show="!removedIds.includes(q.id)" type="button"
                                    @click.stop="askRemove(q.id, q.content)"
                                    class="aig-btn-remove">
                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    Remove
                                </button>

                                {{-- Removed badge --}}
                                <span x-show="removedIds.includes(q.id)"
                                      class="aig-removed-badge">
                                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    {{ __('creator.aig_removed_badge') }}
                                </span>
                            </div>

                            {{-- Card body (collapsible, rendered via JS to avoid Alpine x-for bugs) --}}
                            <div class="aig-qbody"
                                 x-show="isExpanded(q.id) && !removedIds.includes(q.id)"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-html="renderBody(q)">
                            </div>

                        </div>
                    </template>
                </div>
            </div>

        </div>{{-- /right --}}
    </div>{{-- /grid --}}

    {{-- ── Remove confirmation modal ── --}}
    <div class="aig-modal-backdrop" x-show="showRemoveModal" x-transition.opacity
         @keydown.escape.window="showRemoveModal = false" style="display:none;">
        <div class="aig-modal-overlay" @click="showRemoveModal = false"></div>
        <div class="aig-modal-box" @click.stop x-transition:enter="aig-modal-in">
            <div class="aig-modal-icon">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            </div>
            <div class="aig-modal-title">{{ __('creator.aig_modal_title') }}</div>
            <div class="aig-modal-sub">{{ __('creator.aig_modal_body') }}</div>
            <div class="aig-modal-preview" x-text="removeTargetContent"></div>
            <div class="aig-modal-actions">
                <button type="button" class="aig-modal-cancel" @click="showRemoveModal = false">{{ __('creator.aig_modal_cancel') }}</button>
                <button type="button" class="aig-modal-confirm" @click="confirmRemove()">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    {{ __('creator.aig_modal_confirm') }}
                </button>
            </div>
        </div>
    </div>

</div>{{-- /wrap+x-data --}}
</x-filament-panels::page>
