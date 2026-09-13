<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect ke halaman persetujuan Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    /**
     * Callback dari Google: cocokkan by email, buat jika belum ada.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/')->with('error', 'Login Google dibatalkan. Silakan coba lagi.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        $email = $googleUser->getEmail();
        $raw = $googleUser->getRaw() ?? [];
        $emailVerified = $raw['email_verified'] ?? $raw['verified_email'] ?? true;

        if (empty($email) || ! $emailVerified) {
            return redirect('/')->with('error', 'Email Google tidak terverifikasi. Gunakan akun Google yang valid.');
        }

        $googleId = $googleUser->getId();

        $user = User::where('google_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: explode('@', $email)[0],
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => null,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('history.index'));
    }

    /**
     * Logout web.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Anda telah keluar.');
    }
}
