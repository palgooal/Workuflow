<x-app-layout>
    <x-slot name="pageTitle">لوحة التحكم</x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stats-card title="إجمالي الدخل" value="0.00" suffix=" ر.س" color="green" :change="0" />
        <x-stats-card title="إجمالي المصاريف" value="0.00" suffix=" ر.س" color="red" :change="0" />
        <x-stats-card title="صافي الربح" value="0.00" suffix=" ر.س" color="indigo" :change="0" />
        <x-stats-card title="المشاريع النشطة" value="0" color="yellow" />
    </div>

    <div class="mt-6">
        <x-empty-state
            title="لا توجد معاملات بعد"
            description="ابدأ بإضافة مشروع ثم سجّل أول معاملة مالية لك"
            action="{{ route('projects.index') }}"
            actionLabel="إنشاء مشروع"
        />
    </div>
</x-app-layout>
