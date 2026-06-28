<?php

namespace App\Services\Exam;

use App\Models\Attempt;
use App\Models\Certificate;
use App\Settings\PlatformSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;

class CertificateService
{
    public function generate(Attempt $attempt): Certificate
    {
        $cert = Certificate::firstOrCreate(
            ['attempt_id' => $attempt->id],
            [
                'user_id'   => $attempt->user_id,
                'quiz_id'   => $attempt->quiz_id,
                'uuid'      => (string) Str::uuid(),
                'issued_at' => now(),
            ]
        );

        $filename   = "certificates/{$cert->uuid}.pdf";
        $fileExists = \Storage::exists("public/{$filename}");

        if ($cert->wasRecentlyCreated || ! $cert->pdf_path || ! $fileExists) {
            $template = match ($attempt->quiz->certificate_template ?? 'classic') {
                'contemporary' => 'pdf.certificate-contemporary',
                default        => 'pdf.certificate-classic',
            };

            $verifyUrl = route('certificate.verify', $cert->uuid);
            $qrOptions = new QROptions(['outputType' => 'png', 'scale' => 5, 'imageBase64' => true]);
            $qrBase64  = (new QRCode($qrOptions))->render($verifyUrl);

            // Resolve certificate logo:
            // 1. Creator's own logo (if plan allows custom logo)
            // 2. Platform default certificate logo
            // 3. Platform app logo
            $creator     = $attempt->quiz->creator;
            $creatorPlan = $creator?->activeSubscription()->with('plan')->first()?->plan;
            $logoPath    = null;
            if ($creatorPlan?->allow_custom_certificate_logo && $creator?->certificate_logo) {
                $logoPath = $creator->certificate_logo;
            } else {
                $settings = app(PlatformSettings::class);
                $logoPath = $settings->certificate_logo ?: $settings->app_logo;
            }
            // DomPDF cannot fetch HTTP URLs — resolve to absolute local file path
            if ($logoPath) {
                $localPath = \Storage::disk('public')->path($logoPath);
                $logoUrl   = file_exists($localPath) ? $localPath : null;
            } else {
                $logoUrl = null;
            }

            $pdf = Pdf::loadView($template, [
                'attempt'   => $attempt,
                'quiz'      => $attempt->quiz,
                'user'      => $attempt->user,
                'cert'      => $cert,
                'qrBase64'  => $qrBase64,
                'verifyUrl' => $verifyUrl,
                'logoUrl'   => $logoUrl,
            ])->setPaper('a4', 'landscape');

            \Storage::disk('public')->makeDirectory('certificates');
            \Storage::disk('public')->put($filename, $pdf->output());
            $cert->update(['pdf_path' => $filename]);
        }

        return $cert;
    }

    public function getVerifyUrl(Certificate $cert): string
    {
        return route('certificate.verify', $cert->uuid);
    }
}
