<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class EmailTemplateResource extends Resource
{
    protected static ?string $model           = EmailTemplate::class;
    protected static ?string $navigationIcon  = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'قوالب الرسائل';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?string $modelLabel      = 'قالب رسالة';
    protected static ?string $pluralModelLabel = 'قوالب الرسائل';
    protected static ?int    $navigationSort  = 11;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('معلومات القالب')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم القالب')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Toggle::make('is_active')
                        ->label('مفعَّل')
                        ->default(true),

                    Forms\Components\TextInput::make('subject')
                        ->label('موضوع الرسالة (Subject)')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                ]),

            Forms\Components\Section::make('محتوى الرسالة')
                ->schema([
                    Forms\Components\RichEditor::make('body')
                        ->label('نص الرسالة')
                        ->required()
                        ->toolbarButtons([
                            'bold', 'italic', 'underline',
                            'bulletList', 'orderedList',
                            'link', 'h2', 'h3',
                            'blockquote', 'redo', 'undo',
                        ])
                        ->fileAttachmentsVisibility('public'),
                ]),

            Forms\Components\Section::make('المتغيرات المتاحة')
                ->description('يمكنك استخدام هذه المتغيرات داخل النص وسيتم استبدالها تلقائياً')
                ->schema([
                    Forms\Components\KeyValue::make('variables')
                        ->label('المتغيرات')
                        ->keyLabel('المتغير')
                        ->valueLabel('الوصف')
                        ->addButtonLabel('إضافة متغير')
                        ->reorderable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('المفتاح')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القالب')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعَّل')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),

                Tables\Actions\Action::make('preview')
                    ->label('معاينة')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (EmailTemplate $r) => 'معاينة: ' . $r->name)
                    ->modalContent(fn (EmailTemplate $r) => view('filament.email-preview', ['template' => $r]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                Tables\Actions\Action::make('sendTest')
                    ->label('تجربة')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('test_email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->placeholder('your@email.com'),
                    ])
                    ->modalHeading(fn (EmailTemplate $r) => 'تجربة: ' . $r->name)
                    ->modalDescription('سيُرسَل بمتغيرات افتراضية للمعاينة.')
                    ->modalSubmitActionLabel('إرسال')
                    ->action(function (EmailTemplate $record, array $data): void {
                        $demoVars = [
                            '{{name}}'      => 'محمد (تجريبي)',
                            '{{reset_url}}' => config('app.url') . '/reset-password/DEMO',
                            '{{verify_url}}'=> config('app.url') . '/verify-email/DEMO',
                            '{{login_url}}' => config('app.url') . '/dashboard',
                        ];

                        $subject = $record->subject;
                        $body    = $record->body;
                        foreach ($demoVars as $k => $v) {
                            $subject = str_replace($k, $v, $subject);
                            $body    = str_replace($k, $v, $body);
                        }

                        try {
                            Mail::html(
                                view('emails.template', ['body' => $body])->render(),
                                fn ($msg) => $msg->to($data['test_email'])->subject('[تجريبي] ' . $subject)
                            );
                            Notification::make()->title('✅ تم الإرسال')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('❌ فشل')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->recordUrl(fn (EmailTemplate $r) => static::getUrl('edit', ['record' => $r->key]))
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'edit'  => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteKeyName(): string
    {
        return 'key';
    }
}
