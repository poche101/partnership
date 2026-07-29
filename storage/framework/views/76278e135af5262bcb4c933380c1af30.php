
<?php $__env->startSection('title', 'Partnership Arms'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl px-6 py-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl text-primary">Partnership Arms</h1>
            <p class="mt-1 text-sm text-muted-foreground">Manage the ministry arms partners can give towards. Disabling an arm hides it from new giving entries.</p>
        </div>
        <button data-open-modal="new-arm" class="btn-primary">+ New Arm</button>
    </div>

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Order</th><th>Key</th><th>Label</th><th>Enabled</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $arms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="font-mono text-xs"><?php echo e($arm->sort_order); ?></td>
                        <td class="font-mono text-xs"><?php echo e($arm->key); ?></td>
                        <td class="font-medium"><?php echo e($arm->label); ?></td>
                        <td>
                            <form method="POST" action="<?php echo e(route('arms.update', $arm)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <input type="hidden" name="enabled" value="<?php echo e($arm->enabled ? 0 : 1); ?>">
                                <button class="badge <?php echo e($arm->enabled ? 'bg-accent/20 text-accent' : ''); ?>">
                                    <?php echo e($arm->enabled ? 'Enabled' : 'Disabled'); ?>

                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <p class="mt-4 text-xs text-muted-foreground">
        Note: new arms need a matching column added to the <code>partnership_entries</code> table (a migration) before they can
        be recorded against — the same limitation the original app had.
    </p>
</div>

<div id="new-arm" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="card w-full max-w-md p-6">
        <h2 class="font-display text-lg text-primary">New Arm</h2>
        <form method="POST" action="<?php echo e(route('arms.store')); ?>" class="mt-4 space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="field-label">Key (snake_case)</label>
                <input name="key" required pattern="[a-z0-9_]+" class="field-input" placeholder="e.g. new_media">
            </div>
            <div>
                <label class="field-label">Label</label>
                <input name="label" required class="field-input" placeholder="e.g. New Media Technologies">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal="new-arm" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/arms/index.blade.php ENDPATH**/ ?>