@props(['value' => 0, 'max' => 100, 'color' => null])

@php
    $percentage = $max > 0 ? min(round(($value / $max) * 100), 100) : 0;
    $barColor = $color ?? match(true) {
        $percentage >= 100 => 'bg-red-500',
        $percentage >= 80  => 'bg-yellow-500',
        default            => 'bg-green-500',
    };
@endphp

<div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
    <div
        class="{{ $barColor }} h-2 rounded-full transition-all duration-500"
        style="width: {{ $percentage }}%"
    ></div>
</div>
