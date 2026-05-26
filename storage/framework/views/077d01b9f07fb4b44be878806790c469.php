<?php $__env->startSection('title', 'مشروع جديد'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="text-gray-300">/</span>
    <a href="<?php echo e(route('projects.index')); ?>" class="text-gray-500 hover:text-gray-700">المشاريع</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">مشروع جديد</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">إنشاء مشروع جديد</h1>
        <p class="mt-1 text-sm text-gray-500">حدد تفاصيل المشروع لتبدأ بتتبع أرباحه وتكاليفه</p>
    </div>

    <form method="POST" action="<?php echo e(route('projects.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('projects._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/projects/create.blade.php ENDPATH**/ ?>