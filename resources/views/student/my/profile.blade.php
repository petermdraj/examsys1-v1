@extends('layouts.app')
@section('title', __('common.account_settings_title') . ' — ' . config('app.name'))

@push('styles')
<style>
.settings-wrap{min-width:0;max-width:680px;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;}
.form-label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text);}
.form-input{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);box-sizing:border-box;transition:border-color .15s;}
.form-input:focus{outline:none;border-color:var(--brand-primary);}
.form-input.is-error{border-color:var(--danger);}
.form-input:disabled{background:var(--surface-2);}
.form-error{display:block;font-size:12px;color:var(--danger);margin-top:4px;}
.btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:600;border:1.5px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;transition:all .15s;}
.btn-primary{background:var(--brand-primary);color:#fff;border-color:var(--brand-primary);}
.btn-primary:hover{opacity:.9;}
.btn-danger{background:var(--danger);color:#fff;border-color:var(--danger);}
.btn-danger:hover{opacity:.9;}
.toggle-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 0;border-bottom:1px solid var(--border);cursor:pointer;}
.toggle-row:last-child{border-bottom:none;}
.toggle-input{width:44px;height:24px;appearance:none;background:var(--border);border-radius:12px;cursor:pointer;transition:background .2s;flex:none;position:relative;}
.toggle-input:checked{background:var(--brand-primary);}
.toggle-input::after{content:'';position:absolute;top:2px;left:2px;width:20px;height:20px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.toggle-input:checked::after{transform:translateX(20px);}
@media(max-width:600px){.grid-2{grid-template-columns:1fr;}}
@media(max-width:700px){
  .myp-wrap{padding-left:16px;padding-right:16px;padding-bottom:100px;}
  .myp-heading{font-size:24px;}
  .myp-card,.myp-card-danger{padding:20px 16px;}
  .myp-modal-card{padding:24px 16px;}
}
/* Profile page extras */
.myp-wrap{padding-top:40px;padding-bottom:80px;}
.myp-heading{font-size:32px;font-weight:700;margin-bottom:8px;}
.myp-sub{margin-bottom:40px;}
.myp-card{padding:32px;margin-bottom:24px;}
.myp-card-danger{padding:32px;border-color:var(--danger);border-width:1px;}
.myp-section-h2{font-size:18px;font-weight:600;margin-bottom:24px;}
.myp-section-h2-sm{font-size:18px;font-weight:600;margin-bottom:8px;}
.myp-section-h2-purchase{font-size:18px;font-weight:600;margin-bottom:20px;}
.myp-avatar-row{display:flex;align-items:center;gap:20px;margin-bottom:28px;}
.myp-avatar-rel{position:relative;flex:none;}
.myp-avatar-img{width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);}
.myp-avatar-initials{background:var(--brand-primary);width:80px;height:80px;font-size:28px;flex:none;display:flex;align-items:center;justify-content:center;border-radius:50%;color:#fff;font-weight:700;}
.myp-avatar-edit{position:absolute;bottom:0;right:0;width:26px;height:26px;background:var(--brand-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid var(--surface);}
.myp-user-name{font-size:20px;font-weight:600;}
.myp-field-gap{gap:16px;margin-bottom:16px;}
.myp-email-wrap{margin-bottom:20px;}
.myp-email-note{font-size:12px;margin-top:4px;}
.myp-email-input{opacity:.6;cursor:not-allowed;}
.myp-col-gap{display:flex;flex-direction:column;gap:16px;margin-bottom:20px;}
.myp-grid-gap{gap:16px;}
.myp-notif-sub{margin-bottom:24px;font-size:14px;}
.myp-notif-col{display:flex;flex-direction:column;gap:16px;margin-bottom:20px;}
.myp-notif-label-name{font-weight:500;}
.myp-notif-label-sub{font-size:13px;}
.myp-danger-h2{font-size:18px;font-weight:600;color:var(--danger);margin-bottom:8px;}
.myp-danger-sub{margin-bottom:24px;font-size:14px;}
.myp-order-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);}
.myp-order-info{flex:1;min-width:0;}
.myp-order-title{font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.myp-order-date{font-size:12px;margin-top:2px;}
.myp-order-amount{font-weight:700;font-family:var(--font-display);color:var(--text);flex:none;}
.myp-order-badge{flex:none;font-size:11px;}
.myp-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;}
.myp-modal-card{width:100%;max-width:440px;padding:32px;}
.myp-modal-h3{font-size:20px;font-weight:700;margin-bottom:8px;}
.myp-modal-body{font-size:14px;color:var(--text-muted);margin-bottom:24px;}
.myp-modal-pw{margin-bottom:16px;}
.myp-modal-btns{display:flex;gap:12px;}
.myp-modal-btn{flex:1;}
/* Searchable select */
.ss-wrap{position:relative;}
.ss-trigger{width:100%;padding:10px 36px 10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;text-align:left;}
.ss-trigger:focus{outline:none;border-color:var(--brand-primary);}
.ss-trigger.open{border-color:var(--brand-primary);border-bottom-left-radius:0;border-bottom-right-radius:0;}
.ss-trigger .ss-caret{flex:none;transition:transform .15s;}
.ss-trigger.open .ss-caret{transform:rotate(180deg);}
.ss-dropdown{position:absolute;top:100%;left:0;right:0;background:var(--surface);border:1.5px solid var(--brand-primary);border-top:none;border-bottom-left-radius:10px;border-bottom-right-radius:10px;z-index:200;display:none;flex-direction:column;}
.ss-dropdown.open{display:flex;}
.ss-search-wrap{padding:8px 10px;border-bottom:1px solid var(--border);}
.ss-search{width:100%;padding:7px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);box-sizing:border-box;}
.ss-search:focus{outline:none;border-color:var(--brand-primary);}
.ss-list{max-height:220px;overflow-y:auto;overscroll-behavior:contain;}
.ss-option{padding:9px 14px;font-size:14px;cursor:pointer;color:var(--text);}
.ss-option:hover,.ss-option.focused{background:var(--brand-primary-soft,#ede9fe);color:var(--brand-primary);}
.ss-option.selected{font-weight:600;color:var(--brand-primary);}
.ss-empty{padding:10px 14px;font-size:13px;color:var(--text-muted,#9ca3af);}
</style>
@endpush
@section('content')
<div class="student-layout">
  @include('partials.student-nav')

  <div class="settings-wrap myp-wrap">

  <h1 class="myp-heading">{{ __('common.account_settings_title') }}</h1>
  <p class="muted myp-sub">{{ __('common.account_settings_sub') }}</p>

  {{-- Section 1: Profile Info --}}
  <div class="card myp-card">
    <h2 class="myp-section-h2">{{ __('common.profile_info_heading') }}</h2>

    <div class="myp-avatar-row">
      <div class="myp-avatar-rel">
        @if($user->avatar)
          <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" id="avatarPreview"
               class="myp-avatar-img">
        @else
          <span class="avatar avatar-lg myp-avatar-initials" id="avatarPreview">
            {{ strtoupper(substr($user->name, 0, 2)) }}
          </span>
        @endif
        <label for="avatarInput" title="{{ __('common.profile_info_heading') }}"
               class="myp-avatar-edit">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
        </label>
      </div>
      <div>
        <div class="myp-user-name">{{ $user->name }}</div>
        <div class="muted">{{ $user->email }}</div>
      </div>
    </div>

    {{-- Avatar upload (hidden file input, submits its own form) --}}
    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatarForm">
      @csrf
      <input type="file" id="avatarInput" name="avatar" accept="image/*" class="sr-only"
             onchange="
               const file=this.files[0];
               if(!file)return;
               const reader=new FileReader();
               reader.onload=e=>{
                 const p=document.getElementById('avatarPreview');
                 if(p.tagName==='IMG'){p.src=e.target.result;}
                 else{const img=document.createElement('img');img.id='avatarPreview';img.src=e.target.result;img.style='width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);';p.replaceWith(img);}
               };
               reader.readAsDataURL(file);
               document.getElementById('avatarForm').submit();
             ">
    </form>

    <form method="POST" action="{{ route('profile.update') }}">
      @csrf

      <div class="grid-2 myp-field-gap">
        <div>
          <label class="form-label">{{ __('common.profile_full_name') }}</label>
          <input type="text" name="name" class="form-input @error('name') is-error @enderror"
                 value="{{ old('name', $user->name) }}" required>
          @error('name')<span class="form-error">{{ $message }}</span>@enderror
        </div>
        <div>
          <label class="form-label">{{ __('common.profile_phone') }}</label>
          <input type="tel" name="phone" class="form-input"
                 value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210">
        </div>
        <div>
          <label class="form-label">{{ __('common.profile_country') }}</label>
          @php
            $ccOptions = collect(\App\Enums\CountryCodes::options())->map(fn($l,$c)=>['value'=>$c,'label'=>$l])->values();
            $ccSelected = old('country_code', $user->country_code ?? '');
            $ccLabel = \App\Enums\CountryCodes::options()[$ccSelected] ?? '';
          @endphp
          <div x-data="searchableSelect({
                 options: {{ $ccOptions->toJson() }},
                 selected: '{{ $ccSelected }}',
                 selectedLabel: '{{ addslashes($ccLabel) }}',
                 placeholder: '{{ __('common.select_country_placeholder') }}'
               })" class="ss-wrap" @click.outside="close">
            <input type="hidden" name="country_code" :value="selected">
            <button type="button" class="ss-trigger" :class="{open:open}" @click="toggle" @keydown.escape="close">
              <span x-text="selectedLabel || placeholder" :class="selectedLabel?'':' muted'"></span>
              <svg class="ss-caret" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="ss-dropdown" :class="{open:open}">
              <div class="ss-search-wrap">
                <input class="ss-search" type="text" placeholder="{{ __('common.search_country_placeholder') }}" x-model="query" x-ref="search" @keydown.arrow-down.prevent="moveFocus(1)" @keydown.arrow-up.prevent="moveFocus(-1)" @keydown.enter.prevent="selectFocused">
              </div>
              <div class="ss-list" x-ref="list">
                <template x-for="(opt,i) in filtered" :key="opt.value">
                  <div class="ss-option"
                       :class="{selected:opt.value===selected,focused:i===focusIdx}"
                       @click="pick(opt)"
                       @mouseenter="focusIdx=i"
                       x-text="opt.label">
                  </div>
                </template>
                <div class="ss-empty" x-show="filtered.length===0">{{ __('common.no_results_select') }}</div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <label class="form-label">{{ __('common.profile_timezone') }}</label>
          @php
            $tzOptions = collect(timezone_identifiers_list())->map(fn($t)=>['value'=>$t,'label'=>$t])->values();
            $tzSelected = old('timezone', $user->timezone ?? '');
          @endphp
          <div x-data="searchableSelect({
                 options: {{ $tzOptions->toJson() }},
                 selected: '{{ $tzSelected }}',
                 selectedLabel: '{{ $tzSelected }}',
                 placeholder: '{{ __('common.select_timezone_placeholder') }}'
               })" class="ss-wrap" @click.outside="close">
            <input type="hidden" name="timezone" :value="selected">
            <button type="button" class="ss-trigger" :class="{open:open}" @click="toggle" @keydown.escape="close">
              <span x-text="selectedLabel || placeholder" :class="selectedLabel?'':' muted'"></span>
              <svg class="ss-caret" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="ss-dropdown" :class="{open:open}">
              <div class="ss-search-wrap">
                <input class="ss-search" type="text" placeholder="{{ __('common.search_timezone_placeholder') }}" x-model="query" x-ref="search" @keydown.arrow-down.prevent="moveFocus(1)" @keydown.arrow-up.prevent="moveFocus(-1)" @keydown.enter.prevent="selectFocused">
              </div>
              <div class="ss-list" x-ref="list">
                <template x-for="(opt,i) in filtered" :key="opt.value">
                  <div class="ss-option"
                       :class="{selected:opt.value===selected,focused:i===focusIdx}"
                       @click="pick(opt)"
                       @mouseenter="focusIdx=i"
                       x-text="opt.label">
                  </div>
                </template>
                <div class="ss-empty" x-show="filtered.length===0">{{ __('common.no_results_select') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="myp-email-wrap">
        <label class="form-label">{{ __('common.profile_email_address') }}</label>
        <input type="email" class="form-input myp-email-input" value="{{ $user->email }}" disabled>
        <p class="muted myp-email-note">{{ __('common.profile_email_note') }}</p>
      </div>

      <button type="submit" class="btn btn-primary">{{ __('common.profile_save_btn') }}</button>
    </form>
  </div>

  {{-- Section 2: Change Password --}}
  <div class="card myp-card">
    <h2 class="myp-section-h2">{{ __('common.change_password_heading') }}</h2>

    <form method="POST" action="{{ route('profile.password') }}">
      @csrf

      <div class="myp-col-gap">
        <div>
          <label class="form-label">{{ __('common.current_password_label') }}</label>
          <input type="password" name="current_password"
                 class="form-input @error('current_password') is-error @enderror" required>
          @error('current_password')<span class="form-error">{{ $message }}</span>@enderror
        </div>
        <div class="grid-2 myp-grid-gap">
          <div>
            <label class="form-label">{{ __('common.new_password_label') }}</label>
            <input type="password" name="password"
                   class="form-input @error('password') is-error @enderror"
                   minlength="8" required>
            @error('password')<span class="form-error">{{ $message }}</span>@enderror
          </div>
          <div>
            <label class="form-label">{{ __('common.confirm_new_password_label') }}</label>
            <input type="password" name="password_confirmation" class="form-input" required>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">{{ __('common.update_password_btn') }}</button>
    </form>
  </div>

  {{-- Section 3: Notification Preferences --}}
  <div class="card myp-card">
    <h2 class="myp-section-h2-sm">{{ __('common.notifications_heading') }}</h2>
    <p class="muted myp-notif-sub">{{ __('common.notifications_sub') }}</p>

    @php $prefs = $user->notification_preferences ?? []; @endphp

    <form method="POST" action="{{ route('profile.notifications') }}">
      @csrf

      <div class="myp-notif-col">
        <label class="toggle-row">
          <div>
            <div class="myp-notif-label-name">{{ __('common.notify_quiz_results') }}</div>
            <div class="muted myp-notif-label-sub">{{ __('common.notify_quiz_results_sub') }}</div>
          </div>
          <input type="checkbox" name="notify_quiz_results" value="1"
                 {{ ($prefs['notify_quiz_results'] ?? true) ? 'checked' : '' }}
                 class="toggle-input">
        </label>

        <label class="toggle-row">
          <div>
            <div class="myp-notif-label-name">{{ __('common.notify_weekly_digest') }}</div>
            <div class="muted myp-notif-label-sub">{{ __('common.notify_weekly_digest_sub') }}</div>
          </div>
          <input type="checkbox" name="notify_weekly_digest" value="1"
                 {{ ($prefs['notify_weekly_digest'] ?? false) ? 'checked' : '' }}
                 class="toggle-input">
        </label>
      </div>

      <button type="submit" class="btn btn-primary">{{ __('common.save_preferences_btn') }}</button>
    </form>
  </div>

  {{-- Section 4: Danger Zone --}}
  <div class="card myp-card-danger">
    <h2 class="myp-danger-h2">{{ __('common.danger_zone_heading') }}</h2>
    <p class="muted myp-danger-sub">
      {{ __('common.danger_zone_body') }}
    </p>

    <button type="button" class="btn btn-danger" onclick="document.getElementById('delete-modal').style.display='flex'">
      {{ __('common.delete_account_btn') }}
    </button>
  </div>

  </div>{{-- /.settings-wrap --}}
</div>{{-- /.student-layout --}}

{{-- Delete Account Modal --}}
<div id="delete-modal" class="myp-modal-overlay">
  <div class="card myp-modal-card">
    <h3 class="myp-modal-h3">{{ __('common.delete_modal_heading') }}</h3>
    <p class="myp-modal-body">
      {{ __('common.delete_modal_body') }}
    </p>

    <form method="POST" action="{{ route('profile.destroy') }}">
      @csrf
      @method('DELETE')

      <div class="myp-modal-pw">
        <label class="form-label">{{ __('common.delete_modal_your_password') }}</label>
        <input type="password" name="password"
               class="form-input @error('password') is-error @enderror" required>
        @error('password')<span class="form-error">{{ $message }}</span>@enderror
      </div>

      <div class="myp-modal-btns">
        <button type="submit" class="btn btn-danger myp-modal-btn">{{ __('common.delete_confirm_btn') }}</button>
        <button type="button" class="btn myp-modal-btn"
                onclick="document.getElementById('delete-modal').style.display='none'">{{ __('common.cancel_btn') }}</button>
      </div>
    </form>
  </div>
</div>

@if(session('error_open_modal'))
<script>'use strict';document.getElementById('delete-modal').style.display='flex';</script>
@endif

@push('scripts')
<script>
'use strict';
function searchableSelect({ options, selected, selectedLabel, placeholder }) {
  return {
    options,
    selected,
    selectedLabel,
    placeholder,
    open: false,
    query: '',
    focusIdx: -1,
    get filtered() {
      const q = this.query.toLowerCase();
      return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
    },
    toggle() {
      this.open ? this.close() : this.openDropdown();
    },
    openDropdown() {
      this.open = true;
      this.query = '';
      this.focusIdx = -1;
      this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
    },
    close() { this.open = false; this.query = ''; },
    pick(opt) {
      this.selected = opt.value;
      this.selectedLabel = opt.label;
      this.close();
    },
    moveFocus(dir) {
      const len = this.filtered.length;
      if (!len) return;
      this.focusIdx = (this.focusIdx + dir + len) % len;
      this.$nextTick(() => {
        const items = this.$refs.list.querySelectorAll('.ss-option');
        if (items[this.focusIdx]) items[this.focusIdx].scrollIntoView({ block: 'nearest' });
      });
    },
    selectFocused() {
      if (this.focusIdx >= 0 && this.filtered[this.focusIdx]) {
        this.pick(this.filtered[this.focusIdx]);
      }
    }
  };
}
</script>
@endpush
@endsection
