<?php

namespace App\Filament\Resources\GdriveResource\Pages;

use App\Filament\Resources\GdriveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGdrives extends ListRecords
{
    protected static string $resource = GdriveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
