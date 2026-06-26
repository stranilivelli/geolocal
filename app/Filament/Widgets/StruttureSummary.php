<?php

namespace App\Filament\Widgets;

use App\Models\Location;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StruttureSummary extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $total      = Location::withTrashed()->count();
        $published  = Location::published()->count();
        $drafts     = Location::where('published', false)->whereNull('deleted_at')->count();
        $noCoords   = Location::published()->where(fn ($q) => $q->whereNull('lat')->orWhereNull('lng'))->count();

        return [
            Stat::make('Totale strutture', $total)
                ->description('Incluse le bozze')
                ->icon('heroicon-o-building-office-2')
                ->color('gray'),

            Stat::make('Pubblicate', $published)
                ->description('Visibili sul sito')
                ->icon('heroicon-o-eye')
                ->color('success'),

            Stat::make('Bozze', $drafts)
                ->description('Non ancora pubblicate')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),

            Stat::make('Senza coordinate', $noCoords)
                ->description('Non compaiono sulla mappa')
                ->icon('heroicon-o-map-pin')
                ->color($noCoords > 0 ? 'danger' : 'success'),
        ];
    }
}
