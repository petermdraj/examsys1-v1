<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'DejaVu Sans',Arial,sans-serif;
    background:#FFFFFF;
    width:297mm; height:210mm;
    position:relative; overflow:hidden;
}

/* ── Left band (85.7mm = 300/1040 × 297mm) ── */
.band { position:absolute; top:0; left:0; width:85.7mm; height:210mm; background:#5a1a6e; }

/* Brand: top:14mm, left:10mm */
.lb-brand { position:absolute; top:14mm; left:10mm; }
.lb-icon {
    display:inline-block; width:10mm; height:10mm; border-radius:2.3mm;
    background:#E0A431; color:#4D2049;
    font-family:'DejaVu Serif',Georgia,serif; font-size:17px; font-weight:800;
    line-height:10mm; text-align:center; vertical-align:middle;
}
.lb-name {
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:17px; font-weight:700; color:#FBF4F8;
    vertical-align:middle; margin-left:2mm;
}
.lb-name b { color:#E0A431; }

/* Seal: centered horizontally in band (band=85.7mm, seal=38mm → left=(85.7-38)/2=23.85mm) */
.seal-wrap { position:absolute; top:62mm; left:23.85mm; width:38mm; height:38mm; }
.seal-outer {
    width:38mm; height:38mm; border-radius:19mm;
    background:#380d50;
    border:1.5px solid rgba(255,255,255,0.25);
    position:relative;
}
.seal-inner {
    position:absolute; top:2.5mm; left:2.5mm; right:2.5mm; bottom:2.5mm;
    border-radius:50%; border:1.5px dashed rgba(235,217,166,.6);
}
.seal-content {
    position:absolute; top:0; left:0; right:0; bottom:0;
    text-align:center; padding-top:6.5mm;
}
.seal-star { font-size:13px; color:#E0A431; line-height:1; }
.seal-pct  { font-family:'DejaVu Serif',Georgia,serif; font-size:20px; font-weight:700; color:#fff; line-height:1.2; margin-top:1mm; }
.seal-lab  { font-size:5.5px; letter-spacing:.18em; color:rgba(235,217,166,.7); margin-top:1.5mm; font-weight:700; }

/* QR: centered in band (band=85.7mm, qr=19mm → left=(85.7-19)/2=33.35mm) */
.lb-qr { position:absolute; top:143mm; left:33.35mm; width:19mm; }
.lb-qr img { width:19mm; height:19mm; border:1.5px solid rgba(255,255,255,.25); border-radius:1.5mm; }

/* Tagline */
.lb-tag {
    position:absolute; top:167mm; left:8mm; right:8mm;
    text-align:center; font-size:8.5px; line-height:1.55; color:rgba(251,244,248,.72);
}
.lb-tag b { color:#fff; display:block; font-size:10px; margin-bottom:2.5px; font-weight:700; }

/* ── Right panel (starts at 101mm) ── */

.r-eyebrow {
    position:absolute; top:16mm; left:101mm;
    font-size:8px; letter-spacing:.3em; text-transform:uppercase;
    color:#BF861A; font-weight:700;
}

.r-title {
    position:absolute; top:23mm; left:101mm; right:14mm;
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:34px; font-weight:700; color:#5a1a6e; line-height:1.08;
}

.r-present {
    position:absolute; top:62mm; left:101mm;
    font-size:10px; color:#938793; letter-spacing:.02em;
}

.r-name {
    position:absolute; top:67mm; left:101mm; right:14mm;
    font-family:'DejaVu Serif',Georgia,serif;
    font-size:40px; font-weight:600; color:#261C23; line-height:1.1;
}

.r-name-rule { position:absolute; top:86mm; left:101mm; width:90mm; height:2px; background:#C79A3A; }

.r-body {
    position:absolute; top:91mm; left:101mm; right:14mm;
    font-size:11.5px; color:#5E535B; line-height:1.75;
}
.r-body .quiz { font-weight:700; color:#5a1a6e; }

/* Signature: right-aligned, positioned after body */
.r-sig {
    position:absolute; top:126mm; right:14mm;
    text-align:right;
}
.r-sig-mark {
    font-family:'DejaVu Serif',Georgia,serif;
    font-style:italic; font-size:20px; color:#5a1a6e; margin-bottom:3px;
}
.r-sig-rule { height:1px; background:#261C23; opacity:.4; margin-bottom:5px; width:46mm; }
.r-sig-name { font-size:9px; font-weight:700; color:#261C23; }
.r-sig-role { font-size:8px; color:#938793; margin-top:2px; }

/* Meta footer */
.r-meta-line { position:absolute; bottom:22mm; left:101mm; right:14mm; height:1px; background:#EADFE6; }

.r-meta { position:absolute; bottom:11mm; left:101mm; right:14mm; }
.r-meta table { border-collapse:collapse; }
.r-meta td { vertical-align:top; padding-right:10mm; }
.ml { font-size:7px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#938793; margin-bottom:3px; }
.mv { font-family:'DejaVu Serif',Georgia,serif; font-size:15px; font-weight:600; color:#261C23; }
.mv-mono { font-family:'DejaVu Sans Mono',monospace; font-size:11px; font-weight:500; color:#5E535B; letter-spacing:.02em; }
</style>
</head>
<body>

<div class="band"></div>

{{-- Brand --}}
<div class="lb-brand">
    @if(!empty($logoUrl))
        <img src="{{ 'file://' . $logoUrl }}" alt="Logo"
             style="max-height:11mm;max-width:45mm;object-fit:contain;display:block;">
    @else
        <span class="lb-icon">{{ strtoupper(substr($quiz->creator->name, 0, 1)) }}</span>
        <span class="lb-name">{{ $quiz->creator->name }}</span>
    @endif
</div>

{{-- Seal (in left band) --}}
<div class="seal-wrap">
    <div class="seal-outer">
        <div class="seal-inner"></div>
        <div class="seal-content">
            <div class="seal-star">&#9733;</div>
            <div class="seal-pct">{{ number_format($attempt->percentage, 0) }}%</div>
            <div class="seal-lab">VERIFIED</div>
        </div>
    </div>
</div>

{{-- QR (in left band) --}}
<div class="lb-qr"><img src="{{ $qrBase64 }}" alt="Verify"></div>

{{-- Tagline --}}
<div class="lb-tag">
    <b>Scan to verify</b>
    {{ parse_url($verifyUrl, PHP_URL_HOST) }}
</div>

{{-- Right: Eyebrow --}}
<div class="r-eyebrow">Certificate of Completion</div>

{{-- Right: Title --}}
<div class="r-title">Awarded for<br><em style="color:#BF861A;">outstanding</em> performance</div>

{{-- Right: Presented to --}}
<div class="r-present">Presented to</div>

{{-- Right: Name --}}
<div class="r-name">{{ $user->name }}</div>

{{-- Right: Name rule --}}
<div class="r-name-rule"></div>

{{-- Right: Body --}}
<div class="r-body">
    for successfully completing the <span class="quiz">{{ $quiz->title }}</span>
    assessment, meeting all proficiency criteria set by the course author
    with a score of <strong>{{ number_format($attempt->percentage, 1) }}%</strong>
    ({{ $attempt->score }}/{{ $attempt->total_marks }} marks) &mdash; {{ $attempt->is_passed ? 'Distinction' : 'Completed' }}.
</div>

{{-- Right: Signature --}}
<div class="r-sig">
    <div class="r-sig-mark">{{ $quiz->creator->name }}</div>
    <div class="r-sig-rule"></div>
    <div class="r-sig-name">{{ $quiz->creator->name }}</div>
    <div class="r-sig-role">Quiz Author</div>
</div>

{{-- Right: Meta divider --}}
<div class="r-meta-line"></div>

{{-- Right: Meta details --}}
<div class="r-meta">
    <table>
        <tr>
            <td>
                <div class="ml">Score</div>
                <div class="mv">{{ number_format($attempt->percentage, 1) }}% &middot; {{ $attempt->is_passed ? 'Distinction' : 'Completed' }}</div>
            </td>
            <td>
                <div class="ml">Date Issued</div>
                <div class="mv">{{ $cert->issued_at->format('d M Y') }}</div>
            </td>
            <td>
                <div class="ml">Certificate ID</div>
                <div class="mv-mono">{{ strtoupper(substr($cert->uuid, 0, 18)) }}</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
