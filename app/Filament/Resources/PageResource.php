<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Support\Content\ReservedSlugs;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model            = Page::class;
    protected static ?string $navigationIcon   = 'heroicon-o-document-text';
    protected static ?string $navigationLabel  = 'الصفحات';
    protected static ?string $navigationGroup  = 'المحتوى والصفحات';
    protected static ?string $modelLabel       = 'صفحة';
    protected static ?string $pluralModelLabel = 'الصفحات';
    protected static ?int    $navigationSort   = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('معلومات أساسية')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $state, Forms\Set $set, string $operation) {
                            // اقتراح slug تلقائياً عند الإنشاء فقط — لا يُعاد توليده عند التعديل
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('الرابط (Slug)')
                        ->required()
                        ->maxLength(255)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->rule(function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (ReservedSlugs::isReserved((string) $value)) {
                                    $fail('هذا الرابط محجوز لاستخدام النظام ولا يمكن استخدامه لصفحة.');
                                }
                            };
                        })
                        ->helperText('يُستخدَم في الرابط العام: /pages/{slug} (باستثناء الصفحات القانونية الأربع الحالية التي تبقى بروابطها الخاصة).'),

                    Forms\Components\Select::make('page_type')
                        ->label('نوع الصفحة')
                        ->options(collect(PageType::cases())->mapWithKeys(fn (PageType $t) => [$t->value => $t->label()]))
                        ->required()
                        ->live()
                        ->default(PageType::General->value),

                    Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options(collect(PageStatus::cases())->mapWithKeys(fn (PageStatus $s) => [$s->value => $s->label()]))
                        ->required()
                        ->live()
                        ->default(PageStatus::Draft->value)
                        ->helperText(fn (Forms\Get $get) => $get('page_type') === PageType::Legal->value
                            ? 'صفحة قانونية سبق نشرها لا يمكن حذفها نهائياً — الأرشفة فقط.'
                            : null),
                ]),

            Forms\Components\Section::make('المحتوى')
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->label('المحتوى')
                        ->required()
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'bulletList', 'orderedList',
                            'link', 'h2', 'h3',
                            'blockquote', 'redo', 'undo',
                        ])
                        // لا نسمح بمرفقات/صور مرفوعة داخل المحرر — نثر آمن فقط، لا Page Builder
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('مقتطف (اختياري)')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('إعدادات الفوتر')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('show_in_footer')
                        ->label('إظهار في الفوتر')
                        ->live()
                        ->default(false),

                    Forms\Components\Select::make('footer_group')
                        ->label('مجموعة الفوتر')
                        ->options(collect(PageFooterGroup::cases())->mapWithKeys(fn (PageFooterGroup $g) => [$g->value => $g->label()]))
                        ->default(PageFooterGroup::None->value)
                        ->required()
                        ->visible(fn (Forms\Get $get) => (bool) $get('show_in_footer')),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتيب الظهور')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('footer_label')
                        ->label('نص الرابط في الفوتر (اختياري)')
                        ->maxLength(255)
                        ->helperText('إن تُرك فارغاً يُستخدَم العنوان (title) كما هو.')
                        ->visible(fn (Forms\Get $get) => (bool) $get('show_in_footer'))
                        ->columnSpan(3),
                ]),

            Forms\Components\Section::make('تحسين محركات البحث (SEO)')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('meta_title')->label('Meta Title')->maxLength(255),
                    Forms\Components\TextInput::make('meta_description')->label('Meta Description')->maxLength(255),
                    Forms\Components\TextInput::make('og_description')->label('OG Description')->maxLength(255)->columnSpan(2),
                ]),

            Forms\Components\Section::make('بيانات النسخة القانونية')
                ->columns(2)
                ->visible(fn (Forms\Get $get) => $get('page_type') === PageType::Legal->value)
                ->schema([
                    Forms\Components\TextInput::make('document_version')
                        ->label('رقم النسخة الحالي')
                        ->disabled()
                        ->dehydrated(true)
                        ->helperText('يُحدَّث تلقائياً عند إعادة نشر تعديل على صفحة قانونية منشورة.'),

                    Forms\Components\DateTimePicker::make('last_reviewed_at')
                        ->label('آخر مراجعة')
                        ->disabled()
                        ->dehydrated(true),

                    Forms\Components\Textarea::make('change_note')
                        ->label('سبب التعديل (Change Note)')
                        ->helperText('مطلوب عند إعادة نشر تعديل على صفحة قانونية منشورة أصلاً — يُحفَظ في سجل النسخ (page_revisions).')
                        // يجب أن يبقى dehydrated لتصل قيمته فعلياً إلى
                        // EditPage::mutateFormDataBeforeSave() (الذي يقرأه
                        // ثم يزيله بنفسه عبر unset() قبل تحديث السجل، لأنه
                        // ليس عموداً في جدول pages). ضبطه على false هنا كان
                        // يمنع القيمة من الوصول أصلاً فيبقى change_note دائماً
                        // null في سجل page_revisions رغم إدخال المستخدم له.
                        ->dehydrated(true)
                        ->required(fn (?Page $record) => $record?->requiresChangeNote() ?? false)
                        ->visible(fn (?Page $record) => $record?->page_type === PageType::Legal)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('page_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (PageType $state) => $state->label()),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (PageStatus $state) => $state->color())
                    ->formatStateUsing(fn (PageStatus $state) => $state->label()),

                Tables\Columns\IconColumn::make('show_in_footer')
                    ->label('في الفوتر')
                    ->boolean(),

                Tables\Columns\TextColumn::make('footer_group')
                    ->label('مجموعة الفوتر')
                    ->formatStateUsing(fn (PageFooterGroup $state) => $state->label())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('document_version')
                    ->label('النسخة')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updater.name')
                    ->label('آخر مُعدِّل')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('page_type')
                    ->label('النوع')
                    ->options(collect(PageType::cases())->mapWithKeys(fn (PageType $t) => [$t->value => $t->label()])),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(PageStatus::cases())->mapWithKeys(fn (PageStatus $s) => [$s->value => $s->label()])),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
