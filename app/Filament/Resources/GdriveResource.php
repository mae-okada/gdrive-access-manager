<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GdriveResource\Pages;
use App\Models\Division;
use App\Models\Gdrive;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GdriveResource extends Resource
{
    protected static ?string $model = Gdrive::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('division_id')
                    ->options(Division::pluck('name', 'id'))
                    ->relationship('division', 'name')
                    ->required(),
                Textarea::make('unique_id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('division.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unique_id')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGdrives::route('/'),
            'create' => Pages\CreateGdrive::route('/create'),
            'edit' => Pages\EditGdrive::route('/{record}/edit'),
        ];
    }
}
