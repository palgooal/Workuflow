<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'الخدمات';
    protected static ?string $modelLabel = 'خدمة';
    protected static ?string $pluralModelLabel = 'الخدمات';
    protected static ?string $navigationGroup = 'إدارة الأعمال';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الخدمة')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم الخدمة (إنجليزي)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name_ar')
                        ->label('اسم الخدمة (عربي)')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('الوصف')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\ColorPicker::make('color')
                        ->label('اللون')
                        ->default('#6366f1'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(''),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الخدمة')
                    ->description(fn ($record) => $record->name)
                    ->searchable(['name', 'name_ar'])
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('is_global')
                    ->label('النوع')
                    ->formatStateUsing(fn ($state) => $state ? 'افتراضية' : 'مخصصة')
                    ->color(fn ($state) => $state ? 'success' : 'primary'),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('المشاريع')
                    ->counts('projects')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_global')
                    ->label('النوع')
                    ->trueLabel('افتراضية')
                    ->falseLabel('مخصصة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->hidden(fn ($record) => $record->is_global),
            ])
            ->defaultSort('name_ar');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
