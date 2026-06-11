<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title'); ?> — <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <div class="mb-8">
            <span class="text-8xl font-black text-indigo-600 dark:text-indigo-400"><?php echo $__env->yieldContent('code'); ?></span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $__env->yieldContent('title'); ?></h1>
        <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto"><?php echo $__env->yieldContent('message'); ?></p>
        <div class="flex gap-3 justify-center">
            <a href="<?php echo e(url()->previous('#') !== '#' ? url()->previous() : route('dashboard')); ?>"
               class="px-5 py-2.5 bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-gray-700 transition">
                رجوع
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('dashboard')); ?>"
               class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                لوحة التحكم
            </a>
            <?php else: ?>
            <a href="<?php echo e(route('login')); ?>"
               class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                تسجيل الدخول
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/errors/layout.blade.php ENDPATH**/ ?>