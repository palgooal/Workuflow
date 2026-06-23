@props([
    'head' => null,   // محتوى <thead> (صف من table-th) — يُمرَّر كـ named slot
])
{{--
    جدول بيانات موحّد. الاستخدام:
    <x-data-table>
        <x-slot name="head">
            <x-table-th>العمود</x-table-th> ...
        </x-slot>
        ... صفوف <tr class="dash-row"> ...
        <x-slot name="pagination">{{ $items->links() }}</x-slot>
    </x-data-table>
--}}

<div {{ $attributes->merge(['class' => 'dash-card overflow-hidden']) }}>
    <div class="overflow-x-auto scrollbar-hidden">
        <table class="w-full text-sm">
            @isset($head)
                <thead>
                    <tr class="bg-slate-50/70 border-b border-subtle">
                        {{ $head }}
                    </tr>
                </thead>
            @endisset
            <tbody class="divide-y divide-subtle/70">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($pagination)
        <div class="px-4 py-3 border-t border-subtle">
            {{ $pagination }}
        </div>
    @endisset
</div>
