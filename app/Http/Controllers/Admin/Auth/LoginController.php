<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Login formunu göster.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Admin login işlemi.
     */
    public function login(Request $request)
    {
        // Basit rate limit anahtarı: IP + email
        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        // Doğrulama
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        // Rate limit kontrolü
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Çok fazla deneme yapıldı. Lütfen {$seconds} saniye sonra tekrar deneyin.",
            ]);
        }

        // Giriş denemesi
        $credentials = [
            'email'    => $validated['email'],
            'password' => $validated['password'],
            // İsteğe bağlı: sadece aktif adminler giriş yapsın
            'is_active' => true,
        ];

        $remember = (bool) ($validated['remember'] ?? false);

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            // İsteğe bağlı: son giriş zamanı güncellemesi (model üzerinden)
            // $admin = Auth::guard('admin')->user();
            // $admin->last_login_at = now();
            // $admin->save();

            return redirect()->intended(route('admin.dashboard'));
        }

        // Başarısız giriş denemesi
        RateLimiter::hit($throttleKey, 60);
        throw ValidationException::withMessages([
            'email' => 'Girdiğiniz bilgilere uygun bir admin bulunamadı veya hesap pasif.',
        ]);
    }
}
