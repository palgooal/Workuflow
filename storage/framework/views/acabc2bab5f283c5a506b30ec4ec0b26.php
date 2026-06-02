<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['href', 'active' => false]));

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

foreach (array_filter((['href', 'active' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a
    href="<?php echo e($href); ?>"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
           <?php echo e($active
               ? 'bg-indigo-50 text-indigo-700'
               : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>"
>
    <span class="<?php echo e($active ? 'text-indigo-600' : 'text-gray-400'); ?>">
        <?php echo e($icon); ?>

    </span>
    <?php echo e($slot); ?>

</a>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/nav-item.blade.php ENDPATH**/ ?>