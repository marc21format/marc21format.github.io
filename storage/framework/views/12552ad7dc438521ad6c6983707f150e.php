<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="px-5 pt-5 pb-2">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-1000">Positions</h1>
                <p class="text-sm text-slate-500">Manage positions</p>
            </div>
            <div class="flex items-center gap-3">
                <!--[if BLOCK]><![endif]--><?php if(\Illuminate\Support\Facades\Route::has('positions.create')): ?>
                    <a href="<?php echo e(route('positions.create')); ?>" class="text-gray-600 hover:text-gray-800" title="Add position">
                        <i class="fa fa-plus"></i>
                    </a>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>

    <div class="px-5 pb-3">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white-50 p-2">
            <input type="text" wire:model.live="search" placeholder="Search positions..." class="flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200" />
        </div>
    </div>

    <div class="px-5 pb-5">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left p-3 font-medium text-slate-700 flex items-center gap-2">
                            <i class="fa fa-briefcase text-slate-600"></i>
                            Position Name
                        </th>
                        <th class="text-left p-3 font-medium text-slate-700 flex items-center gap-2">
                            <i class="fa fa-users text-slate-600"></i>
                            Committees
                        </th>
                        <th class="text-left p-3 font-medium text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 text-slate-900"><?php echo e($p->position_name ?? $p->position_name); ?></td>
                            <td class="p-3 text-slate-900"><?php echo e($p->committee_names ?? ($p->committee_names ?? '')); ?></td>
                            <td class="p-3">
                                <!--[if BLOCK]><![endif]--><?php if(\Illuminate\Support\Facades\Route::has('positions.edit')): ?>
                                    <a href="<?php echo e(route('positions.edit', ['id' => $p->position_id ?? $p->position_id])); ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <button wire:click="delete(<?php echo e($p->position_id); ?>)" wire:confirm="Delete this position?" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-500 hover:bg-red-50" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>
        <!--[if BLOCK]><![endif]--><?php if(method_exists($this->positions, 'hasPages') && $this->positions->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($this->positions->links()); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH C:\Users\Marc Ian C. Young\laravel\fceerweb\resources\views/livewire/positions/index.blade.php ENDPATH**/ ?>