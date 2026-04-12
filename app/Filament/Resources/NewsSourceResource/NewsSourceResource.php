<?php

namespace App\Filament\Resources\NewsSourceResource;

use App\Models\NewsSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsSourceResource extends Resource
{
    protected static ?string $model = NewsSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-rss';

    protected static ?string $navigationGroup = 'News Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Source Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('api_endpoint')
                    ->label('API Endpoint')
                    ->url()
                    ->maxLength(255)
                    ->hint('Leave empty to use default WordPress REST API'),
            ])->columns(2),

            Forms\Components\Section::make('Appearance')->schema([
                Forms\Components\TextInput::make('logo_url')
                    ->label('Logo URL')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Select::make('language')
                    ->options([
                        'id' => 'Indonesian',
                        'en' => 'English',
                    ])
                    ->default('id'),
            ])->columns(2),

            Forms\Components\Section::make('Settings')->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\KeyValue::make('config')
                    ->label('Additional Config'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('url')->limit(40),
                Tables\Columns\TextColumn::make('language'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('articles_count')
                    ->label('Articles')
                    ->counts('articles'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsSources::route('/'),
            'create' => Pages\CreateNewsSource::route('/create'),
            'edit' => Pages\EditNewsSource::route('/{record}/edit'),
        ];
    }
}
