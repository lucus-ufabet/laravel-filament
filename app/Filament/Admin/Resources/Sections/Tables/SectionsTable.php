<?php

namespace App\Filament\Admin\Resources\Sections\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')->label('Website')->sortable()->searchable(),
                TextColumn::make('language')->label('Lang')->sortable(),
                TextColumn::make('type')->sortable(),
                TextColumn::make('slug')->copyable()->searchable(),
                TextColumn::make('order')->sortable(),
                IconColumn::make('is_published')->boolean(),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
