<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Category;
use App\Models\Location;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Strutture';
    protected static ?string $modelLabel = 'Struttura';
    protected static ?string $pluralModelLabel = 'Strutture convenzionate';
    protected static ?string $navigationGroup = 'Gestione';
    protected static ?int $navigationSort = 1;

    // Badge con conteggio strutture nel nav
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::published()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Tabs::make('Struttura')->tabs([

                // ── TAB 1: Dati principali ────────────────────────────
                Tabs\Tab::make('Dati principali')->schema([

                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome struttura')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                $set('slug', \Illuminate\Support\Str::slug($state))
                            )
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),
                    ]),

                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(30),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('website')
                            ->label('Sito web')
                            ->url()
                            ->maxLength(200),
                    ]),

                    Section::make('Specializzazioni')->schema([
                        Forms\Components\CheckboxList::make('categories')
                            ->label('')
                            ->relationship('categories', 'name')
                            ->columns(4)
                            ->gridDirection('row'),
                    ]),

                    Section::make('Descrizione')->schema([
                        Forms\Components\RichEditor::make('intro')
                            ->label('Testo breve (visibile in lista)')
                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList'])
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('hours_prices')
                            ->label('Orari e informazioni tariffarie')
                            ->toolbarButtons(['bold', 'italic', 'bulletList'])
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Note interne (non visibili al pubblico)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                ]),

                // ── TAB 2: Indirizzo e mappa ──────────────────────────
                Tabs\Tab::make('Indirizzo e mappa')->schema([

                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Via / Indirizzo')
                            ->maxLength(200),

                        Forms\Components\TextInput::make('address2')
                            ->label('Indirizzo 2 (es. località, c/o)')
                            ->maxLength(200),
                    ]),

                    Grid::make(4)->schema([
                        Forms\Components\TextInput::make('city')
                            ->label('Città')
                            ->maxLength(200)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('province')
                            ->label('Provincia (sigla)')
                            ->maxLength(5)
                            ->placeholder('FI')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('CAP')
                            ->maxLength(10),
                    ]),

                    Section::make('Posizione sulla mappa')
                        ->description('Clicca sulla mappa per posizionare il marker, oppure digita l\'indirizzo nel campo sopra per geocodificarlo automaticamente.')
                        ->schema([
                            Map::make('location')
                                ->label('')
                                ->defaultLocation([43.77, 11.25])
                                ->defaultZoom(8)
                                ->draggable()
                                ->clickable()
                                ->autocomplete('address')
                                ->autocompleteReverse()
                                ->height('420px')
                                ->columnSpanFull(),

                            Forms\Components\Select::make('zoom')
                                ->label('Zoom iniziale mappa (pagina pubblica)')
                                ->options(array_combine(range(8, 20), range(8, 20)))
                                ->default(13)
                                ->helperText('Controlla il livello di zoom quando si apre il dettaglio struttura'),
                        ]),
                ]),

                // ── TAB 3: Pubblicazione ──────────────────────────────
                Tabs\Tab::make('Pubblicazione')->schema([

                    Grid::make(2)->schema([
                        Forms\Components\Toggle::make('published')
                            ->label('Pubblicata')
                            ->helperText('Visibile sul sito pubblico')
                            ->onColor('success')
                            ->default(false),

                        Forms\Components\Toggle::make('featured')
                            ->label('In evidenza')
                            ->helperText('Mostrata in cima alla lista')
                            ->onColor('warning')
                            ->default(false),
                    ]),

                    Forms\Components\Select::make('convention_type')
                        ->label('Tipo convenzione')
                        ->options([
                            'diretta'   => 'Diretta — i soci non anticipano',
                            'indiretta' => 'Indiretta — rimborso a posteriori',
                        ])
                        ->default('diretta')
                        ->required(),
                ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Location $r) => "{$r->address}, {$r->city}"),

                Tables\Columns\TextColumn::make('city')
                    ->label('Città')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('province')
                    ->label('Prov.')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Specializzazioni')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limit(3)
                    ->limitList(3),

                Tables\Columns\TextColumn::make('convention_type')
                    ->label('Convenzione')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'diretta'   => 'success',
                        'indiretta' => 'warning',
                    }),

                Tables\Columns\IconColumn::make('published')
                    ->label('Pub.')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('hits')
                    ->label('Visite')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornata')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('province')
                    ->label('Provincia')
                    ->options(
                        Location::distinct()->pluck('province', 'province')
                            ->filter()->sort()->toArray()
                    ),

                Tables\Filters\SelectFilter::make('categories')
                    ->label('Specializzazione')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('convention_type')
                    ->label('Convenzione')
                    ->options([
                        'diretta'   => 'Diretta',
                        'indiretta' => 'Indiretta',
                    ]),

                Tables\Filters\TernaryFilter::make('published')
                    ->label('Stato pubblicazione'),

                Tables\Filters\TernaryFilter::make('featured')
                    ->label('In evidenza'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('togglePublish')
                        ->label(fn (Location $r) => $r->published ? 'Nascondi' : 'Pubblica')
                        ->icon(fn (Location $r) => $r->published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn (Location $r) => $r->published ? 'warning' : 'success')
                        ->action(fn (Location $r) => $r->update(['published' => !$r->published])),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publishSelected')
                        ->label('Pubblica selezionate')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['published' => true])),

                    Tables\Actions\BulkAction::make('unpublishSelected')
                        ->label('Nascondi selezionate')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['published' => false])),

                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit'   => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
