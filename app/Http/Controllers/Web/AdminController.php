<?php

namespace App\Http\Controllers\Web;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin) {
            return redirect()->back()->with('error', 'Username tidak ditemukan. Silahkan coba lagi.');
        }

        if (!Hash::check($request->password, $admin->password)) {
            return redirect()->back()->with('error', 'Password yang Anda masukkan salah.');
        }

        Auth::guard('web')->login($admin);

        return redirect()->route('dashboard')->with('success', 'Login berhasil! Selamat datang.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }
}
