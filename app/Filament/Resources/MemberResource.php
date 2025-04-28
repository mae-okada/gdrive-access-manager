<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\Services\DrivePermissionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('email'),
                Select::make('division')
                    ->options([
                        'HR' => 'HR',
                        'Sales' => 'Sales',
                        'Accounting' => 'Accounting',
                        'Design' => 'Design',
                        'Front End' => 'Front End',
                        'Back End' => 'Back End',
                        'Mobile' => 'Mobile',
                    ])
                    ->placeholder('Select a division'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('division')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('division')
                    ->options([
                        'HR' => 'HR',
                        'Sales' => 'Sales',
                        'Accounting' => 'Accounting',
                        'Design' => 'Design',
                        'Front End' => 'Front End',
                        'Back End' => 'Back End',
                        'Mobile' => 'Mobile',
                    ])
                    ->label('Filter by Division')
                    ->placeholder('Select a division'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('remove_drive_access')
                    ->icon('heroicon-o-user-minus')
                    ->color('info')
                    ->action(function (Member $member) {
                        try {
                            app(DrivePermissionService::class)->remove($member);

                            Notification::make()
                                ->title('Drive access removed successfully!')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to remove drive access!')
                                ->body($e->getMessage()) // optional, show reason
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
