<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Models\Deposit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice_id')->disabled(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled(),
            Forms\Components\TextInput::make('gateway')->disabled(),
            Forms\Components\TextInput::make('amount')->numeric()->disabled(),
            Forms\Components\TextInput::make('fee')->numeric()->disabled(),
            Forms\Components\TextInput::make('total')->numeric()->disabled(),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending', 'paid' => 'Paid', 'expired' => 'Expired', 'failed' => 'Failed',
            ]),
            Forms\Components\TextInput::make('gateway_reference')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_id')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('gateway')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('IDR'),
                Tables\Columns\TextColumn::make('total')->money('IDR'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state) => match ($state) {
                    'paid' => 'success', 'pending' => 'warning', default => 'danger',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'paid' => 'Paid', 'expired' => 'Expired', 'failed' => 'Failed',
                ]),
                Tables\Filters\SelectFilter::make('gateway')->options([
                    'dompetx' => 'DOMPETX', 'pakasir' => 'Pakasir',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
