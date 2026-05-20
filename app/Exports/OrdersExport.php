<?php

namespace App\Exports;

use App\Models\OtpOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return OtpOrder::with(['user', 'service', 'country', 'provider'])->latest();
    }

    public function headings(): array
    {
        return ['Order ID', 'User', 'Service', 'Country', 'Provider', 'Phone', 'OTP', 'Status', 'Price', 'Cost', 'Profit', 'Created At'];
    }

    public function map($order): array
    {
        return [
            $order->order_id,
            $order->user?->name,
            $order->service?->name,
            $order->country?->name,
            $order->provider?->name,
            $order->phone_number,
            $order->otp_code,
            $order->status,
            $order->price,
            $order->cost,
            $order->profit,
            $order->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
