<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Halaman tujuan setelah login, sesuai peran pengguna
    private function homeFor(User $user): string
    {
        return $user->isAdmin()
            ? route('admin.dashboard')
            : route('customer.orders.index');
    }

    // Login pelanggan (/login). Akun admin sengaja tidak bisa masuk lewat sini.
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        return $this->attempt($request, User::ROLE_CUSTOMER, route('customer.orders.index'));
    }

    // Login admin (/admin/login). Akun pelanggan tidak bisa masuk lewat sini.
    public function showAdminLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }
        return view('auth.admin-login');
    }

    public function authenticateAdmin(Request $request)
    {
        return $this->attempt($request, User::ROLE_ADMIN, route('admin.dashboard'));
    }

    // Peran ikut menjadi syarat pencarian akun, jadi akun dengan peran lain diperlakukan
    // sama seperti akun yang tidak ada (pesan yang sama, tidak membocorkan keberadaan admin).
    private function attempt(Request $request, string $role, string $default)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials + ['role' => $role])) {
            $request->session()->regenerate();
            return redirect()->intended($default);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }
        return view('auth.register');
    }

    // Pendaftaran akun pelanggan. Peran selalu 'customer', tidak pernah dari input.
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'phone.regex' => 'Nomor HP/WhatsApp tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $user = new User($data);
        $user->role = User::ROLE_CUSTOMER;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.orders.index')
            ->with('success', 'Akun berhasil dibuat. Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        $wasAdmin = Auth::user()?->isAdmin() ?? false;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $wasAdmin ? redirect()->route('admin.login') : redirect('/');
    }
}
