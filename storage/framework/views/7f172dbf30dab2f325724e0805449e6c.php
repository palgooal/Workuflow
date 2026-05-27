<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['number' => '1', 'title' => '']));

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

foreach (array_filter((['number' => '1', 'title' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-start gap-4">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
            <?php echo e($number); ?>

        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-gray-900 mb-2 text-sm"><?php echo e($title); ?></h3>
            <p class="text-sm text-gray-600 leading-relaxed">
                <?php echo e($slot); ?>

            </p>
        </div>
    </div>
</div>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/help-step.blade.php ENDPATH**/ ?>