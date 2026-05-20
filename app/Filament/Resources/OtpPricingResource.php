<?php
namespace App\Filament\Resources;
use App\Filament\Resources\OtpPricingResource\Pages;
use App\Models\OtpPricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OtpPricingResource extends Resource
{
    protected static ?string $model = OtpPricing::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'OTP Management';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('country_id')->relationship('country', 'name')->required()->searchable(),
            Forms\Components\Select::make('service_id')->relationship('service', 'name')->required()->searchable(),
            Forms\Components\Select::make('operator_id')->relationship('operator', 'name')->searchable(),
            Forms\Components\Select::make('provider_id')->relationship('provider', 'name')->required()->searchable(),
            Forms\Components\TextInput::make('cost_price')->numeric()->required(),
            Forms\Components\TextInput::make('sell_price')->numeric()->required(),
            Forms\Components\TextInput::make('sell_price_gold')->numeric(),
            Forms\Components\TextInput::make('sell_price_platinum')->numeric(),
            Forms\Components\TextInput::make('markup_percent')->numeric()->default(0),
            Forms\Components\TextInput::make('stock')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('provider_service_code'),
            Forms\Components\TextInput::make('provider_country_code'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('country.name')->sortable(),
            Tables\Columns\TextColumn::make('service.name')->sortable(),
            Tables\Columns\TextColumn::make('operator.name'),
            Tables\Columns\TextColumn::make('provider.name'),
            Tables\Columns\TextColumn::make('cost_price')->money('IDR'),
            Tables\Columns\TextColumn::make('sell_price')->money('IDR'),
            Tables\Columns\TextColumn::make('stock'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('country_id')->relationship('country', 'name'),
            Tables\Filters\SelectFilter::make('service_id')->relationship('service', 'name'),
            Tables\Filters\SelectFilter::make('provider_id')->relationship('provider', 'name'),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOtpPricings::route('/'),
            'create' => Pages\CreateOtpPricing::route('/create'),
            'edit' => Pages\EditOtpPricing::route('/{record}/edit'),
        ];
    }
}