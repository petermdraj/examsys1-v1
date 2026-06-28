<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Certificate;
use App\Services\Exam\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certService) {}

    public function verify(string $uuid)
    {
        $cert = Certificate::with(['user', 'quiz'])->where('uuid', $uuid)->firstOrFail();
        return view('customer.certificate', compact('cert'));
    }

    public function download(Request $request, string $attempt)
    {
        $attempt = Attempt::with(['quiz', 'user'])
            ->where('id', $attempt)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        abort_unless($attempt->is_passed && $attempt->quiz->certificate_enabled, 403);

        $cert = $this->certService->generate($attempt);

        $path = storage_path('app/public/' . $cert->pdf_path);

        if (! file_exists($path)) {
            abort(404, 'Certificate file not found. Please try again.');
        }

        $filename = 'certificate-' . str($attempt->quiz->title)->slug() . '.pdf';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
