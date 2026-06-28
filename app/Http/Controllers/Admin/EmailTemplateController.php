<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Settings\PlatformSettings;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailTemplateController extends Controller
{
    public function sendTest(Request $request, EmailTemplate $template)
    {
        abort_if(auth()->user()?->role !== 'super_admin', 403);

        $request->validate(['email' => 'required|email']);

        $settings = app(PlatformSettings::class);

        if (empty($settings->mail_host) || empty($settings->mail_from_address)) {
            return response()->json([
                'ok'      => false,
                'message' => 'SMTP is not configured. Go to Settings → Email and set Host + From address.',
            ], 422);
        }

        $dummyValues = [
            'user_name'        => 'Alex Johnson',
            'first_name'       => 'Alex',
            'last_name'        => 'Johnson',
            'email'            => 'alex.johnson@example.com',
            'user_email'       => 'alex.johnson@example.com',
            'quiz_title'       => 'Introduction to PHP OOP',
            'quiz_url'         => config('app.url') . '/quiz/intro-php-oop',
            'quiz_price'       => '₹499',
            'quiz_duration'    => '45 minutes',
            'quiz_questions'   => '40',
            'pass_percentage'  => '60%',
            'amount'           => '₹499.00',
            'currency'         => 'INR',
            'order_id'         => 'ORD-' . now()->format('Ymd') . '-8821',
            'paid_at'          => now()->format('d M Y, h:i A'),
            'payment_method'   => 'Razorpay',
            'score'            => '32.8',
            'percentage'       => '82',
            'total_marks'      => '40',
            'marks_earned'     => '32.8',
            'attempt_number'   => '1',
            'time_taken'       => '00:38:14',
            'result'           => 'Pass',
            'result_label'     => 'PASSED',
            'result_url'       => config('app.url') . '/attempt/preview/result',
            'certificate_url'  => config('app.url') . '/certificate/preview',
            'certificate_id'   => 'CERT-PHP-2024-00421',
            'payout_amount'    => '₹3,250.00',
            'payout_status'    => 'Processed',
            'payout_reference' => 'PAY-REF-' . now()->format('Ymd'),
            'app_name'         => config('app.name', 'Quizora'),
            'app_url'          => config('app.url'),
            'support_email'    => 'support@' . parse_url(config('app.url'), PHP_URL_HOST),
            'login_url'        => config('app.url') . '/login',
            'reset_url'        => config('app.url') . '/password/reset/preview-token',
            'verify_url'       => config('app.url') . '/email/verify/preview',
            'current_year'     => now()->year,
            'date'             => now()->format('d M Y'),
        ];

        $vars = collect($template->variables ?? [])
            ->pluck('name')
            ->mapWithKeys(fn ($n) => [$n => $dummyValues[$n] ?? '[' . strtoupper($n) . ']'])
            ->toArray();

        $from = $settings->mail_from_address;
        $name = $settings->mail_from_name ?: config('app.name');

        try {
            \Illuminate\Support\Facades\Mail::mailer('smtp')->send([], [], function ($msg) use ($request, $template, $vars, $from, $name) {
                $msg->from($from, $name)
                    ->to($request->email)
                    ->subject('[TEST] ' . $template->renderSubject($vars))
                    ->html($template->renderRaw($vars));
            });
        } catch (TransportExceptionInterface $e) {
            return response()->json(['ok' => false, 'message' => 'SMTP error: ' . $e->getMessage()], 500);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Mail error: ' . $e->getMessage()], 500);
        }

        return response()->json(['ok' => true]);
    }
}
