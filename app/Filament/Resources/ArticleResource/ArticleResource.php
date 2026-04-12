<?php

namespace App\Filament\Resources\ArticleResource;

use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'News Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Article Content')->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(500),
                    Forms\Components\Textarea::make('excerpt')
                        ->rows(3)
                        ->maxLength(1000),
                    Forms\Components\RichEditor::make('content')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\Select::make('news_source_id')
                        ->relationship('newsSource', 'name')
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable(),
                    Forms\Components\TextInput::make('author')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('external_id')
                        ->maxLength(255),
                ])->columns(2),
            ])->columnSpan(2),

            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Media')->schema([
                    Forms\Components\TextInput::make('image_url')
                        ->label('Image URL')
                        ->url(),
                    Forms\Components\TextInput::make('source_url')
                        ->label('Source URL')
                        ->url()
                        ->required(),
                ]),

                Forms\Components\Section::make('Settings')->schema([
                    Forms\Components\Select::make('language')
                        ->options([
                            'id' => 'Indonesian',
                            'en' => 'English',
                        ])
                        ->default('id'),
                    Forms\Components\DateTimePicker::make('published_at'),
                    Forms\Components\Toggle::make('is_translated')
                        ->label('Translated')
                        ->disabled(),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured'),
                    Forms\Components\Toggle::make('is_approved')
                        ->label('Approved'),
                ])->columns(2),
            ])->columnSpan(1),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),
                Tables\Columns\TextColumn::make('newsSource.name')
                    ->label('Source')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_translated')
                    ->label('Translated')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('news_source_id')
                    ->label('Source')
                    ->relationship('newsSource', 'name'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_translated')
                    ->label('Translated'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Approved'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
