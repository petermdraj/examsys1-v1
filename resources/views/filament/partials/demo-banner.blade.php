@if(config('quizora.demo_mode'))
<div style="
    background: linear-gradient(90deg, #d97706 0%, #b45309 100%);
    color: #fff;
    padding: 7px 20px;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.025em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    position: relative;
    z-index: 100;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    <span>⚡ Demo Mode — This is a live demo. Edits &amp; deletions are disabled.</span>
</div>
@endif
