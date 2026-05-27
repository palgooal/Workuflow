<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['emoji' => '', 'title' => '']));

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

foreach (array_filter((['emoji' => '', 'title' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-4">
    
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-xl shrink-0">
                <?php echo e($emoji); ?>

            </div>
            <h2 class="text-lg font-bold text-gray-900"><?php echo e($title); ?></h2>
        </div>
    </div>

    <?php echo e($slot); ?>

</div>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/help-section.blade.php ENDPATH**/ ?>