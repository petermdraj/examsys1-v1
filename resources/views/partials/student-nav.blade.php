@php
$navItems = [
    ['route' => 'my.dashboard',    'label' => 'Dashboard',     'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
    ['route' => 'my.quizzes',      'label' => 'My Quizzes',    'icon' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>'],
    ['route' => 'my.attempts',     'label' => 'Attempts',      'icon' => '<path d="M12 3a9 9 0 109 9"/><path d="M12 3v9l6-3"/>'],
    ['route' => 'my.certificates', 'label' => 'Certificates',  'icon' => '<circle cx="12" cy="9" r="5"/><path d="M9 13l-1 7 4-2 4 2-1-7"/>'],
    ['route' => 'my.favourites',   'label' => 'Favourites',    'icon' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
    ['route' => 'profile.show',    'label' => 'Settings',      'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
];
$currentRoute = Route::currentRouteName();
@endphp

{{-- Desktop sidebar + mobile tab bar --}}
<aside class="student-nav">
    @foreach($navItems as $item)
        @php $active = $currentRoute === $item['route']; @endphp
        <a href="{{ route($item['route']) }}" class="sn-item {{ $active ? 'sn-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</aside>

<style>
.student-layout{display:grid;grid-template-columns:220px 1fr;gap:32px;align-items:start;max-width:1100px;margin:0 auto;padding:40px 24px 80px;}
.student-nav{position:sticky;top:80px;display:flex;flex-direction:column;gap:4px;}
.sn-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;font-size:14px;font-weight:500;color:var(--text-muted,#6b7280);text-decoration:none;transition:background .15s,color .15s;}
.sn-item svg{width:18px;height:18px;flex:none;}
.sn-item:hover{background:var(--surface-2,#f3f4f6);color:var(--text);}
.sn-active{background:var(--brand-primary-soft,#ede9fe)!important;color:var(--brand-primary)!important;font-weight:600;}
.student-content{min-width:0;}
@media(max-width:700px){
  .student-layout{grid-template-columns:1fr;padding:0 0 80px;}
  .student-nav{position:fixed;bottom:0;left:0;right:0;top:auto;flex-direction:row;background:var(--surface);border-top:1px solid var(--border);z-index:100;padding:6px 4px;gap:0;box-shadow:0 -2px 12px rgba(0,0,0,.08);}
  .sn-item{flex:1;flex-direction:column;gap:2px;padding:6px 2px;border-radius:8px;font-size:10px;text-align:center;justify-content:center;white-space:nowrap;overflow:hidden;}
  .sn-item svg{width:20px;height:20px;}
  .student-content{padding:20px 16px 0;}
}
@media(max-width:420px){
  .sn-item span{display:none;}
  .sn-item{padding:10px 4px;}
  .sn-item svg{width:22px;height:22px;}
  .student-nav{padding:6px 8px;}
}
</style>
