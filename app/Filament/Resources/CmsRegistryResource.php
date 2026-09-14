<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsRegistryResource\Pages;
use App\Models\CmsRegistry;
use App\Services\AI\AiConsoleService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CmsRegistryResource extends Resource
{
    protected static ?string $model = CmsRegistry::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('type')
                ->options([
                    'module' => 'Module',
                    'plugin' => 'Plugin',
                    'theme' => 'Theme',
                    'command' => 'AI command',
                ])
                ->required(),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Select::make('ai_provider')
                ->options(fn (): array => array_combine(
                    app(AiConsoleService::class)->providers(),
                    app(AiConsoleService::class)->providers(),
                ))
                ->searchable(),
            Textarea::make('structure')
                ->formatStateUsing(fn (?array $state): string => json_encode($state ?? [], JSON_PRETTY_PRINT))
                ->dehydrateStateUsing(fn (?string $state): array => json_decode($state ?: '{}', true, 512, JSON_THROW_ON_ERROR))
                ->json()
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('ai_provider')->label('Provider')->sortable(),
            TextColumn::make('version')->sortable(),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCmsRegistries::route('/'),
            'create' => Pages\CreateCmsRegistry::route('/create'),
            'edit' => Pages\EditCmsRegistry::route('/{record}/edit'),
        ];
    }
}