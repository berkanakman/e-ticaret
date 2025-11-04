<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Admin logout işlemi.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        // Session güvenlik temizliği
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Oturum kapatıldı.');
    }
}
