<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class MailSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'إعدادات البريد';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.mail-settings';

    public array $data = [];

    public function mount(): void
    {
        $saved = Setting::group('mail');

        $this->data = [
            'mail_host'         => $saved['mail_host']         ?? config('mail.mailers.smtp.host'),
            'mail_port'         => $saved['mail_port']         ?? config('mail.mailers.smtp.port', 465),
            'mail_username'     => $saved['mail_username']     ?? config('mail.mailers.smtp.username'),
            'mail_password'     => $saved['mail_password']     ?? '',
            'mail_encryption'   => $saved['mail_encryption']   ?? config('mail.mailers.smtp.encryption', 'ssl'),
            'mail_scheme'       => $saved['mail_scheme']       ?? config('mail.mailers.smtp.scheme', 'smtps'),
            'mail_from_address' => $saved['mail_from_address'] ?? config('mail.from.address'),
            'mail_from_name'    => $saved['mail_from_name']    ?? config('mail.from.name'),
            'test_email'        => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('إعدادات SMTP')
                    ->description('إعدادات خادم البريد الصادر')
                    ->icon('heroicon-o-server')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('mail_host')
                            ->label('خادم البريد (Host)')
                            ->placeholder('mail.darahum.com')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('mail_port')
                            ->label('المنفذ (Port)')
                            ->numeric()
                            ->placeholder('465')
                            ->required(),

                        Forms\Components\Select::make('mail_encryption')
                            ->label('التشفير (Encryption)')
                            ->options([
                                'ssl' => 'SSL (port 465)',
                                'tls' => 'TLS / STARTTLS (port 587)',
                                ''    => 'بدون تشفير',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'ssl') {
                                    $set('mail_port', 465);
                                    $set('mail_scheme', 'smtps');
                                } elseif ($state === 'tls') {
                                    $set('mail_port', 587);
                                    $set('mail_scheme', null);
                                }
                            }),

                        Forms\Components\Hidden::make('mail_scheme'),

                        Forms\Components\TextInput::make('mail_username')
                            ->label('اسم المستخدم (Username)')
                            ->placeholder('info@darahum.com')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('mail_password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->placeholder('اتركه فارغاً للإبقاء على القيمة الحالية')
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('معلومات المُرسِل')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('mail_from_address')
                            ->label('بريد المُرسِل')
                            ->email()
                            ->placeholder('info@darahum.com')
                            ->required(),

                        Forms\Components\TextInput::make('mail_from_name')
                            ->label('اسم المُرسِل')
                            ->placeholder('دراهم')
                            ->required(),
                    ]),

                Forms\Components\Section::make('اختبار الإرسال')
                    ->icon('heroicon-o-paper-airplane')
                    ->description('أرسل بريداً تجريبياً للتحقق من صحة الإعدادات')
                    ->schema([
                        Forms\Components\TextInput::make('test_email')
                            ->label('البريد الإلكتروني للاختبار')
                            ->email()
                            ->placeholder('your@email.com'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // إذا تُركت كلمة المرور فارغة — لا تحدّثها
        $saveData = array_filter($data, fn($v, $k) => $k !== 'test_email' && ($k !== 'mail_password' || $v !== ''), ARRAY_FILTER_USE_BOTH);

        Setting::setGroup('mail', $saveData);

        Notification::make()
            ->title('✅ تم حفظ إعدادات البريد')
            ->success()
            ->send();
    }

    public function sendTest(): void
    {
        $data = $this->form->getState();
        $to   = $data['test_email'] ?? '';

        if (empty($to)) {
            Notification::make()
                ->title('أدخل بريد الاختبار أولاً')
                ->warning()
                ->send();
            return;
        }

        // حفظ الإعدادات أولاً ثم الإرسال
        $this->save();

        try {
            Mail::raw('هذا بريد تجريبي من دراهم — الإعدادات تعمل بنجاح ✅', function ($msg) use ($to, $data) {
                $msg->to($to)
                    ->subject('اختبار إعدادات البريد — دراهم')
                    ->from($data['mail_from_address'], $data['mail_from_name']);
            });

            Notification::make()
                ->title("✅ تم إرسال البريد التجريبي إلى {$to}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('❌ فشل إرسال البريد')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('حفظ الإعدادات')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),

            \Filament\Actions\Action::make('sendTest')
                ->label('إرسال بريد تجريبي')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->action('sendTest'),
        ];
    }
}
