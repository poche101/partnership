<?php $__env->startSection('title', 'Audit Logs'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-6xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Audit Logs</h1>
    <p class="mt-1 text-sm text-muted-foreground">System-wide activity trail (latest 500 events).</p>

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="whitespace-nowrap"><?php echo e($log->created_at->format('M j, Y g:ia')); ?></td>
                        <td><?php echo e($log->actor_email ?? '—'); ?></td>
                        <td><span class="badge"><?php echo e($log->action); ?></span></td>
                        <td><?php echo e($log->entity_type); ?><?php if($log->entity_id): ?> #<?php echo e($log->entity_id); ?><?php endif; ?></td>
                        <td class="max-w-xs truncate font-mono text-xs" title="<?php echo e(json_encode($log->details)); ?>"><?php echo e(json_encode($log->details)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="py-6 text-center text-muted-foreground">No activity recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\Downloads\partnership-tracker-laravel\pt-laravel\resources\views/audit/index.blade.php ENDPATH**/ ?>