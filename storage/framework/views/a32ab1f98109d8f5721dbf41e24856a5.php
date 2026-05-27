<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => '']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white rounded-2xl border border-gray-100 p-5">
    <h3 class="font-semibold text-gray-900 mb-3 text-sm flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
        <?php echo e($title); ?>

    </h3>
    <div class="text-sm text-gray-600 leading-relaxed">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/help-card.blade.php ENDPATH**/ ?>