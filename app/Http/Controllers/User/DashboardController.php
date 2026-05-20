<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\OtpOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recentOrders = OtpOrder::where('user_id', $user->id)
            ->with(['service', 'country'])
            ->latest()
            ->limit(10)
            ->get();

        $recentDeposits = Deposit::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'balance' => $user->balance,
            'total_orders' => OtpOrder::where('user_id', $user->id)->count(),
            'successful_orders' => OtpOrder::where('user_id', $user->id)->where('status', 'received')->count(),
            'total_deposits' => Deposit::where('user_id', $user->id)->where('status', 'paid')->sum('amount'),
        ];

        return view('user.dashboard', compact('user', 'recentOrders', 'recentDeposits', 'stats'));
    }
}
