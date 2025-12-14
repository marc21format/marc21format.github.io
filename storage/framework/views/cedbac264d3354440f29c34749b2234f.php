

<?php $__env->startSection('content'); ?>
    <div class="container">
        <h1>Edit Committee</h1>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('committee.edit', ['id' => request()->route('committee')]);

$__html = app('livewire')->mount($__name, $__params, 'lw-315653029-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Marc Ian C. Young\laravel\fceerweb\resources\views/committees/edit.blade.php ENDPATH**/ ?>