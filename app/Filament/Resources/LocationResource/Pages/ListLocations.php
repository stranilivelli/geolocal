<?php
// Pages/ListLocations.php
namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuova struttura'),
        ];
    }

    // Tab rapidi sopra la tabella
    public function getTabs(): array
    {
        return [
            'tutte' => Tab::make('Tutte'),
            'pubblicate' => Tab::make('Pubblicate')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('published', true))
                ->badge(fn () => $this->getModel()::where('published', true)->count()),
            'bozze' => Tab::make('Bozze')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('published', false))
                ->badge(fn () => $this->getModel()::where('published', false)->count()),
            'evidenza' => Tab::make('In evidenza')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('featured', true)),
        ];
    }
}
