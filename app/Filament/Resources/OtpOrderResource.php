<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OtpOrderResource\Pages;
use App\Models\OtpOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class OtpOrderResource extends Resource
{
    protected static ?string $model = OtpOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'OTP Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_id')->disabled(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled(),
            Forms\Components\Select::make('country_id')->relationship('country', 'name'),
            Forms\Components\Select::make('service_id')->relationship('service', 'name'),
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name'),
            Forms\Components\TextInput::make('phone_number'),
            Forms\Components\TextInput::make('otp_code'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending', 'waiting' => 'Waiting', 'received' => 'Received',
                'cancelled' => 'Cancelled', 'expired' => 'Expired', 'refunded' => 'Refunded',
            ]),
            Forms\Components\TextInput::make('price')->numeric(),
            Forms\Components\TextInput::make('cost')->numeric(),
            Forms\Components\TextInput::make('profit')->numeric(),
            Forms\Components\Toggle::make('is_refunded'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('service.name'),
                Tables\Columns\TextColumn::make('country.name'),
                Tables\Columns\TextColumn::make('phone_number'),
                Tables\Columns\TextColumn::make('otp_code')->copyable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state) => match ($state) {
                    'received' => 'success', 'waiting', 'pending' => 'warning',
                    'cancelled', 'expired' => 'danger', 'refunded' => 'info', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('price')->money('IDR'),
                Tables\Columns\TextColumn::make('profit')->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'waiting' => 'Waiting', 'received' => 'Received',
                    'cancelled' => 'Cancelled', 'expired' => 'Expired', 'refunded' => 'Refunded',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('refund')
                    ->action(function (OtpOrder $record) {
                        app(\App\Services\OtpOrderService::class)->refundOrder($record);
                    })
                    ->requiresConfirmation()
                    ->visible(fn(OtpOrder $record) => !$record->is_refunded && $record->status !== 'received'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export Excel')
                    ->action(fn() => Excel::download(new \App\Exports\OrdersExport, 'orders.xlsx'))
                    ->icon('heroicon-o-arrow-down-tray'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOtpOrders::route('/'),
            'create' => Pages\CreateOtpOrder::route('/create'),
            'edit' => Pages\EditOtpOrder::route('/{record}/edit'),
        ];
    }
}
