<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\OtpOrder;
use App\Models\OtpPricing;
use App\Models\OtpService;
use App\Models\User;
use App\Services\OtpOrderService;
use Illuminate\Http\Request;

class ResellerController extends Controller
{
    public function balance(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => ['balance' => (float) $request->user()->balance],
        ]);
    }

    public function services(Request $request)
    {
        $services = OtpService::active()
            ->when($request->country_id, function ($q) use ($request) {
                $q->whereHas('pricing', fn($p) => $p->where('country_id', $request->country_id)->active());
            })
            ->get(['id', 'name', 'slug', 'icon']);

        return response()->json(['success' => true, 'data' => $services]);
    }

    public function countries()
    {
        $countries = Country::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'phone_code', 'flag_emoji']);

        return response()->json(['success' => true, 'data' => $countries]);
    }

    public function pricing(Request $request)
    {
        $request->validate([
            'country_id' => 'required|integer',
            'service_id' => 'required|integer',
        ]);

        $pricing = OtpPricing::active()
            ->where('country_id', $request->country_id)
            ->where('service_id', $request->service_id)
            ->with(['provider:id,name', 'operator:id,name'])
            ->get();

        $user = $request->user();
        $data = $pricing->map(fn($p) => [
            'id' => $p->id,
            'price' => $user->getPriceForService($p),
            'provider' => $p->provider?->name,
            'operator' => $p->operator?->name,
            'stock' => $p->stock,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function order(Request $request, OtpOrderService $orderService)
    {
        $request->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'service_id' => 'required|integer|exists:otp_services,id',
            'operator_id' => 'nullable|integer|exists:operators,id',
        ]);

        try {
            $order = $orderService->createOrder(
                $request->user(),
                $request->country_id,
                $request->service_id,
                $request->operator_id
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->order_id,
                    'phone_number' => $order->phone_number,
                    'status' => $order->status,
                    'price' => (float) $order->price,
                    'expires_at' => $order->expires_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function checkOrder(Request $request, string $orderId, OtpOrderService $orderService)
    {
        $order = OtpOrder::where('order_id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $order = $orderService->checkOrderStatus($order);

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->order_id,
                'phone_number' => $order->phone_number,
                'status' => $order->status,
                'otp_code' => $order->otp_code,
                'price' => (float) $order->price,
                'expires_at' => $order->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function cancelOrder(Request $request, string $orderId, OtpOrderService $orderService)
    {
        $order = OtpOrder::where('order_id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        try {
            $orderService->cancelOrder($order);
            return response()->json(['success' => true, 'message' => 'Order dibatalkan, saldo dikembalikan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
