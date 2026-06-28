<div>
  @php
    // Keep {{variable}} placeholders visible in the preview so editors can see them.
    $vars   = collect($template->variables ?? [])
        ->pluck('name')
        ->mapWithKeys(fn($n) => [$n => strtoupper($n)])
        ->toArray();
    // htmlspecialchars encodes for srcdoc; {!! !!} outputs it raw so browser decodes once
    $srcdoc = htmlspecialchars($template->renderRaw($vars), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  @endphp

  {{-- Subject --}}
  <div class="etp-section">
    <div class="etp-section-label">{{ __('admin.etpl_subject_label') }}</div>
    <p class="etp-subject">{!! $template->subject !!}</p>
  </div>

  {{-- Variables --}}
  @if($template->variables)
  <div class="etp-section">
    <div class="etp-section-label--mb8">{{ __('admin.etpl_variables_label') }}</div>
    <div class="etp-var-list">
      @foreach($template->variables as $v)
        @php $tag = '{{' . $v['name'] . '}}'; @endphp
        <span class="etp-var-chip">
          <code class="etp-var-code">{{ $tag }}</code>
          <span class="etp-var-sep">—</span>
          <span class="etp-var-desc">{{ $v['description'] }}</span>
        </span>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Body Preview --}}
  <div class="etp-section-label--mb8">{{ __('admin.etpl_body_label') }}</div>
  <div class="etp-preview-wrap">
    {{-- {!! !!} is intentional: $srcdoc is already htmlspecialchars-encoded for the attribute --}}
    <iframe srcdoc="{!! $srcdoc !!}"
            class="etp-iframe"
            sandbox="allow-same-origin">
    </iframe>
  </div>

  {{-- Send Test Email --}}
  <div class="etp-send-section"
       x-data="{
         email: '{{ addslashes(auth()->user()->email) }}',
         sending: false,
         sent: false,
         error: '',
         i18n: {
           sending:       @js(__('admin.etpl_sending')),
           send:          @js(__('admin.etpl_send_btn')),
           sentMsg:       @js(__('admin.etpl_sent_msg')),
           failedMsg:     @js(__('admin.etpl_failed_msg')),
           requestFailed: @js(__('admin.etpl_request_failed')),
         },
         send() {
           if (!this.email) return;
           this.sending = true; this.sent = false; this.error = '';
           fetch('{{ route('admin.email-templates.send-test', $template) }}', {
             method: 'POST',
             headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' },
             body: JSON.stringify({ email: this.email })
           })
           .then(async r => {
             const d = await r.json().catch(() => ({}));
             this.sending = false;
             if (r.ok && d.ok) { this.sent = true; return; }
             this.error = d.message ?? this.i18n.failedMsg;
           })
           .catch(() => { this.sending = false; this.error = this.i18n.requestFailed; });
         }
       }">

    {{-- Card wrapper --}}
    <div class="etp-card">

      <div class="etp-card-header">
        <div class="etp-card-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
          </svg>
        </div>
        <div>
          <div class="etp-card-title">{{ __('admin.etpl_send_test_heading') }}</div>
          <div class="etp-card-sub">{{ __('admin.etpl_send_test_sub') }}</div>
        </div>
      </div>

      <div class="etp-input-row">
        <input x-model="email" type="email" placeholder="you@example.com"
               @keydown.enter="send()"
               class="etp-input" />

        <button @click="send()" :disabled="sending || !email" class="qs-send-btn">
          <svg x-show="!sending" class="qs-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
          </svg>
          <svg x-show="sending" class="qs-ico qs-spin" fill="none" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25"/>
            <path fill="currentColor" style="opacity:.75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span x-text="sending ? i18n.sending : i18n.send"></span>
        </button>
      </div>

      {{-- Feedback --}}
      <div x-show="sent" x-transition class="etp-feedback etp-feedback--success">
        <svg class="etp-feedback-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span x-text="i18n.sentMsg.replace(':email', email)"></span>
      </div>
      <div x-show="error" x-transition class="etp-feedback etp-feedback--error">
        <svg class="etp-feedback-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <span x-text="error"></span>
      </div>
    </div>

    <style>
      /* Email template preview utility classes */
      .etp-section{margin-bottom:16px}
      .etp-section-label{font-size:11px;font-weight:600;letter-spacing:.08em;color:#6B7280;margin-bottom:6px}
      .etp-section-label--mb8{font-size:11px;font-weight:600;letter-spacing:.08em;color:#6B7280;margin-bottom:8px}
      .etp-subject{margin:0;font-weight:600;font-size:15px;color:#111827}
      .etp-var-list{display:flex;flex-wrap:wrap;gap:8px}
      .etp-var-chip{display:inline-flex;align-items:center;gap:6px;background:#F3F0FF;border:1px solid #C4B5FD;border-radius:6px;padding:4px 10px;font-size:12px}
      .etp-var-code{font-family:monospace;font-weight:700;color:#4C1D95}
      .etp-var-sep{color:#A78BFA}
      .etp-var-desc{color:#6D28D9}
      .etp-preview-wrap{border:1px solid #E5E7EB;border-radius:8px;overflow:hidden;background:#fff;margin-bottom:24px}
      .etp-iframe{width:100%;height:540px;border:none;display:block}
      .etp-send-section{border-top:1px solid #E5E7EB;padding-top:20px}
      .etp-card{background:#EEF2FF;background:linear-gradient(135deg,#E0E7FF 0%,#EDE9FE 100%);border:1px solid #A5B4FC;border-radius:12px;padding:16px 18px}
      .etp-card-header{display:flex;align-items:center;gap:8px;margin-bottom:12px}
      .etp-card-icon{display:flex;align-items:center;justify-content:center;width:30px;height:30px;background:#6366F1;border-radius:8px;flex-shrink:0}
      .etp-card-icon svg{width:15px;height:15px}
      .etp-card-title{font-size:13px;font-weight:700;color:#312E81;line-height:1.2}
      .etp-card-sub{font-size:11px;color:#818CF8}
      .etp-input-row{display:flex;gap:8px;align-items:center}
      .etp-input{flex:1;min-width:0;box-sizing:border-box;padding:9px 12px;font-size:13px;border:1px solid #A5B4FC;border-radius:8px;outline:none;color:#1E1B4B;background:#fff}
      .etp-feedback{display:flex;align-items:center;gap:8px;margin-top:10px;padding:9px 14px;border-radius:8px;font-size:13px}
      .etp-feedback svg{width:15px;height:15px;flex-shrink:0}
      .etp-feedback--success{background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D}
      .etp-feedback--error{background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C}
      .qs-send-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 9px 18px !important;
        background: linear-gradient(135deg,#6366F1,#8B5CF6) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        box-shadow: 0 2px 8px rgba(99,102,241,.4) !important;
        transition: opacity .15s !important;
      }
      .qs-send-btn:hover:not(:disabled) { opacity: .9 !important; }
      .qs-send-btn:disabled             { opacity: .5 !important; cursor: not-allowed !important; }
      .qs-ico  { width:14px; height:14px; flex-shrink:0; }
      .qs-spin { animation: qs-spin .8s linear infinite; }
      @keyframes qs-spin { to { transform: rotate(360deg); } }
    </style>
  </div>
</div>
