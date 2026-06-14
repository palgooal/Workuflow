
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('upgrade_prompt')): ?>
<?php
    $prompt    = session('upgrade_prompt');
    $ownerWa   = config('billing.owner_whatsapp');
    $waText    = urlencode('مرحباً، أريد الترقية إلى خطة Pro في دراهم');
    $waUrl     = $ownerWa ? 'https://wa.me/' . $ownerWa . '?text=' . $waText : null;
?>
<div
    x-data="{ open: true }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true">

    
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-on:click="open = false"></div>

    
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-5"
         x-on:click.stop>

        
        <button x-on:click="open = false"
                class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        
        <div class="flex flex-col items-center text-center gap-3 pt-2">
            <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/40 rounded-2xl flex items-center justify-center">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">وصلت للحد الأقصى</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($prompt['message']); ?></p>
            </div>
        </div>

        
        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl px-4 py-3 text-sm text-indigo-700 dark:text-indigo-300 text-center">
            <?php echo e($prompt['hint']); ?>

        </div>

        
        <div class="flex flex-col gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($waUrl): ?>
            <a href="<?php echo e($waUrl); ?>" target="_blank"
               class="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition text-sm">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                تواصل معنا على واتساب للترقية
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a href="<?php echo e(route('billing.index')); ?>"
               class="flex items-center justify-center gap-2 w-full py-2.5 border border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 font-medium rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition text-sm">
                عرض خطط الاشتراك
            </a>
            <button x-on:click="open = false"
                    class="w-full py-2.5 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
                لاحقاً
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/upgrade-modal.blade.php ENDPATH**/ ?>