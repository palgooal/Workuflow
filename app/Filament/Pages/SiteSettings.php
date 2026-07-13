<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * "إعدادات الموقع والتواصل" — صفحة Filament لإدارة بيانات التواصل ووسائل
 * التواصل الاجتماعي الخمس المعروضة في فوتر الموقع التسويقي.
 *
 * تُخزَّن في جدول settings الحالي (group = 'site') بنفس نمط MailSettings/
 * PaymentSettings — بدون أي جدول جديد. القراءة العامة (من الفوتر) تمر عبر
 * Setting::group('site') والتي تُخزَّن بالكامل عبر Cache::rememberForever
 * وتُمسَح تلقائياً عند setGroup() — لا حاجة لأي منطق Cache إضافي هنا.
 */
class SiteSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'إعدادات الموقع والتواصل';
    protected static ?string $navigationGroup = 'المحتوى والصفحات';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.site-settings';

    public array $data = [];

    /** المنصات الاجتماعية المدعومة — المصدر الوحيد للحقيقة لأسماء الحقول والترتيب */
    public const SOCIAL_PLATFORMS = [
        'x'         => 'X (Twitter)',
        'facebook'  => 'Facebook',
        'linkedin'  => 'LinkedIn',
        'instagram' => 'Instagram',
        'whatsapp'  => 'WhatsApp',
    ];

    public function mount(): void
    {
        $saved = Setting::group('site');

        $this->data = [
            'site_contact_email' => $saved['site_contact_email'] ?? '',
            'site_contact_phone' => $saved['site_contact_phone'] ?? '',
            'site_whatsapp_url'  => $saved['site_whatsapp_url']  ?? '',
            'site_location'      => $saved['site_location']      ?? '',
            'footer_description' => $saved['footer_description'] ?? '',
        ];

        foreach (array_keys(self::SOCIAL_PLATFORMS) as $key) {
            $this->data["social_{$key}_url"]     = $saved["social_{$key}_url"]     ?? '';
            $this->data["social_{$key}_enabled"] = filter_var($saved["social_{$key}_enabled"] ?? false, FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات التواصل العامة')
                    ->icon('heroicon-o-envelope')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('site_contact_email')
                            ->label('البريد الإلكتروني للتواصل')
                            ->email()
                            ->placeholder('support@darahum.com'),

                        Forms\Components\TextInput::make('site_contact_phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->placeholder('+970 5X XXX XXXX'),

                        Forms\Components\TextInput::make('site_whatsapp_url')
                            ->label('رابط واتساب للتواصل المباشر')
                            ->url()
                            ->placeholder('https://wa.me/9705XXXXXXXX')
                            ->helperText('يقبل رابط wa.me أو أي رابط صالح آخر.')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('site_location')
                            ->label('الموقع/المدينة الظاهرة في الفوتر')
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('footer_description')
                            ->label('الوصف التعريفي في الفوتر')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('نص عادي فقط — لا يُسمح بإدخال HTML هنا.')
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('وسائل التواصل الاجتماعي')
                    ->icon('heroicon-o-share')
                    ->description('لا تظهر أي أيقونة في الفوتر إلا إذا كانت مفعّلة ولها رابط صالح.')
                    ->schema(collect(self::SOCIAL_PLATFORMS)->map(
                        fn (string $label, string $key) => Forms\Components\Fieldset::make($label)
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make("social_{$key}_url")
                                    ->label('الرابط')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->columnSpan(2),

                                Forms\Components\Toggle::make("social_{$key}_enabled")
                                    ->label('مفعّلة')
                                    ->default(false),
                            ])
                    )->values()->all()),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // دفاع إضافي: منع أي وسم HTML من الوصول لإعدادات التواصل حتى لو
        // تجاوز أحدهم واجهة الفورم (لا نثق بمصدر واحد فقط للتعقيم).
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = strip_tags($value);
            }
        }

        Setting::setGroup('site', $data);

        Notification::make()
            ->title('✅ تم حفظ إعدادات الموقع والتواصل')
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
        ];
    }

    /**
     * روابط التواصل الاجتماعي الجاهزة للعرض العام (الفوتر) — مفعّلة وذات
     * رابط غير فارغ فقط. تُستخدَم بدلاً من config('services.social.*').
     *
     * @return array<string, array{label: string, url: string}>
     */
    public static function activeSocialLinks(): array
    {
        $saved = Setting::group('site');
        $links = [];

        foreach (self::SOCIAL_PLATFORMS as $key => $label) {
            $enabled = filter_var($saved["social_{$key}_enabled"] ?? false, FILTER_VALIDATE_BOOLEAN);
            $url     = trim((string) ($saved["social_{$key}_url"] ?? ''));

            if ($enabled && $url !== '') {
                $links[$key] = ['label' => $label, 'url' => $url];
            }
        }

        return $links;
    }
}
