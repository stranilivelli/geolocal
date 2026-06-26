<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categorie';
    protected static ?string $modelLabel = 'Categoria';
    protected static ?string $pluralModelLabel = 'Categorie';
    protected static ?string $navigationGroup = 'Gestione';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome categoria')
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                    $set('slug', Str::slug($state))
                ),

            Forms\Components\TextInput::make('slug')
                ->label('Slug URL')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(100),

            Forms\Components\Toggle::make('published')
                ->label('Pubblicata')
                ->helperText('Visibile nei filtri di ricerca')
                ->onColor('success')
                ->default(true),

            Forms\Components\TextInput::make('ordering')
                ->label('Ordinamento')
                ->numeric()
                ->default(0)
                ->helperText('Valore più basso = prima nella lista'),
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
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('locations_count')
                    ->label('Strutture')
                    ->counts('locations')
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('published')
                    ->label('Pub.')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('ordering')
                    ->label('Ordine')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('published')
                    ->label('Stato pubblicazione'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ordering')
            ->reorderable('ordering');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
