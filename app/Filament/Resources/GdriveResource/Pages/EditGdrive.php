<?php

namespace App\Filament\Resources\GdriveResource\Pages;

use App\Filament\Resources\GdriveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGdrive extends EditRecord
{
    protected static string $resource = GdriveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
