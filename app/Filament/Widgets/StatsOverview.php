<?php

namespace App\Filament\Widgets;

use App\Models\Deposit;
use App\Models\OtpOrder;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        return [
            Stat::make('Total Users', User::count())
                ->description(User::where('created_at', '>=', $today)->count() . ' hari ini')
                ->icon('heroicon-o-users'),
            Stat::make('Total Orders', OtpOrder::count())
                ->description(OtpOrder::where('created_at', '>=', $today)->count() . ' hari ini')
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Orders Sukses', OtpOrder::where('status', 'received')->count())
                ->description('Rp ' . number_format(OtpOrder::where('status', 'received')->sum('profit'), 0, ',', '.') . ' profit')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Total Deposit', 'Rp ' . number_format(Deposit::where('status', 'paid')->sum('amount'), 0, ',', '.'))
                ->description(Deposit::where('status', 'paid')->where('created_at', '>=', $today)->count() . ' hari ini')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
