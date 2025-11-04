<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;
use App\Models\Product;
use App\Models\Order;

class DashboardController extends Controller
{
    /**
     * Dashboard sayfası (admin panel ana ekranı).
     */
    public function index(Request $request)
    {
        // Giriş yapan admin
        $admin = Auth::guard('admin')->user();

        // Örnek istatistikler (ileride domain modellerine göre genişletilebilir)
        $stats = [
            'total_users'   => User::count(),
            'total_admins'  => Admin::count(),
            'total_products'=> Product::count(),
            'total_orders'  => Order::count(),
        ];

        // Son 5 kullanıcı
        $latestUsers = User::latest()->take(5)->get();

        // Son 5 sipariş
        $latestOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', [
            'admin'        => $admin,
            'stats'        => $stats,
            'latestUsers'  => $latestUsers,
            'latestOrders' => $latestOrders,
        ]);
    }
}
