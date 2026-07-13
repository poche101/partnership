<?php $__env->startSection('title', 'Giving Alerts'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-5xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Giving Alerts</h1>
    <p class="mt-1 text-sm text-muted-foreground">Alerts fire automatically when a giving entry meets a configured threshold for an arm.</p>

    <?php if(auth()->user()->isZoneAdmin()): ?>
        <div class="card mt-6 p-6">
            <h2 class="font-display text-lg text-primary">Thresholds</h2>
            <div class="mt-4 space-y-3">
                <?php $__currentLoopData = $arms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $t = $thresholds->get($arm['key']); ?>
                    <form method="POST" action="<?php echo e(route('alerts.thresholds.save')); ?>" class="flex flex-wrap items-center gap-3">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="arm_key" value="<?php echo e($arm['key']); ?>">
                        <span class="w-48 text-sm"><?php echo e($arm['label']); ?></span>
                        <input type="number" step="0.01" min="0.01" name="threshold_espees" value="<?php echo e($t?->threshold_espees ?? ''); ?>" placeholder="Threshold" class="field-input max-w-[160px]">
                        <label class="flex items-center gap-1 text-xs text-muted-foreground">
                            <input type="checkbox" name="enabled" value="1" <?php echo e($t?->enabled ?? true ? 'checked' : ''); ?>> Enabled
                        </label>
                        <button type="submit" class="btn-outline">Save</button>
                    </form>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Date</th><th>Partner</th><th>Church</th><th>Arm</th><th>Amount</th><th>Threshold</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($a->created_at->format('M j, Y g:ia')); ?></td>
                        <td class="font-medium"><?php echo e($a->partner?->fullName()); ?></td>
                        <td><?php echo e($a->church?->name); ?></td>
                        <td><?php echo e(\App\Support\Arms::label($a->arm_key)); ?></td>
                        <td class="font-mono"><?php echo e(number_format($a->amount_espees, 2)); ?></td>
                        <td class="font-mono"><?php echo e(number_format($a->threshold_espees, 2)); ?></td>
                        <td>
                            <?php if($a->acknowledged): ?>
                                <span class="badge">Acknowledged</span>
                            <?php else: ?>
                                <span class="badge bg-accent/20 text-accent">New</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (! ($a->acknowledged)): ?>
                                <form method="POST" action="<?php echo e(route('alerts.acknowledge', $a)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button class="btn-ghost text-xs">Acknowledge</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="py-6 text-center text-muted-foreground">No alerts yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\Downloads\partnership-tracker-laravel\pt-laravel\resources\views/alerts/index.blade.php ENDPATH**/ ?>