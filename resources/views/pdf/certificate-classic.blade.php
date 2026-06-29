<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'DejaVu Sans',Arial,sans-serif;
    background:#FFFDFB;
    width:297mm; height:210mm;
    position:relative; overflow:hidden;
}

/* ── Double border frame ── */
.b1 { position:absolute; top:6.3mm; left:6.3mm; right:6.3mm; bottom:6.3mm; border:2px solid #6C2E63; }
.b2 { position:absolute; top:9.5mm; left:9.5mm; right:9.5mm; bottom:9.5mm; border:1px solid #C79A3A; }

/* ── Corner ornaments (inline SVG, absolutely positioned) ── */
.cn { position:absolute; width:13.2mm; height:13.2mm; }
.cn-tl { top:8.6mm;    left:8.6mm; }
.cn-tr { top:8.6mm;    right:8.6mm; }
.cn-bl { bottom:8.6mm; left:8.6mm; }
.cn-br { bottom:8.6mm; right:8.6mm; }

/* ── Brand crest ── */
.crest { position:absolute; top:37mm; left:0; right:0; text-align:center; }
.crest-icon {
    display:inline-block; width:9.7mm; height:9.7mm; border-radius:2.5mm;
    background:#6C2E63; color:#E0A431;
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:19px; font-weight:bold; line-height:9.7mm;
    text-align:center; vertical-align:middle;
}
.crest-name {
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:19px; font-weight:700; color:#6C2E63;
    vertical-align:middle; margin-left:2.5mm;
}
.crest-name b { color:#BF861A; }

/* ── Main title ── */
.title {
    position:absolute; top:50mm; left:27mm; right:27mm;
    text-align:center;
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:40px; font-weight:700; color:#6C2E63; line-height:1.04;
}

/* ── Subtitle ── */
.title-sub {
    position:absolute; top:65mm; left:27mm; right:27mm;
    text-align:center;
    font-family:'DejaVu Serif',Georgia,serif;
    font-style:italic; font-size:16px; color:#5E535B;
}

/* ── Flourish: line ◆ line ── */
.fl-wrap { position:absolute; top:74.5mm; left:50%; width:68mm; margin-left:-34mm; }
.fl-line-l { position:absolute; top:3.5mm; left:0; width:28mm; height:1px; background:#C79A3A; opacity:0.7; }
.fl-dot   { position:absolute; top:1.5mm; left:29.5mm; }
.fl-line-r { position:absolute; top:3.5mm; right:0; width:28mm; height:1px; background:#C79A3A; opacity:0.7; }

/* ── Present / certify label ── */
.present {
    position:absolute; top:83mm; left:0; right:0;
    text-align:center; font-size:12px; color:#5E535B; letter-spacing:.02em;
}

/* ── Recipient name ── */
.name {
    position:absolute; top:88mm; left:16mm; right:16mm;
    text-align:center;
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:52px; font-weight:600; color:#261C23; line-height:1.1;
}

/* ── Name underline ── */
.name-rule {
    position:absolute; top:110mm;
    left:50%; width:120mm; margin-left:-60mm;
    height:1px; background:#EADFE6;
}

/* ── Body copy ── */
.body {
    position:absolute; top:116mm; left:24mm; right:24mm;
    text-align:center; font-size:13px; color:#5E535B; line-height:1.75;
}
.body .quiz  { font-weight:700; color:#6C2E63; }
.body strong { color:#261C23; }

/* ── Medallion seal ── */
.seal-outer {
    position:absolute; top:148mm; right:27mm;
    width:33.7mm; height:33.7mm; border-radius:16.85mm;
    background:#6C2E63;
    border:3px solid #C79A3A;
}
.seal-inner {
    position:absolute; top:2.2mm; left:2.2mm; right:2.2mm; bottom:2.2mm;
    border-radius:50%; border:1.5px dashed #EBD9A6;
}
.seal-content {
    position:absolute; top:0; left:0; right:0; bottom:0;
    text-align:center; padding-top:5.5mm;
}
.seal-star { font-family:'DejaVu Sans',Arial,sans-serif; font-size:14px; color:#E0A431; line-height:1; }
.seal-pct  { font-family:'DejaVu Serif',Georgia,serif; font-size:17px; font-weight:700; color:#fff; line-height:1.1; margin-top:1mm; }
.seal-lab  { font-family:'DejaVu Sans',Arial,sans-serif; font-size:5.5px; letter-spacing:.18em; color:#EBD9A6; margin-top:1.5mm; }

/* ── Footer row ── */
.foot {
    position:absolute; top:170mm; left:27mm; right:89mm;
}
.sig-mark {
    font-family:'DejaVu Serif',Georgia,serif;
    font-style:italic; font-size:22px; color:#6C2E63; margin-bottom:5px;
}
.sig-rule { height:1px; background:#261C23; opacity:.4; margin-bottom:6px; }
.sig-name { font-size:10px; font-weight:700; color:#261C23; }
.sig-role { font-size:9px; color:#938793; margin-top:2px; }

.meta { text-align:center; }
.meta-qr { width:17mm; height:17mm; margin:0 auto 2.5mm; }
.meta-qr img { width:17mm; height:17mm; }
.meta-vid {
    font-family:'DejaVu Sans Mono',monospace;
    font-size:8px; color:#5E535B; letter-spacing:.04em; line-height:1.7;
}
.meta-vid b { color:#261C23; }
</style>
</head>
<body>

{{-- Double border --}}
<div class="b1"></div>
<div class="b2"></div>

{{-- Corner ornaments: top-left --}}
<div class="cn cn-tl">
<svg viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
  <path d="M2 44V14C2 7 7 2 14 2h30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
  <path d="M9 44V17c0-5 3-8 8-8h27" fill="none" stroke="#BF861A" stroke-width="1.4"/>
</svg>
</div>

{{-- top-right --}}
<div class="cn cn-tr">
<svg viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
  <g transform="scale(-1,1) translate(-46,0)">
    <path d="M2 44V14C2 7 7 2 14 2h30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
    <path d="M9 44V17c0-5 3-8 8-8h27" fill="none" stroke="#BF861A" stroke-width="1.4"/>
  </g>
</svg>
</div>

{{-- bottom-left --}}
<div class="cn cn-bl">
<svg viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
  <g transform="scale(1,-1) translate(0,-46)">
    <path d="M2 44V14C2 7 7 2 14 2h30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
    <path d="M9 44V17c0-5 3-8 8-8h27" fill="none" stroke="#BF861A" stroke-width="1.4"/>
  </g>
</svg>
</div>

{{-- bottom-right --}}
<div class="cn cn-br">
<svg viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
  <g transform="scale(-1,-1) translate(-46,-46)">
    <path d="M2 44V14C2 7 7 2 14 2h30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
    <path d="M9 44V17c0-5 3-8 8-8h27" fill="none" stroke="#BF861A" stroke-width="1.4"/>
  </g>
</svg>
</div>

{{-- Brand crest / Logo --}}
<div class="crest">
    @if(!empty($logoUrl))
        <img src="{{ 'file://' . $logoUrl }}" alt="Logo"
             style="max-height:12mm;max-width:50mm;vertical-align:middle;object-fit:contain;">
    @else
        <span class="crest-icon">{{ strtoupper(substr($quiz->lecturer->name, 0, 1)) }}</span>
        <span class="crest-name">{{ $quiz->lecturer->name }}</span>
    @endif
</div>

{{-- Title --}}
<div class="title">Certificate of Achievement</div>

{{-- Subtitle --}}
<div class="title-sub">Verified proficiency in a platform assessment</div>

{{-- Flourish —◆— --}}
<div class="fl-wrap">
    <div class="fl-line-l"></div>
    <div class="fl-dot">
        <svg width="8" height="8" viewBox="0 0 8 8" xmlns="http://www.w3.org/2000/svg">
            <rect x="1" y="1" width="6" height="6" fill="#C79A3A" transform="rotate(45 4 4)"/>
        </svg>
    </div>
    <div class="fl-line-r"></div>
</div>

{{-- Present --}}
<div class="present">This is to certify that</div>

{{-- Name --}}
<div class="name">{{ $user->name }}</div>

{{-- Name rule --}}
<div class="name-rule"></div>

{{-- Body copy --}}
<div class="body">
    {{ __('certificate.has_completed') }} <span class="quiz">{{ $quiz->title }}</span>
    on {{ $cert->issued_at->format('d F Y') }}, demonstrating advanced command of the subject
    by scoring <strong>{{ number_format($attempt->percentage, 1) }}%</strong>
    ({{ $attempt->score }}/{{ $attempt->total_marks }} marks) &mdash; {{ $attempt->is_passed ? 'PASSED' : 'COMPLETED' }}.
</div>

{{-- Seal --}}
<div class="seal-outer">
    <div class="seal-inner"></div>
    <div class="seal-content">
        <div class="seal-star">&#9733;</div>
        <div class="seal-pct">{{ number_format($attempt->percentage, 0) }}%</div>
        <div class="seal-lab">VERIFIED</div>
    </div>
</div>

{{-- Footer --}}
<div class="foot">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td width="60%" style="vertical-align:bottom; padding-right:8mm;">
                <div class="sig-mark">{{ $quiz->lecturer->name }}</div>
                <div class="sig-rule"></div>
                <div class="sig-name">{{ $quiz->lecturer->name }}</div>
                <div class="sig-role">{{ __('certificate.sig_author_role') }} &amp; Instructor</div>
            </td>
            <td width="40%" style="vertical-align:bottom; text-align:center;">
                <div class="meta-qr"><img src="{{ $qrBase64 }}" alt="QR"></div>
                <div class="meta-vid">
                    <b>{{ strtoupper(substr($cert->uuid, 0, 14)) }}</b><br>
                    Issued {{ $cert->issued_at->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
