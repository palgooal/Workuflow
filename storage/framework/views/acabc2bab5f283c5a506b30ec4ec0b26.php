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
    class="flex items-center gap-3 px-3 py-3 rounded-xl font-medium transition-all min-h-[44px]
           <?php echo e($active
               ? 'bg-[#14C698]/20 text-[#14C698]'
               : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
    style="font-size: 14.5px;"
>
    <span class="shrink-0 <?php echo e($active ? 'text-[#14C698]' : 'text-white/55'); ?>">
        <?php echo e($icon); ?>

    </span>
    <span class="truncate"><?php echo e($slot); ?></span>
</a>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/nav-item.blade.php ENDPATH**/ ?>