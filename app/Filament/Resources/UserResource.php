<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'إدارة المستخدمين';
    protected static ?string $navigationLabel = 'المستخدمون';
    protected static ?string $modelLabel      = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمون';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('المعلومات الأساسية')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create'),
            ])->columns(2),

            Forms\Components\Section::make('الاشتراك والإعدادات')->schema([
                Forms\Components\Select::make('subscription_plan')
                    ->label('خطة الاشتراك')
                    ->options([
                        SubscriptionPlan::Free->value     => 'مجاني',
                        SubscriptionPlan::Pro->value      => 'Pro',
                        SubscriptionPlan::Business->value => 'Business',
                    ])
                    ->required(),

                Forms\Components\Select::make('currency')
                    ->label('العملة')
                    ->options([
                        'SAR' => 'ريال سعودي (SAR)',
                        'USD' => 'دولار أمريكي (USD)',
                        'EUR' => 'يورو (EUR)',
                        'GBP' => 'جنيه إسترليني (GBP)',
                        'AED' => 'درهم إماراتي (AED)',
                        'EGP' => 'جنيه مصري (EGP)',
                    ])
                    ->default('SAR'),

                Forms\Components\Select::make('timezone')
                    ->label('المنطقة الزمنية')
                    ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray())
                    ->searchable()
                    ->default('Asia/Riyadh'),

                Forms\Components\Select::make('roles')
                    ->label('الدور')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('subscription_plan')
                    ->label('الخطة')
                    ->formatStateUsing(fn ($state) => $state instanceof SubscriptionPlan ? $state->label() : $state)
                    ->colors([
                        'gray'    => fn ($state) => $state === SubscriptionPlan::Free  || $state?->value === 'free',
                        'primary' => fn ($state) => $state === SubscriptionPlan::Pro   || $state?->value === 'pro',
                        'success' => fn ($state) => $state === SubscriptionPlan::Business || $state?->value === 'business',
                    ]),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('المشاريع')
                    ->counts('projects')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('المعاملات')
                    ->counts('transactions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('العملة')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_plan')
                    ->label('الخطة')
                    ->options([
                        'free'     => 'مجاني',
                        'pro'      => 'Pro',
                        'business' => 'Business',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
