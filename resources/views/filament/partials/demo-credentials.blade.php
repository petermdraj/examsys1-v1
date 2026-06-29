@if(config('examsys.demo_mode'))
@php
    $creds = [
        'admin'   => ['email' => 'admin@quiz.com',  'password' => 'password', 'label' => 'Admin',   'color' => '#7c3aed'],
        'lecturer' => ['email' => 'priya@demo.quiz',    'password' => 'password', 'label' => 'Lecturer', 'color' => '#0ea5e9'],
        'student' => ['email' => 'alice@demo.quiz',    'password' => 'password', 'label' => 'Student','color' => '#22c55e'],
    ];
    $role = $role ?? 'admin';
    $show = isset($roles) ? $roles : [$role];
@endphp
<div style="margin-bottom:20px;border-radius:10px;overflow:hidden;border:1px solid rgba(124,58,237,.25);font-family:inherit">
  <div style="background:rgba(124,58,237,.12);padding:8px 14px;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a78bfa;display:flex;align-items:center;gap:6px">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/></svg>
    Demo Credentials
  </div>
  @foreach($show as $r)
  @if(isset($creds[$r]))
  @php $c = $creds[$r]; @endphp
  <div style="padding:10px 14px;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,.06);' : '' }}display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(0,0,0,.15)">
    <div style="display:flex;align-items:center;gap:8px">
      <span style="background:{{ $c['color'] }}22;color:{{ $c['color'] }};border:1px solid {{ $c['color'] }}44;border-radius:5px;padding:2px 8px;font-size:10px;font-weight:700;letter-spacing:.05em">{{ $c['label'] }}</span>
      <span style="font-size:12px;color:rgba(255,255,255,.75);font-family:monospace">{{ $c['email'] }}</span>
      <span style="color:rgba(255,255,255,.3);font-size:11px">·</span>
      <span style="font-size:12px;color:rgba(255,255,255,.5);font-family:monospace">{{ $c['password'] }}</span>
    </div>
    <button
      type="button"
      onclick="
        var f = document.querySelector('form');
        if(f){
          var email = f.querySelector('[name=email],[type=email]');
          var pass  = f.querySelector('[name=password],[type=password]');
          if(email){ email.value='{{ $c['email'] }}'; email.dispatchEvent(new Event('input')); }
          if(pass){ pass.value='{{ $c['password'] }}'; pass.dispatchEvent(new Event('input')); }
        }
      "
      style="font-size:11px;font-weight:600;color:{{ $c['color'] }};background:{{ $c['color'] }}18;border:1px solid {{ $c['color'] }}33;border-radius:6px;padding:4px 10px;cursor:pointer;white-space:nowrap;transition:.15s"
      onmouseover="this.style.background='{{ $c['color'] }}30'"
      onmouseout="this.style.background='{{ $c['color'] }}18'"
    >Use →</button>
  </div>
  @endif
  @endforeach
</div>
@endif
