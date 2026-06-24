<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Modules\Billing\Services\TogoPaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class PaymentSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'بوابة الدفع';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int    $navigationSort  = 20;
    protected static string  $view            = 'filament.pages.payment-settings';

    public array $data = [];

    public function mount(): void
    {
        $saved = Setting::group('payment');

        $this->data = [
            // حالة البوابة
            'billing_provider'             => $saved['billing_provider']             ?? config('billing.provider', ''),

            // Togo API
            'togo_api_key'                 => $saved['togo_api_key']                 ?? config('billing.togo.api_key', ''),
            'togo_receiver_address_id'     => $saved['togo_receiver_address_id']     ?? config('billing.togo.receiver_address_id', ''),
            'togo_currency'                => $saved['togo_currency']                ?? config('billing.togo.currency', 'ILS'),

            // أسعار الخطط
            'billing_price_pro'            => $saved['billing_price_pro']            ?? config('billing.plans.pro.price', '99'),
            'billing_price_business'       => $saved['billing_price_business']       ?? config('billing.plans.business.price', '299'),
            'billing_currency_display'     => $saved['billing_currency_display']     ?? config('billing.plans.pro.currency', 'ILS'),

            // حقول إنشاء receiver address (مؤقتة — لا تُحفظ)
            'receiver_name'                => '',
            'receiver_phone'               => '',
            'receiver_country_code'        => 'PS',
            'receiver_country_name'        => 'Palestine',
            'receiver_city'                => '',
            'receiver_details'             => '',
            'receiver_whatsapp'            => true,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                // ── القسم 1: حالة البوابة ──────────────────────────────
                Forms\Components\Section::make('حالة بوابة الدفع')
                    ->icon('heroicon-o-power')
                    ->description('تفعيل أو تعطيل بوابة الدفع الآلي')
                    ->schema([
                        Forms\Components\Select::make('billing_provider')
                            ->label('مزود الدفع المفعّل')
                            ->options([
                                ''     => 'معطّل (لا يوجد بوابة دفع)',
                                'togo' => 'Togo (togo.ps)',
                            ])
                            ->default('')
                            ->live()
                            ->helperText('اختر "معطّل" لاستخدام الترقية اليدوية عبر واتساب'),
                    ]),

                // ── القسم 2: إعدادات Togo API ──────────────────────────
                Forms\Components\Section::make('إعدادات Togo API')
                    ->icon('heroicon-o-key')
                    ->description('أدخل بيانات حساب Togo الخاص بك')
                    ->visible(fn (Forms\Get $get) => $get('billing_provider') === 'togo')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('togo_api_key')
                            ->label('مفتاح API (x-api-key)')
                            ->password()
                            ->revealable()
                            ->placeholder('أدخل مفتاح API من لوحة Togo')
                            ->helperText('من لوحة Togo: Users → Add New User → API Key')
                            ->required(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('togo_receiver_address_id')
                                    ->label('Receiver Address ID')
                                    ->placeholder('سيظهر هنا تلقائياً بعد الإنشاء')
                                    ->helperText('أنشئ العنوان من القسم أدناه أولاً ثم احفظ')
                                    ->nullable(),

                                Forms\Components\Select::make('togo_currency')
                                    ->label('عملة الدفع')
                                    ->options([
                                        'ILS' => 'شيكل (ILS)',
                                        'USD' => 'دولار (USD)',
                                    ])
                                    ->default('ILS')
                                    ->required(),
                            ]),
                    ]),

                // ── القسم 3: إنشاء Receiver Address ───────────────────
                Forms\Components\Section::make('إنشاء عنوان المستقبل (Receiver Address)')
                    ->icon('heroicon-o-map-pin')
                    ->description('يُنفَّذ مرة واحدة فقط. البيانات يجب أن تطابق الحساب المسجّل في Togo.')
                    ->visible(fn (Forms\Get $get) => $get('billing_provider') === 'togo')
                    ->collapsible()
                    ->collapsed(fn () => ! empty($this->data['togo_receiver_address_id']))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Placeholder::make('ascii_warning')
                            ->label('')
                            ->content('⚠️  جميع الحقول أدناه يجب إدخالها بالإنجليزية فقط (Latin characters). Togo API لا يقبل الحروف العربية.')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('receiver_name')
                            ->label('الاسم الكامل')
                            ->placeholder('Ahmad Mahmoud')
                            ->helperText('بالإنجليزية فقط')
                            ->rules(['regex:/^[\x20-\x7E]*$/'])
                            ->validationMessages(['regex' => 'الاسم يجب أن يكون بالإنجليزية فقط (بدون حروف عربية).'])
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('receiver_phone')
                            ->label('رقم الهاتف')
                            ->placeholder('+970591234567')
                            ->helperText('أرقام فقط مع كود الدولة'),

                        Forms\Components\TextInput::make('receiver_country_code')
                            ->label('كود الدولة')
                            ->placeholder('PS')
                            ->default('PS')
                            ->helperText('PS أو JO أو ...'),

                        Forms\Components\TextInput::make('receiver_country_name')
                            ->label('اسم الدولة')
                            ->placeholder('Palestine')
                            ->default('Palestine')
                            ->helperText('بالإنجليزية فقط')
                            ->rules(['regex:/^[\x20-\x7E]*$/'])
                            ->validationMessages(['regex' => 'اسم الدولة يجب أن يكون بالإنجليزية فقط.']),

                        Forms\Components\TextInput::make('receiver_city')
                            ->label('المدينة')
                            ->placeholder('Ramallah')
                            ->helperText('بالإنجليزية فقط')
                            ->rules(['regex:/^[\x20-\x7E]*$/'])
                            ->validationMessages(['regex' => 'المدينة يجب أن تكون بالإنجليزية فقط.']),

                        Forms\Components\TextInput::make('receiver_details')
                            ->label('تفاصيل إضافية (اختياري)')
                            ->placeholder('West Bank')
                            ->helperText('بالإنجليزية فقط إن وُجدت')
                            ->rules(['nullable', 'regex:/^[\x20-\x7E]*$/'])
                            ->validationMessages(['regex' => 'التفاصيل يجب أن تكون بالإنجليزية فقط.'])
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('receiver_whatsapp')
                            ->label('الرقم متصل بواتساب')
                            ->default(true)
                            ->columnSpan(2),
                    ]),

                // ── القسم 4: أسعار الخطط ──────────────────────────────
                Forms\Components\Section::make('أسعار خطط الاشتراك')
                    ->icon('heroicon-o-tag')
                    ->description('الأسعار المعروضة في صفحة الترقية')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('billing_price_pro')
                            ->label('سعر خطة Pro')
                            ->numeric()
                            ->placeholder('99')
                            ->required()
                            ->suffix(fn (Forms\Get $get) => $get('billing_currency_display') ?: 'ILS'),

                        Forms\Components\TextInput::make('billing_price_business')
                            ->label('سعر خطة Business')
                            ->numeric()
                            ->placeholder('299')
                            ->required()
                            ->suffix(fn (Forms\Get $get) => $get('billing_currency_display') ?: 'ILS'),

                        Forms\Components\Select::make('billing_currency_display')
                            ->label('عملة العرض')
                            ->options([
                                'ILS' => 'شيكل (₪)',
                                'USD' => 'دولار ($)',
                                'SAR' => 'ريال (SAR)',
                            ])
                            ->default('ILS')
                            ->required(),
                    ]),

            ])
            ->statePath('data');
    }

    // ── Actions ───────────────────────────────────────────────────────────

    public function save(): void
    {
        $data = $this->form->getState();

        $toSave = [
            'billing_provider'         => $data['billing_provider'],
            'togo_api_key'             => $data['togo_api_key'],
            'togo_receiver_address_id' => $data['togo_receiver_address_id'],
            'togo_currency'            => $data['togo_currency'],
            'billing_price_pro'        => $data['billing_price_pro'],
            'billing_price_business'   => $data['billing_price_business'],
            'billing_currency_display' => $data['billing_currency_display'],
        ];

        // احفظ كل قيمة بشكل مستقل حتى تُحفظ القيم الفارغة (مثل مسح togo_receiver_address_id)
        foreach ($toSave as $key => $value) {
            Setting::set($key, $value ?? '', 'payment');
        }

        Notification::make()
            ->title('✅ تم حفظ إعدادات بوابة الدفع')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $data   = $this->form->getState();
        $apiKey = $data['togo_api_key'] ?? '';

        if (empty($apiKey)) {
            Notification::make()
                ->title('أدخل مفتاح API أولاً')
                ->warning()
                ->send();
            return;
        }

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(10)
                ->get('https://api.togo.ps/api/v1/currency-exchange');

            if ($response->successful()) {
                Notification::make()
                    ->title('✅ الاتصال بـ Togo يعمل بنجاح')
                    ->body('المفتاح صحيح والـ API يستجيب.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('❌ خطأ في الاتصال')
                    ->body("كود الخطأ: {$response->status()} — تحقق من صحة المفتاح.")
                    ->danger()
                    ->persistent()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('❌ تعذّر الاتصال بـ Togo')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function createReceiverAddress(): void
    {
        $data = $this->form->getState();

        $name        = trim($data['receiver_name']         ?? '');
        $phone       = trim($data['receiver_phone']        ?? '');
        $city        = trim($data['receiver_city']         ?? '');
        $countryCode = strtoupper(trim($data['receiver_country_code'] ?? 'PS'));
        $countryName = trim($data['receiver_country_name'] ?? 'Palestine');
        $details     = trim($data['receiver_details']      ?? '') ?: 'N/A';
        $apiKey      = trim($data['togo_api_key']          ?? '');

        if (empty($name) || empty($phone) || empty($city)) {
            Notification::make()
                ->title('أدخل الاسم والهاتف والمدينة أولاً')
                ->warning()
                ->send();
            return;
        }

        if (empty($apiKey)) {
            Notification::make()
                ->title('أدخل مفتاح API وتأكد من حفظه أولاً')
                ->warning()
                ->send();
            return;
        }

        // تحقق يدوي من ASCII قبل الإرسال — Togo لا يقبل الحروف العربية
        $fieldsToCheck = [
            'الاسم'    => $name,
            'المدينة'  => $city,
            'الدولة'   => $countryName,
            'التفاصيل' => $details,
        ];
        foreach ($fieldsToCheck as $label => $value) {
            for ($i = 0; $i < strlen($value); $i++) {
                if (ord($value[$i]) > 127) {
                    Notification::make()
                        ->title("❌ حقل [{$label}] يحتوي على حروف عربية")
                        ->body("Togo يقبل الإنجليزية فقط. الحرف المشكل: \"{$value[$i]}\" — أدخل القيمة بالإنجليزية.")
                        ->danger()
                        ->persistent()
                        ->send();
                    return;
                }
            }
        }

        try {
            // اضبط الـ key في config أولاً ثم أنشئ الـ service
            config(['billing.togo.api_key' => $apiKey]);
            $togo = new TogoPaymentService();

            $result = $togo->createReceiverAddress(
                name: $name,
                phone: $phone,
                countryCode: $countryCode,
                countryName: $countryName,
                city: $city,
                details: $details,
                phoneConnectedToWhatsapp: (bool) ($data['receiver_whatsapp'] ?? true),
            );

            // أضف الـ ID للنموذج تلقائياً
            $this->data['togo_receiver_address_id'] = $result['id'];
            $this->form->fill($this->data);

            Notification::make()
                ->title('✅ تم إنشاء Receiver Address')
                ->body("الـ ID: {$result['id']} — احفظ الإعدادات الآن.")
                ->success()
                ->persistent()
                ->send();

        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('❌ فشل إنشاء Receiver Address')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function clearReceiverId(): void
    {
        $this->data['togo_receiver_address_id'] = '';
        $this->form->fill($this->data);
        Setting::set('togo_receiver_address_id', '', 'payment');

        Notification::make()
            ->title('تم مسح Receiver Address ID')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('حفظ الإعدادات')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),

            \Filament\Actions\Action::make('testConnection')
                ->label('اختبار الاتصال')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->action('testConnection'),

            \Filament\Actions\Action::make('createReceiverAddress')
                ->label('إنشاء Receiver Address')
                ->icon('heroicon-o-map-pin')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إنشاء عنوان المستقبل')
                ->modalDescription('سيتم إرسال البيانات لـ Togo API لإنشاء receiver address. تأكد من صحة البيانات في القسم الثالث أدناه.')
                ->modalSubmitActionLabel('إنشاء')
                ->action('createReceiverAddress'),

            \Filament\Actions\Action::make('clearReceiverId')
                ->label('مسح الـ ID')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('مسح Receiver Address ID')
                ->modalDescription('سيتم مسح الـ ID الحالي. ستحتاج لإنشاء عنوان جديد.')
                ->modalSubmitActionLabel('مسح')
                ->action('clearReceiverId'),
        ];
    }
}
