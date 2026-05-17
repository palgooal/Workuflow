@props(['number' => '1', 'title' => ''])

<div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-start gap-4">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
            {{ $number }}
        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-gray-900 mb-2 text-sm">{{ $title }}</h3>
            <p class="text-sm text-gray-600 leading-relaxed">
                {{ $slot }}
            </p>
        </div>
    </div>
</div>
