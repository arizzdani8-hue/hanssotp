<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone'),
            Forms\Components\TextInput::make('password')->password()->dehydrated(fn($state) => filled($state)),
            Forms\Components\TextInput::make('balance')->numeric()->default(0),
            Forms\Components\Select::make('role')->options(['user' => 'User', 'reseller' => 'Reseller', 'admin' => 'Admin']),
            Forms\Components\Select::make('tier')->options(['basic' => 'Basic', 'gold' => 'Gold', 'platinum' => 'Platinum']),
            Forms\Components\Toggle::make('is_banned'),
            Forms\Components\TextInput::make('ban_reason'),
            Forms\Components\TextInput::make('max_active_orders')->numeric()->default(5),
            Forms\Components\TextInput::make('max_orders_per_minute')->numeric()->default(3),
            Forms\Components\TextInput::make('api_key')->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('referral_code')->disabled()->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('balance')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('tier')->badge(),
                Tables\Columns\IconColumn::make('is_banned')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->options(['user' => 'User', 'reseller' => 'Reseller']),
                Tables\Filters\SelectFilter::make('tier')->options(['basic' => 'Basic', 'gold' => 'Gold', 'platinum' => 'Platinum']),
                Tables\Filters\TernaryFilter::make('is_banned'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
