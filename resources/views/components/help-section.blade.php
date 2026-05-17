@props(['emoji' => '', 'title' => ''])

<div class="space-y-4">
    {{-- عنوان القسم --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-xl shrink-0">
                {{ $emoji }}
            </div>
            <h2 class="text-lg font-bold text-gray-900">{{ $title }}</h2>
        </div>
    </div>

    {{ $slot }}
</div>
