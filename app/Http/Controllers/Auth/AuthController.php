<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'cf-turnstile-response' => 'nullable|string',
        ]);

        if (config('services.turnstile.enabled')) {
            $this->verifyTurnstile($request->input('cf-turnstile-response'));
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah'])->onlyInput('email');
        }

        $user = Auth::user();
        if ($user->is_banned) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun Anda telah diblokir: ' . ($user->ban_reason ?? '')]);
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('user.dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'referral_code' => 'nullable|string|exists:users,referral_code',
            'cf-turnstile-response' => 'nullable|string',
        ]);

        if (config('services.turnstile.enabled')) {
            $this->verifyTurnstile($request->input('cf-turnstile-response'));
        }

        $referrer = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => $referrer?->id,
        ]);

        Auth::login($user);
        return redirect()->route('user.dashboard');
    }

    public function showWhatsAppLogin()
    {
        return view('auth.whatsapp-login');
    }

    public function requestWhatsAppOtp(Request $request, WhatsAppService $whatsAppService)
    {
        $request->validate(['phone' => 'required|string|max:20']);

        $phone = $request->phone;
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak terdaftar']);
        }

        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("wa_otp:{$phone}", $otpCode, 300);

        $whatsAppService->sendOtp($phone, $otpCode);

        return back()->with('otp_sent', true)->with('phone', $phone);
    }

    public function verifyWhatsAppOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $cached = Cache::get("wa_otp:{$request->phone}");
        if (!$cached || $cached !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah expired']);
        }

        Cache::forget("wa_otp:{$request->phone}");
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor tidak terdaftar']);
        }

        Auth::login($user);
        $user->update(['last_login_at' => now()]);

        return redirect()->route('user.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function verifyTurnstile(?string $token): void
    {
        if (!$token) {
            abort(422, 'Captcha verification required');
        }

        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret'),
            'response' => $token,
        ]);

        if (!$response->json('success')) {
            abort(422, 'Captcha verification failed');
        }
    }
}
