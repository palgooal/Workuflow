@props(['title' => ''])

<div class="bg-white rounded-2xl border border-gray-100 p-5">
    <h3 class="font-semibold text-gray-900 mb-3 text-sm flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
        {{ $title }}
    </h3>
    <div class="text-sm text-gray-600 leading-relaxed">
        {{ $slot }}
    </div>
</div>
