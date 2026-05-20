<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OtpProviderResource\Pages;
use App\Models\OtpProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OtpProviderResource extends Resource
{
    protected static ?string $model = OtpProvider::class;
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'OTP Management';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('api_base_url')->url(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('priority')->numeric()->default(0),
            Forms\Components\KeyValue::make('config')
                ->keyLabel('Key')
                ->valueLabel('Value')
                ->addActionLabel('Add Config')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('priority')->sortable(),
                Tables\Columns\TextColumn::make('orders_count')->counts('orders')->label('Orders'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOtpProviders::route('/'),
            'create' => Pages\CreateOtpProvider::route('/create'),
            'edit' => Pages\EditOtpProvider::route('/{record}/edit'),
        ];
    }
}
