<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #fff;
    width: 297mm;
    height: 210mm;
    overflow: hidden;
}

/* ── Page background ── */
.page {
    width: 297mm;
    height: 210mm;
    position: relative;
    background: #fff;
}

/* Deep purple fill on left third */
.bg-left {
    position: absolute;
    top: 0; left: 0;
    width: 80mm; height: 210mm;
    background: #4A1460;
}

/* Soft diagonal accent strip */
.bg-accent {
    position: absolute;
    top: 0; left: 68mm;
    width: 24mm; height: 210mm;
    background: linear-gradient(to right, #6C2E63, #fff0);
    opacity: .35;
}

/* Gold top bar */
.bar-top {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 6px;
    background: linear-gradient(to right, #E0A431, #F5C842, #E0A431);
}

/* Gold bottom bar */
.bar-bottom {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 6px;
    background: linear-gradient(to right, #E0A431, #F5C842, #E0A431);
}

/* Decorative corner circles on the dark panel */
.corner-tl {
    position: absolute;
    top: -30mm; left: -30mm;
    width: 80mm; height: 80mm;
    border-radius: 50%;
    border: 12px solid rgba(224,164,49,.25);
}
.corner-bl {
    position: absolute;
    bottom: -25mm; left: -25mm;
    width: 70mm; height: 70mm;
    border-radius: 50%;
    border: 10px solid rgba(224,164,49,.18);
}

/* ── LEFT PANEL content ── */
.left-panel {
    position: absolute;
    top: 0; left: 0;
    width: 80mm; height: 210mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0 10mm;
}

.brand-name {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 6px;
    color: #E0A431;
    text-transform: uppercase;
    margin-bottom: 10mm;
}

/* Medal circle */
.medal {
    width: 42mm;
    height: 42mm;
    border-radius: 50%;
    background: linear-gradient(145deg, #F5C842 0%, #E0A431 50%, #C68B1A 100%);
    border: 3px solid rgba(255,255,255,.3);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8mm;
    box-shadow: 0 4px 20px rgba(0,0,0,.35);
    position: relative;
}
.medal-inner {
    width: 34mm;
    height: 34mm;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,.5);
    display: flex;
    align-items: center;
    justify-content: center;
}
.medal-star {
    font-size: 26px;
    color: #fff;
    line-height: 1;
}

.cert-type {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,.55);
    margin-bottom: 3mm;
}

.cert-of {
    font-size: 16px;
    color: #fff;
    font-style: italic;
    line-height: 1.3;
}

.left-divider {
    width: 24mm;
    height: 1px;
    background: linear-gradient(to right, transparent, #E0A431, transparent);
    margin: 6mm auto;
}

.issued-label {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(255,255,255,.45);
    margin-bottom: 2mm;
}
.issued-date {
    font-size: 12px;
    color: #E0A431;
}

/* ── RIGHT PANEL content ── */
.right-panel {
    position: absolute;
    top: 0; left: 82mm;
    width: 211mm; height: 210mm;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 14mm 16mm 14mm 14mm;
}

.presented-to {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 4mm;
}

.recipient-name {
    font-size: 42px;
    color: #4A1460;
    font-style: italic;
    line-height: 1.05;
    margin-bottom: 5mm;
    border-bottom: 2px solid #E0A431;
    padding-bottom: 4mm;
}

.has-completed {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11px;
    color: #666;
    margin-bottom: 3mm;
    letter-spacing: .5px;
}

.quiz-title {
    font-size: 20px;
    color: #1E1B4B;
    font-weight: bold;
    line-height: 1.25;
    margin-bottom: 6mm;
}

/* Score badge row */
.score-row {
    display: flex;
    gap: 5mm;
    margin-bottom: 8mm;
}
.score-badge {
    padding: 3mm 6mm;
    border-radius: 3mm;
    text-align: center;
    font-family: 'DejaVu Sans', Arial, sans-serif;
}
.score-badge.primary {
    background: #4A1460;
    color: #fff;
}
.score-badge.gold {
    background: #FEF3C7;
    border: 1.5px solid #E0A431;
    color: #92400e;
}
.score-badge .label {
    font-size: 7px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    opacity: .75;
    margin-bottom: 1mm;
}
.score-badge .value {
    font-size: 16px;
    font-weight: bold;
    line-height: 1;
}

/* Footer */
.cert-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: auto;
    padding-top: 6mm;
    border-top: 1px solid #e5e7eb;
}

.sig-block { text-align: center; }
.sig-line {
    width: 40mm; height: 1px;
    background: #333;
    margin: 0 auto 2mm;
}
.sig-name {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9px;
    color: #444;
    font-weight: bold;
}
.sig-role {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 7.5px;
    color: #999;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 1mm;
}

.cert-id-block {
    text-align: center;
    font-family: 'DejaVu Sans', Arial, sans-serif;
}
.cert-id-label {
    font-size: 7px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 1.5mm;
}
.cert-id-value {
    font-size: 8px;
    color: #4A1460;
    font-weight: bold;
    letter-spacing: .5px;
    word-break: break-all;
    max-width: 60mm;
}
.verify-url {
    font-size: 7px;
    color: #aaa;
    margin-top: 1.5mm;
}
</style>
</head>
<body>
<div class="page">

    <!-- Background layers -->
    <div class="bg-left">
        <div class="corner-tl"></div>
        <div class="corner-bl"></div>
    </div>
    <div class="bg-accent"></div>
    <div class="bar-top"></div>
    <div class="bar-bottom"></div>

    <!-- Left panel -->
    <div class="left-panel">
        <div class="brand-name">Quizora</div>

        <div class="medal">
            <div class="medal-inner">
                <div class="medal-star">★</div>
            </div>
        </div>

        <div class="cert-type">{{ __('certificate.official_label') }}</div>
        <div class="cert-of">{{ __('certificate.cert_of') }}</div>

        <div class="left-divider"></div>

        <div class="issued-label">{{ __('certificate.issued_on') }}</div>
        <div class="issued-date">{{ $cert->issued_at->format('d M Y') }}</div>
    </div>

    <!-- Right panel -->
    <div class="right-panel">

        <div class="presented-to">{{ __('certificate.presented_to') }}</div>

        <div class="recipient-name">{{ $user->name }}</div>

        <div class="has-completed">{{ __('certificate.has_completed') }}</div>
        <div class="quiz-title">{{ $quiz->title }}</div>

        <div class="score-row">
            <div class="score-badge primary">
                <div class="label">{{ __('certificate.label_score') }}</div>
                <div class="value">{{ number_format($attempt->percentage, 1) }}%</div>
            </div>
            <div class="score-badge gold">
                <div class="label">{{ __('certificate.label_marks') }}</div>
                <div class="value">{{ $attempt->score }} / {{ $attempt->total_marks }}</div>
            </div>
            <div class="score-badge gold">
                <div class="label">{{ __('certificate.label_status') }}</div>
                <div class="value">{{ $attempt->is_passed ? __('certificate.status_passed') : __('certificate.status_completed') }}</div>
            </div>
        </div>

        <div class="cert-footer">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-name">{{ __('certificate.sig_platform_name') }}</div>
                <div class="sig-role">{{ __('certificate.sig_platform_role') }}</div>
            </div>

            <div class="cert-id-block">
                <div class="cert-id-label">{{ __('certificate.cert_id_label') }}</div>
                <div class="cert-id-value">{{ $cert->uuid }}</div>
                <div class="verify-url">{{ __('certificate.verify_url_prefix') }}: {{ route('certificate.verify', $cert->uuid) }}</div>
            </div>

            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $quiz->creator->name }}</div>
                <div class="sig-role">{{ __('certificate.sig_author_role') }}</div>
            </div>
        </div>

    </div>

</div>
</body>
</html>
