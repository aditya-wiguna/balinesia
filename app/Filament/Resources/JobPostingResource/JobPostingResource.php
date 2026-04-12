<?php

namespace App\Filament\Resources\JobPostingResource;

use App\Models\JobPosting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobPostingResource extends Resource
{
    protected static ?string $model = JobPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Jobs';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Job Details')->schema([
                Forms\Components\TextInput::make('job_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('external_id')
                    ->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Employment')->schema([
                Forms\Components\Select::make('employment_type')
                    ->options([
                        'Full Time' => 'Full Time',
                        'Part Time' => 'Part Time',
                        'Contract' => 'Contract',
                        'Internship' => 'Internship',
                        'Freelance' => 'Freelance',
                        'Daily Worker' => 'Daily Worker',
                    ]),
                Forms\Components\TextInput::make('category')
                    ->maxLength(255),
                Forms\Components\TextInput::make('salary_range')
                    ->maxLength(255)
                    ->placeholder('e.g., Rp 5.000.000 - Rp 8.000.000'),
            ])->columns(2),

            Forms\Components\Section::make('Description')->schema([
                Forms\Components\Textarea::make('description')
                    ->rows(4),
                Forms\Components\Textarea::make('requirements')
                    ->rows(4),
            ]),

            Forms\Components\Section::make('Source')->schema([
                Forms\Components\TextInput::make('source_name')
                    ->default('LokerBali')
                    ->maxLength(255),
                Forms\Components\TextInput::make('source_url')
                    ->label('Source URL')
                    ->url()
                    ->required(),
                Forms\Components\Toggle::make('is_remote')
                    ->label('Remote Available'),
            ])->columns(2),

            Forms\Components\Section::make('Dates')->schema([
                Forms\Components\DateTimePicker::make('posted_date'),
                Forms\Components\DateTimePicker::make('expires_date'),
            ])->columns(2),

            Forms\Components\Section::make('Settings')->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Toggle::make('is_approved')
                    ->label('Approved')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_title')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_type')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_remote')
                    ->boolean()
                    ->label('Remote'),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posted_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employment_type'),
                Tables\Filters\SelectFilter::make('location'),
                Tables\Filters\TernaryFilter::make('is_approved'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
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
            'index' => Pages\ListJobPostings::route('/'),
            'create' => Pages\CreateJobPosting::route('/create'),
            'edit' => Pages\EditJobPosting::route('/{record}/edit'),
        ];
    }
}
