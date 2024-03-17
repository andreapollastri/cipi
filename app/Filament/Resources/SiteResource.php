<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $username = 'cp'.Str::lower(Str::random(10));
        $password = Str::random(16);

        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('username')
                    ->default($username),
                Forms\Components\Hidden::make('password')
                    ->default($password),
                Forms\Components\Select::make('basepath')
                    ->required()
                    ->prefix('/home/'.$username.'/www')
                    ->options([
                        '/' => '/',
                        '/web' => '/web',
                        '/build' => '/build',
                        '/public' => '/public',
                    ])
                    ->default('/public')
                    ->native(false),
                Forms\Components\TextInput::make('repository')
                    ->maxLength(255),
                Forms\Components\TextInput::make('branch')
                    ->maxLength(255),
                Forms\Components\Select::make('php')
                    ->required()
                    ->options(\App\Helpers\Php::availableVersions())
                    ->default(\App\Helpers\Php::defaultVersion())
                    ->native(false),
                Forms\Components\TextInput::make('supervisor'),
                Forms\Components\TextInput::make('nginx'),
                Forms\Components\TextInput::make('deploy'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('basepath')
                    ->searchable(),
                Tables\Columns\TextColumn::make('repository')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch')
                    ->searchable(),
                Tables\Columns\TextColumn::make('php')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
