{{--
    غلاف رقيق فقط: يُستدعى عبر Infolists\Components\View من
    ViewBackup::infolist() (المرحلة الثامنة). $getRecord() مُتاحة تلقائياً
    (Filament\Infolists\Components\Concerns\HasState::getRecord()) — بدون أي
    استعلام إضافي، السجل محمَّل أصلاً في الصفحة.
--}}
<x-backup-timeline :backup="$getRecord()" />
