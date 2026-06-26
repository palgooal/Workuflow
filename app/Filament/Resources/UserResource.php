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
use App\Mail\ReEngagementEmail;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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

                Forms\Components\TextInput::make('phone')
                    ->label('رقم الجوال')
                    ->tel()
                    ->placeholder('+970599123456')
                    ->maxLength(30)
                    ->nullable()
                    ->unique(ignoreRecord: true)
                    ->regex('/^\+[1-9]\d{5,14}$/')
                    ->validationMessages([
                        'regex'  => 'صيغة رقم الجوال غير صحيحة. مثال: +970599123456',
                        'unique' => 'رقم الجوال هذا مستخدم من قِبل حساب آخر.',
                    ]),

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
                // ── Identity ───────────────────────────────────────────
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الجوال')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الجوال')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),

                // ── Verification badge ─────────────────────────────────
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('التحقق')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->email_verified_at
                        ? 'مُتحقق'
                        : 'غير مُتحقق'
                    )
                    ->color(fn (string $state): string => $state === 'مُتحقق' ? 'success' : 'danger')
                    ->sortable(),

                // ── Status & Plan ──────────────────────────────────────
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

                // ── Activity counts ────────────────────────────────────
                Tables\Columns\TextColumn::make('projects_count')
                    ->label('المشاريع')
                    ->counts('projects')
                    ->sortable(),

                Tables\Columns\TextColumn::make('clients_count')
                    ->label('العملاء')
                    ->getStateUsing(fn (User $record): int =>
                        DB::table('clients')->where('user_id', $record->id)->count()
                    )
                    ->sortable(false), // لا علاقة Eloquent — الترتيب غير متاح

                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('المعاملات')
                    ->counts('transactions')
                    ->sortable(),

                // ── Login metadata ─────────────────────────────────────
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('آخر دخول')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('لم يسجّل دخولاً')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_login_ip')
                    ->label('IP آخر دخول')
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Registration metadata ──────────────────────────────
                Tables\Columns\TextColumn::make('registration_ip')
                    ->label('IP التسجيل')
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('registration_user_agent')
                    ->label('User-Agent التسجيل')
                    ->limit(40)
                    ->tooltip(fn (User $record): string => $record->registration_user_agent ?? '—')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Date ───────────────────────────────────────────────
                Tables\Columns\TextColumn::make('currency')
                    ->label('العملة')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // ── Existing ───────────────────────────────────────────
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

                // ── Security filters ───────────────────────────────────
                Tables\Filters\Filter::make('email_unverified')
                    ->label('غير مُتحقق من البريد')
                    ->query(fn (Builder $query) => $query->whereNull('email_verified_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('email_verified')
                    ->label('مُتحقق من البريد')
                    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('registered_today')
                    ->label('مسجّل اليوم')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today()))
                    ->toggle(),

                Tables\Filters\Filter::make('never_logged_in')
                    ->label('لم يسجّل دخولاً قط')
                    ->query(fn (Builder $query) => $query->whereNull('last_login_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('no_activity')
                    ->label('بلا نشاط (مشبوه)')
                    ->query(fn (Builder $query) => $query
                        ->whereDoesntHave('projects')
                        ->whereDoesntHave('transactions')
                        ->whereRaw('(SELECT COUNT(*) FROM clients WHERE clients.user_id = users.id) = 0')
                    )
                    ->toggle(),

                // ── Phone filters ──────────────────────────────────────
                Tables\Filters\Filter::make('has_phone')
                    ->label('لديه رقم جوال')
                    ->query(fn (Builder $query) => $query->whereNotNull('phone'))
                    ->toggle(),

                Tables\Filters\Filter::make('missing_phone')
                    ->label('بدون رقم جوال')
                    ->query(fn (Builder $query) => $query->whereNull('phone'))
                    ->toggle(),
            ])
            ->actions([
                // ─── تعديل ────────────────────────────────────────────
                Tables\Actions\EditAction::make()->label('تعديل'),

                // ─── واتساب ───────────────────────────────────────────
                Tables\Actions\Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn (User $record): string => 'https://wa.me/' . ltrim($record->phone ?? '', '+'))
                    ->openUrlInNewTab()
                    ->visible(fn (User $record): bool => filled($record->phone)),

                // ─── إعادة إرسال التحقق ───────────────────────────────
                Tables\Actions\Action::make('resendVerification')
                    ->label('إعادة التحقق')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => "إعادة إرسال بريد التحقق لـ {$record->name}")
                    ->modalDescription('سيُرسل رابط تحقق جديد إلى بريده الإلكتروني.')
                    ->modalSubmitActionLabel('إرسال')
                    ->visible(fn (User $record): bool => $record->email_verified_at === null)
                    ->action(function (User $record): void {
                        $record->sendEmailVerificationNotification();
                        Notification::make()
                            ->title('تم الإرسال')
                            ->body("أُرسل بريد التحقق إلى {$record->email}.")
                            ->success()
                            ->send();
                    }),

                // ─── تعليق الحساب ─────────────────────────────────────
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

                // ─── تفعيل الحساب ─────────────────────────────────────
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

                // ─── إعادة ضبط الخطة إلى مجاني ───────────────────────
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
                        $record->subscriptions()
                            ->where('status', 'active')
                            ->update(['status' => 'cancelled', 'ends_at' => now()]);

                        $record->update(['subscription_plan' => SubscriptionPlan::Free]);

                        Notification::make()
                            ->title('تم إعادة الضبط')
                            ->body("تم تحويل {$record->name} إلى الخطة المجانية.")
                            ->warning()
                            ->send();
                    }),

                // ─── تفعيل خطة مدفوعة ─────────────────────────────────
                Tables\Actions\Action::make('activatePlan')
                    ->label('تفعيل خطة')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->tooltip('تفعيل اشتراك مدفوع لهذا المستخدم')
                    ->visible(fn (User $record) => $record->subscription_plan === SubscriptionPlan::Free)
                    ->form([
                        Forms\Components\Select::make('plan')
                            ->label('الخطة')
                            ->options([
                                'pro'      => 'Pro — $17/شهر',
                                'business' => 'Business — $45/شهر',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('ends_at')
                            ->label('تاريخ انتهاء الاشتراك')
                            ->default(now()->addMonth())
                            ->minDate(now()->addDay())
                            ->required(),

                        Forms\Components\TextInput::make('notes')
                            ->label('ملاحظة الدفع')
                            ->placeholder('مثال: تحويل بنكي — 64 SAR — 24 يونيو 2026')
                            ->helperText('للمراجعة الإدارية فقط — لا تُحفظ في قاعدة البيانات (لا يوجد حقل notes في جدول subscriptions)'),
                    ])
                    ->modalHeading(fn (User $record) => "تفعيل خطة مدفوعة لـ {$record->name}")
                    ->modalSubmitActionLabel('تفعيل الاشتراك')
                    ->action(function (User $record, array $data): void {
                        $providerSubscriptionId = 'manual-' . $record->id . '-' . now()->timestamp;

                        $service      = app(SubscriptionService::class);
                        $subscription = $service->activatePlan($record, $data['plan'], $providerSubscriptionId);

                        $subscription->update([
                            'ends_at' => $data['ends_at'],
                        ]);

                        Notification::make()
                            ->title("تم تفعيل خطة {$data['plan']} للمستخدم {$record->name}")
                            ->body("تنتهي في: " . \Carbon\Carbon::parse($data['ends_at'])->format('d/m/Y'))
                            ->success()
                            ->send();
                    }),

                // ─── إرسال بريد إلكتروني ──────────────────────────────
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

                // ─── إرسال بريد إعادة التفعيل ─────────────────────────
                Tables\Actions\Action::make('sendReEngagement')
                    ->label('إعادة التفعيل')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => "إرسال بريد إعادة التفعيل لـ {$record->name}")
                    ->modalDescription(fn (User $record) => "سيُرسل إيميل «مشروعك الأول ينتظرك» إلى {$record->email}.")
                    ->modalSubmitActionLabel('إرسال')
                    ->action(function (User $record): void {
                        try {
                            Mail::to($record->email, $record->name)
                                ->send(new ReEngagementEmail($record));
                            Notification::make()
                                ->title('تم الإرسال')
                                ->body("أُرسل بريد إعادة التفعيل إلى {$record->email}.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('فشل الإرسال')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ─── حذف حساب سبام (مشروط) ────────────────────────────
                Tables\Actions\Action::make('deleteSpamAccount')
                    ->label('حذف سبام')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('حذف حساب سبام')
                    ->modalDescription(fn (User $record) => implode("\n", [
                        "الحساب: {$record->name} ({$record->email})",
                        "IP التسجيل: " . ($record->registration_ip ?? '—'),
                        "",
                        "الشروط مستوفاة: بريد غير مُتحقق + خطة مجانية + لا مشاريع + لا عملاء + لا معاملات.",
                        "هذا الإجراء لا يمكن التراجع عنه.",
                    ]))
                    ->modalSubmitActionLabel('نعم، احذف الحساب نهائياً')
                    ->visible(function (User $record): bool {
                        // شروط الأمان: يجب استيفاء الخمسة معاً
                        if ($record->email_verified_at !== null) return false;
                        if ($record->subscription_plan !== SubscriptionPlan::Free) return false;
                        if ($record->projects()->exists()) return false;
                        if ($record->transactions()->exists()) return false;
                        if (DB::table('clients')->where('user_id', $record->id)->exists()) return false;
                        return true;
                    })
                    ->action(function (User $record): void {
                        $email = $record->email;
                        $name  = $record->name;

                        // تنظيف البيانات المرتبطة قبل الحذف
                        $record->notifications()->delete();
                        $record->categories()->delete();
                        $record->delete(); // حذف نهائي للحساب

                        Notification::make()
                            ->title('تم حذف حساب السبام')
                            ->body("تم حذف {$name} ({$email}) نهائياً.")
                            ->danger()
                            ->send();
                    }),

                // ─── حذف بيانات المستخدم ──────────────────────────────
                Tables\Actions\Action::make('deleteData')
                    ->label('حذف البيانات')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('حذف بيانات المستخدم')
                    ->modalDescription(fn (User $record) => "سيتم حذف جميع بيانات {$record->name} (المشاريع، المعاملات، الديون، الميزانيات، الإشعارات). هذا الإجراء لا يمكن التراجع عنه!")
                    ->modalSubmitActionLabel('نعم، احذف كل البيانات')
                    ->form([
                        Forms\Components\Checkbox::make('confirm')
                            ->label('أؤكد حذف جميع البيانات بشكل نهائي')
                            ->required()
                            ->accepted(),
                    ])
                    ->action(function (User $record): void {
                        $record->transactions()->delete();
                        $record->debts()->delete();
                        $record->budgets()->delete();
                        $record->recurringTransactions()->delete();
                        $record->subscriptions()->delete();
                        $record->notifications()->delete();
                        $record->categories()->delete();
                        $record->projects()->delete();
                        $record->update([
                            'subscription_plan' => SubscriptionPlan::Free,
                        ]);

                        Notification::make()
                            ->title('تم حذف البيانات')
                            ->body("تم حذف جميع بيانات {$record->name} بنجاح.")
                            ->danger()
                            ->send();
                    }),

                // ─── دخول كمستخدم (Impersonate) ───────────────────────
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

                    // ─── إرسال بريد إعادة التفعيل للمحدد ─────────────
                    Tables\Actions\BulkAction::make('sendReEngagementBulk')
                        ->label('إرسال بريد إعادة التفعيل')
                        ->icon('heroicon-o-rocket-launch')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('إرسال بريد إعادة التفعيل')
                        ->modalDescription(fn ($records) => "سيُرسل إيميل «مشروعك الأول ينتظرك» إلى {$records->count()} مستخدم.")
                        ->modalSubmitActionLabel('إرسال للجميع')
                        ->action(function ($records): void {
                            $sent   = 0;
                            $failed = 0;
                            foreach ($records as $user) {
                                try {
                                    Mail::to($user->email, $user->name)
                                        ->send(new ReEngagementEmail($user));
                                    $sent++;
                                    usleep(200_000);
                                } catch (\Throwable) {
                                    $failed++;
                                }
                            }
                            Notification::make()
                                ->title('اكتمل الإرسال')
                                ->body("أُرسل: {$sent}" . ($failed ? " | فشل: {$failed}" : ''))
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
