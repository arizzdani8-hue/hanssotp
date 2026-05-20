<?php

namespace App\Jobs;

use App\Models\OtpOrder;
use App\Services\OtpOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollOtpOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(OtpOrderService $orderService): void
    {
        $orders = OtpOrder::active()
            ->where('provider_order_id', '!=', null)
            ->get();

        foreach ($orders as $order) {
            try {
                $orderService->checkOrderStatus($order);
            } catch (\Exception $e) {
                // Continue to next order
            }
        }
    }
}
