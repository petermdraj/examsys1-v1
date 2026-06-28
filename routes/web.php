<?php

use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Api\AiGenerateController;
use App\Http\Controllers\Api\QuestionBankController;
use App\Http\Controllers\Api\QuestionCollectionController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Customer\AttemptController;
use App\Http\Controllers\Customer\CertificateController;
use App\Http\Controllers\Customer\ForCreatorsController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PricingController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\QuizController;
use App\Http\Controllers\Customer\SubscriptionController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

// Installer routes — EnsureInstalled middleware gates access in both directions
Route::get('/install',          \App\Livewire\Installer\InstallerWizard::class)->name('installer');
Route::get('/install/database', \App\Livewire\Installer\InstallerWizard::class)->name('installer.database');
Route::get('/install/app',      \App\Livewire\Installer\InstallerWizard::class)->name('installer.app');
Route::get('/install/ready',    \App\Livewire\Installer\InstallerWizard::class)->name('installer.ready');

// Customer portal (public)
Route::get('/',            [HomeController::class, 'index'])->name('home');
Route::get('/quizzes',     [QuizController::class, 'index'])->name('quizzes.index');
Route::get('/categories',  [QuizController::class, 'categories'])->name('categories.index');
Route::get('/quiz/{slug}', [QuizController::class, 'show'])->name('quizzes.show');
Route::get('/certificate/{uuid}', [CertificateController::class, 'verify'])->name('certificate.verify');
Route::get('/pricing',     [PricingController::class, 'index'])->name('pricing');
Route::get('/for-creators', [ForCreatorsController::class, 'index'])->name('for-creators');

// ── Auth (guest) ──────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'create'])->name('login');
    Route::post('/login',   [LoginController::class, 'store'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.post');

    // Social login
    Route::get('/auth/google',          [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    // Password reset
    Route::get('/forgot-password',       [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password',      [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',       [NewPasswordController::class, 'store'])->name('password.store');
});

// ── Logout ────────────────────────────────────────────────────────────────────
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ── OTP email verification ────────────────────────────────────────────────────
Route::get('/email/verify-otp',    [OtpController::class, 'show'])->name('otp.verify');
Route::post('/email/verify-otp',   [OtpController::class, 'verify'])->middleware('throttle:10,1')->name('otp.verify.post');
Route::post('/email/resend-otp',   [OtpController::class, 'resend'])->middleware('throttle:3,5')->name('otp.resend');

// ── Email verification (signed link flow) ─────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/')->with('status', 'Your email has been verified!');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::middleware('otp.verified')->group(function () {

        // SSE endpoint for AI question generation (GET → CSRF-exempt; must be web.php for session auth)
        Route::get('/api/ai/generate', [AiGenerateController::class, 'stream'])->name('api.ai.generate');

        // Delete a single AI-generated question (creator only)
        Route::delete('/api/questions/{question}', [QuestionController::class, 'destroy'])->name('api.questions.delete');

        // Exam attempt
        Route::post('/quiz/{slug}/start',          [AttemptController::class, 'start'])->name('attempt.start');
        Route::get('/attempt/{attempt}',            [AttemptController::class, 'show'])->name('attempt.show');
        Route::post('/attempt/{attempt}/answer',    [AttemptController::class, 'saveAnswer'])->name('attempt.answer');
        Route::post('/attempt/{attempt}/submit',    [AttemptController::class, 'submit'])->name('attempt.submit');
        Route::get('/attempt/{attempt}/result',     [AttemptController::class, 'result'])->name('attempt.result');

        // Subscriptions (creator plan purchase)
        Route::get('/pricing/{slug}/checkout',      [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
        Route::post('/pricing/{slug}/pay',          [SubscriptionController::class, 'initiate'])->name('subscription.initiate');
        Route::post('/pricing/{slug}/callback',     [SubscriptionController::class, 'callback'])->name('subscription.callback');
        Route::get('/pricing/{slug}/stripe-success',[SubscriptionController::class, 'stripeSuccess'])->name('subscription.stripe.success');

        // Payments
        Route::get('/quiz/{slug}/checkout',          [OrderController::class, 'checkout'])->name('quiz.checkout');
        Route::post('/quiz/{slug}/pay/razorpay',     [OrderController::class, 'initiateRazorpay'])->name('payment.razorpay.initiate');
        Route::post('/payment/razorpay/callback',    [OrderController::class, 'callbackRazorpay'])->name('payment.razorpay.callback');
        Route::post('/quiz/{slug}/pay/stripe',       [OrderController::class, 'initiateStripe'])->name('payment.stripe.initiate');
        Route::get('/payment/stripe/success/{order}',[OrderController::class, 'stripeSuccess'])->name('payment.stripe.success');

        Route::get('/attempt/{attempt}/certificate/download', [CertificateController::class, 'download'])->name('certificate.download');

        Route::prefix('my')->name('my.')->group(function () {
            Route::get('/dashboard',    [ProfileController::class, 'dashboard'])->name('dashboard');
            Route::get('/quizzes',      [ProfileController::class, 'quizzes'])->name('quizzes');
            Route::get('/attempts',     [ProfileController::class, 'attempts'])->name('attempts');
            Route::get('/certificates', [ProfileController::class, 'certificates'])->name('certificates');
            Route::get('/favourites',   [ProfileController::class, 'favourites'])->name('favourites');
        });

        Route::get('/profile',                    [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile/update',            [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar',            [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::post('/profile/password',          [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/notifications',     [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
        Route::delete('/profile',                 [ProfileController::class, 'destroy'])->name('profile.destroy');

    }); // end: otp.verified group
});

// ── Admin: email template test send ──────────────────────────────────────────
Route::post('/admin/email-templates/{template}/send-test', [EmailTemplateController::class, 'sendTest'])
    ->middleware('auth')
    ->name('admin.email-templates.send-test');

// ── Creator panel AJAX (session-auth; NOT in api.php — Sanctum would break auth()->id()) ──
Route::middleware('auth')->prefix('creator/api')->name('creator.api.')->group(function () {
    Route::get('/bank-questions',              [QuestionBankController::class, 'index'])->name('bank-questions');
    Route::post('/bank-questions/random-import', [QuestionBankController::class, 'randomImport'])->name('bank-questions.random-import');
    Route::post('/bank-collections',           [QuestionCollectionController::class, 'store'])->name('bank-collections.store');
});

// ── Language switcher ─────────────────────────────────────────────────────────
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');
