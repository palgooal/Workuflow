<div class="p-4 bg-white rounded-lg border" style="direction:rtl; font-family: Arial, sans-serif;">
    <div class="mb-3 pb-3 border-b">
        <span class="text-xs text-gray-500">الموضوع:</span>
        <strong class="block text-gray-800">{{ $template->subject }}</strong>
    </div>
    <div class="prose max-w-none text-sm">
        {!! $template->body !!}
    </div>
    @if($template->variables)
    <div class="mt-4 pt-3 border-t">
        <p class="text-xs text-gray-400 mb-1">المتغيرات المتاحة:</p>
        <div class="flex flex-wrap gap-1">
            @foreach($template->variables as $var => $desc)
            <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded">
                <code>{{ $var }}</code> — {{ $desc }}
            </span>
            @endforeach
        </div>
    </div>
    @endif
</div>
