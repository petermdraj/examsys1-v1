<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\DeleteAccountRequest;
use App\Http\Requests\Customer\UpdateNotificationsRequest;
use App\Http\Requests\Customer\UpdatePasswordRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user   = auth()->user();
        $orders = $user->orders()->with('quiz')->where('status', 'paid')->latest()->take(10)->get();

        return view('customer.my.profile', compact('user', 'orders'));
    }

    public function update(UpdateProfileRequest $request)
    {
        auth()->user()->update($request->validated());

        return back()->with('success', __('common.profile_updated'));
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);

        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', __('common.avatar_updated'));
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => __('common.password_incorrect')])->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', __('common.password_updated'));
    }

    public function updateNotifications(UpdateNotificationsRequest $request)
    {
        auth()->user()->update([
            'notification_preferences' => $request->validated(),
        ]);

        return back()->with('success', __('common.notifications_saved'));
    }

    public function destroy(DeleteAccountRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.'])->withInput();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect('/')->with('success', __('common.account_deleted'));
    }

    public function dashboard()
    {
        $user = auth()->user();

        $enrolledCount    = $user->enrollments()->count();
        $certsCount       = $user->certificates()->count();
        $completedAttempts = $user->attempts()->where('status', 'completed');
        $totalCompleted   = $completedAttempts->count();
        $passedCount      = (clone $completedAttempts)->where('is_passed', true)->count();
        $passRate         = $totalCompleted > 0 ? round(($passedCount / $totalCompleted) * 100) : null;

        $attempts = $user->attempts()->with('quiz')->latest()->take(5)->get();

        return view('customer.my.dashboard', compact('user', 'attempts', 'enrolledCount', 'certsCount', 'passRate'));
    }

    public function quizzes()
    {
        $enrollments = auth()->user()
            ->enrollments()
            ->with(['quiz.category', 'quiz' => function ($q) {
                $q->withCount('questions');
            }])
            ->latest('enrolled_at')
            ->paginate(12);

        // Attach latest attempt status per quiz for the CTA
        $quizIds       = $enrollments->pluck('quiz_id');
        $latestAttempts = auth()->user()->attempts()
            ->whereIn('quiz_id', $quizIds)
            ->orderBy('started_at')
            ->get()
            ->keyBy('quiz_id');

        return view('customer.my.quizzes', compact('enrollments', 'latestAttempts'));
    }

    public function attempts()
    {
        $status   = request('status', 'all');
        $query    = auth()->user()->attempts()->with(['quiz.category'])->latest();

        if ($status === 'completed')   { $query->where('status', 'completed'); }
        elseif ($status === 'active')  { $query->whereIn('status', ['in_progress']); }
        elseif ($status === 'missed')  { $query->whereIn('status', ['abandoned', 'timed_out']); }

        $attempts = $query->paginate(15)->withQueryString();

        return view('customer.my.attempts', compact('attempts', 'status'));
    }

    public function certificates()
    {
        $certificates = auth()->user()
            ->certificates()
            ->with(['quiz', 'attempt'])
            ->latest('issued_at')
            ->paginate(10);

        return view('customer.my.certificates', compact('certificates'));
    }

    public function favourites()
    {
        $favourites = auth()->user()
            ->favourites()
            ->with(['quiz.category', 'quiz.creator'])
            ->latest('created_at')
            ->paginate(12);

        return view('customer.my.favourites', compact('favourites'));
    }
}
