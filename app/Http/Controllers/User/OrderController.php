<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Operator;
use App\Models\OtpOrder;
use App\Models\OtpPricing;
use App\Models\OtpService;
use App\Services\OtpOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = OtpOrder::where('user_id', Auth::id())
            ->with(['service', 'country', 'operator', 'provider'])
            ->latest()
            ->paginate(20);

        return view('user.orders.index', compact('orders'));
    }

    public function create()
    {
        $countries = Country::active()->orderBy('sort_order')->orderBy('name')->get();
        $services = OtpService::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('user.orders.create', compact('countries', 'services'));
    }

    public function getOperators(Request $request)
    {
        $operators = Operator::active()
            ->when($request->country_id, fn($q) => $q->where('country_id', $request->country_id))
            ->get();

        return response()->json(['data' => $operators]);
    }

    public function getPricing(Request $request)
    {
        $request->validate([
            'country_id' => 'required|integer',
            'service_id' => 'required|integer',
        ]);

        $pricing = OtpPricing::active()
            ->where('country_id', $request->country_id)
            ->where('service_id', $request->service_id)
            ->when($request->operator_id, fn($q, $v) => $q->where('operator_id', $v))
            ->with(['provider', 'operator'])
            ->orderBy('sell_price')
            ->get();

        $user = Auth::user();
        $pricing->each(function ($item) use ($user) {
            $item->user_price = $user->getPriceForService($item);
        });

        return response()->json(['data' => $pricing]);
    }

    public function store(Request $request, OtpOrderService $orderService)
    {
        $request->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'service_id' => 'required|integer|exists:otp_services,id',
            'operator_id' => 'nullable|integer|exists:operators,id',
        ]);

        try {
            $order = $orderService->createOrder(
                Auth::user(),
                $request->country_id,
                $request->service_id,
                $request->operator_id
            );

            return redirect()->route('user.orders.show', $order)->with('success', 'Order berhasil dibuat');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(OtpOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        $order->load(['service', 'country', 'operator', 'provider']);
        return view('user.orders.show', compact('order'));
    }

    public function checkStatus(OtpOrder $order, OtpOrderService $orderService)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        $order = $orderService->checkOrderStatus($order);
        return response()->json([
            'status' => $order->status,
            'otp_code' => $order->otp_code,
            'phone_number' => $order->phone_number,
        ]);
    }

    public function cancel(OtpOrder $order, OtpOrderService $orderService)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        try {
            $orderService->cancelOrder($order);
            return back()->with('success', 'Order berhasil dibatalkan dan saldo dikembalikan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
