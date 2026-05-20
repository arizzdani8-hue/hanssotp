<?php

namespace App\Jobs;

use App\Models\OtpOrder;
use App\Services\OtpOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoCancelExpiredOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(OtpOrderService $orderService): void
    {
        $expiredOrders = OtpOrder::active()
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredOrders as $order) {
            try {
                $orderService->cancelOrder($order);
            } catch (\Exception $e) {
                $order->update(['status' => 'expired']);
                $orderService->refundOrder($order);
            }
        }
    }
}
