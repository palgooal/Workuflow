<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * ClientResource — للأدمن فقط (قراءة فقط)
 * يعرض عملاء جميع المستخدمين بدون Global Scope
 * لا يُستخدم للتعديل — CRM الحقيقي في /clients
 */
class ClientResource extends Resource
{
    protected static ?string $model           = Client::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'العملاء (مراقبة)';
    protected static ?string $navigationGroup = 'البيانات المالية';
    protected static ?string $modelLabel      = 'عميل';
    protected static ?string $pluralModelLabel = 'العملاء';
    protected static ?int    $navigationSort  = 5;

    // قراءة فقط — تعطيل الإنشاء والتعديل والحذف
    public static function canCreate(): bool  { return false; }
    public static function canEdit($record): bool   { return false; }
    public static function canDelete($record): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('company')
                    ->label('الشركة')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—')
                    ->color(fn ($state) => match($state?->value) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'lead' => 'info',
                        'vip' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_archived')
                    ->label('مؤرشف')
                    ->boolean()
                    ->trueIcon('heroicon-o-archive-box')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('invoice_count')
                    ->label('الفواتير')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('health_score')
                    ->label('نقاط الصحة')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_archived')
                    ->label('الأرشيف')
                    ->options(['0' => 'نشط', '1' => 'مؤرشف']),
            ])
            ->actions([])   // لا إجراءات — قراءة فقط
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
        ];
    }

    // تجاوز Global Scope لعرض جميع العملاء بدون فلتر user_id
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()->with('user');
    }
}
