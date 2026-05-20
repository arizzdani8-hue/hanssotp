<?php
namespace App\Filament\Resources;
use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Promotions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('type')->options(['fixed' => 'Fixed', 'percent' => 'Percent'])->required(),
            Forms\Components\TextInput::make('value')->numeric()->required(),
            Forms\Components\TextInput::make('min_deposit')->numeric()->default(0),
            Forms\Components\TextInput::make('max_discount')->numeric(),
            Forms\Components\TextInput::make('max_uses')->numeric()->default(0)->helperText('0 = unlimited'),
            Forms\Components\TextInput::make('max_uses_per_user')->numeric()->default(1),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('expires_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('value'),
            Tables\Columns\TextColumn::make('used_count'),
            Tables\Columns\TextColumn::make('max_uses'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('expires_at')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}