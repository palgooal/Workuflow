<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use App\Support\Enums\UserStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

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

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        UserStatus::Active->value    => 'نشط',
                        UserStatus::Suspended->value => 'موقوف',
                    ])
                    ->default(UserStatus::Active->value)
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
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => $state instanceof UserStatus ? $state->label() : $state)
                    ->colors([
                        'success' => fn ($state) => $state === UserStatus::Active    || $state?->value === 'active',
                        'danger'  => fn ($state) => $state === UserStatus::Suspended || $state?->value === 'suspended',
                    ]),

                Tables\Columns\BadgeColumn::make('subscription_plan')
                    ->label('الخطة')
                    ->formatStateUsing(fn ($state) => $state instanceof SubscriptionPlan ? $state->label() : $state)
                    ->colors([
                        'gray'    => fn ($state) => $state === SubscriptionPlan::Free     || $state?->value === 'free',
                        'primary' => fn ($state) => $state === SubscriptionPlan::Pro      || $state?->value === 'pro',
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

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'    => 'نشط',
                        'suspended' => 'موقوف',
                    ]),
            ])
            ->actions([
                // ─── تعديل ───────────────────────────────────────────
                Tables\Actions\EditAction::make()->label('تعديل'),

                // ─── تعليق الحساب ────────────────────────────────────
                Tables\Actions\Action::make('suspend')
                    ->label('تعليق')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تعليق الحساب')
                    ->modalDescription(fn (User $record) => "هل أنت متأكد من تعليق حساب {$record->name}؟ لن يتمكن من تسجيل الدخول.")
                    ->modalSubmitActionLabel('نعم، علّق الحساب')
                    ->visible(fn (User $record) => $record->status !== UserStatus::Suspended)
                    ->action(function (User $record): void {
                        $record->update(['status' => UserStatus::Suspended]);
                        Notification::make()
                            ->title('تم تعليق الحساب')
                            ->body("تم تعليق حساب {$record->name} بنجاح.")
                            ->warning()
                            ->send();
                    }),

                // ─── تفعيل الحساب ────────────────────────────────────
                Tables\Actions\Action::make('activate')
                    ->label('تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تفعيل الحساب')
                    ->modalDescription(fn (User $record) => "هل تريد إعادة تفعيل حساب {$record->name}؟")
                    ->modalSubmitActionLabel('نعم، فعّل الحساب')
                    ->visible(fn (User $record) => $record->status === UserStatus::Suspended)
                    ->action(function (User $record): void {
                        $record->update(['status' => UserStatus::Active]);
                        Notification::make()
                            ->title('تم تفعيل الحساب')
                            ->body("تم تفعيل حساب {$record->name} بنجاح.")
                            ->success()
                            ->send();
                    }),

                // ─── إعادة ضبط الخطة إلى مجاني ──────────────────────
                Tables\Actions\Action::make('resetPlan')
                    ->label('خطة مجانية')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة ضبط الخطة')
                    ->modalDescription(fn (User $record) => "سيتم تحويل {$record->name} إلى الخطة المجانية وإلغاء اشتراكه الحالي.")
                    ->modalSubmitActionLabel('نعم، أعد الضبط')
                    ->visible(fn (User $record) => $record->subscription_plan !== SubscriptionPlan::Free)
                    ->action(function (User $record): void {
                        // إلغاء الاشتراك النشط
                        $record->subscriptions()
                            ->where('status', 'active')
                            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

                        // إعادة الخطة إلى Free
                        $record->update(['subscription_plan' => SubscriptionPlan::Free]);

                        Notification::make()
                            ->title('تم إعادة الضبط')
                            ->body("تم تحويل {$record->name} إلى الخطة المجانية.")
                            ->warning()
                            ->send();
                    }),

                // ─── إرسال بريد إلكتروني ─────────────────────────────
                Tables\Actions\Action::make('sendEmail')
                    ->label('إرسال بريد')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('subject')
                            ->label('موضوع البريد')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('موضوع الرسالة...'),

                        Forms\Components\Textarea::make('message')
                            ->label('نص الرسالة')
                            ->required()
                            ->rows(5)
                            ->placeholder('اكتب رسالتك هنا...'),
                    ])
                    ->modalHeading('إرسال بريد إلكتروني')
                    ->modalSubmitActionLabel('إرسال')
                    ->action(function (User $record, array $data): void {
                        try {
                            Mail::raw($data['message'], function ($mail) use ($record, $data) {
                                $mail->to($record->email, $record->name)
                                     ->subject($data['subject'])
                                     ->from(config('mail.from.address'), config('mail.from.name'));
                            });

                            Notification::make()
                                ->title('تم إرسال البريد')
                                ->body("تم إرسال البريد إلى {$record->email} بنجاح.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل الإرسال')
                                ->body('حدث خطأ أثناء إرسال البريد: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ─── حذف بيانات المستخدم ─────────────────────────────
                Tables\Actions\Action::make('deleteData')
                    ->label('حذف البيانات')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('حذف بيانات المستخدم')
                    ->modalDescription(fn (User $record) => "⚠️ سيتم حذف جميع بيانات {$record->name} (المشاريع، المعاملات، الديون، الميزانيات، الإشعارات). هذا الإجراء لا يمكن التراجع عنه!")
                    ->modalSubmitActionLabel('نعم، احذف كل البيانات')
                    ->form([
                        Forms\Components\Checkbox::make('confirm')
                            ->label('أؤكد حذف جميع البيانات بشكل نهائي')
                            ->required()
                            ->accepted(),
                    ])
                    ->action(function (User $record): void {
                        // حذف جميع البيانات المرتبطة (مع الحفاظ على الحساب)
                        $record->transactions()->delete();
                        $record->debts()->delete();
                        $record->budgets()->delete();
                        $record->recurringTransactions()->delete();
                        $record->subscriptions()->delete();
                        $record->notifications()->delete();
                        // الفئات والمشاريع تُحذف أخيراً (لأن المعاملات مرتبطة بها)
                        $record->categories()->delete();
                        $record->projects()->delete();
                        // إعادة الخطة إلى Free
                        $record->update([
                            'subscription_plan' => SubscriptionPlan::Free,
                        ]);

                        Notification::make()
                            ->title('تم حذف البيانات')
                            ->body("تم حذف جميع بيانات {$record->name} بنجاح.")
                            ->danger()
                            ->send();
                    }),

                // ─── دخول كمستخدم (Impersonate) ──────────────────────
                Tables\Actions\Action::make('loginAs')
                    ->label('دخول كمستخدم')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => "دخول كـ {$record->name}")
                    ->modalDescription(fn (User $record) => "ستدخل إلى لوحة تحكم {$record->name} ({$record->email}). للعودة إلى الأدمن استخدم زر «العودة» في الشريط العلوي.")
                    ->modalSubmitActionLabel('نعم، ادخل كمستخدم')
                    ->url(fn (User $record) => route('admin.impersonate', $record->id))
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // ─── تعليق جماعي ──────────────────────────────────
                    Tables\Actions\BulkAction::make('suspendAll')
                        ->label('تعليق المحدد')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each->update(['status' => UserStatus::Suspended]);
                            Notification::make()
                                ->title('تم التعليق')
                                ->body('تم تعليق الحسابات المحددة.')
                                ->warning()
                                ->send();
                        }),

                    // ─── تفعيل جماعي ──────────────────────────────────
                    Tables\Actions\BulkAction::make('activateAll')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each->update(['status' => UserStatus::Active]);
                            Notification::make()
                                ->title('تم التفعيل')
                                ->body('تم تفعيل الحسابات المحددة.')
                                ->success()
                                ->send();
                        }),

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

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
