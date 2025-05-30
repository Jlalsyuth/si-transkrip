<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Sesuaikan 'nama_lengkap' dengan nama field di form Vue Anda
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:255', 'unique:'.User::class], // Tambahkan validasi NIM
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Tambahkan validasi untuk program_studi, sesuaikan dengan ENUM di migrasi Anda
            'program_studi' => ['nullable', Rule::in(['Sistem_Informasi','Teknologi_Informasi','Pendidikan_Teknologi_Informasi', 'Teknik_Informatika', 'Teknik_Komputer'])],
            // Tambahkan validasi untuk role. PENTING: Biasanya role mahasiswa di-set default.
            // Jangan biarkan pengguna memilih 'kps' atau 'admin' secara bebas saat registrasi.
            // 'role' => ['required', Rule::in(['mahasiswa', 'kps', 'admin'])], // Contoh jika ingin divalidasi
        ]);

        $user = User::create([
            // Sesuaikan 'nama_lengkap' dengan nama field di form Vue Anda
            'nama' => $request->nama_lengkap, // Ganti 'name' menjadi 'nama' atau 'nama_lengkap' sesuai model User Anda
            'nim' => $request->nim,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'program_studi' => $request->program_studi,
            // Atur role default ke 'mahasiswa' untuk keamanan.
            // Jika Anda membiarkan pengguna memilih, pastikan ada validasi ketat atau proses approval.
            'role' => 'mahasiswa', // $request->role jika Anda memvalidasi dan mengizinkan input
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
