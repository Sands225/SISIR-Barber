<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarberResource\Pages;
use App\Models\Barber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BarberResource extends Resource
{
    protected static ?string $model = Barber::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Kapster';
    protected static ?string $modelLabel = 'Kapster';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('nickname')->maxLength(100),
            Forms\Components\Textarea::make('bio')->rows(3),
            Forms\Components\TextInput::make('capacity_per_slot')
                ->numeric()->default(1)->minValue(1)->maxValue(5),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\FileUpload::make('photo_path')
                ->image()->directory('barbers'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')->label('Foto')->circular(),
                Tables\Columns\TextColumn::make('user.name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('nickname')->label('Nickname'),
                Tables\Columns\TextColumn::make('capacity_per_slot')->label('Kapasitas/Slot'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBarbers::route('/'),
            'create' => Pages\CreateBarber::route('/create'),
            'edit'   => Pages\EditBarber::route('/{record}/edit'),
        ];
    }
}
