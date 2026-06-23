@props([
    'align' => 'right',   // right | left | center
])
@php
    $alignClass = match($align) {
        'left'   => 'text-left',
        'center' => 'text-center',
        default  => 'text-right',
    };
@endphp

<th {{ $attributes->merge(['class' => "dash-th $alignClass py-3 px-4 whitespace-nowrap"]) }}>
    {{ $slot }}
</th>
