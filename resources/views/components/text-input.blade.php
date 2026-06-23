@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'dash-field px-3 py-2.5 disabled:opacity-60 disabled:bg-slate-50']) }}>
