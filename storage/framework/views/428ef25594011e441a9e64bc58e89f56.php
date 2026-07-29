
<?php $__env->startSection('title', 'Audit Logs'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Audit Logs</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        <?php if(request()->filled('entity_type') || request()->filled('entity_id')): ?>
            Filtered activity trail.
            <a href="<?php echo e(route('audit.index')); ?>" class="underline">Clear filter</a>
        <?php else: ?>
            System-wide activity trail.
        <?php endif; ?>
    </p>

    <form method="GET" action="<?php echo e(route('audit.index')); ?>" class="mt-4 flex gap-2">
        <?php if(request()->filled('entity_type')): ?>
            <input type="hidden" name="entity_type" value="<?php echo e(request('entity_type')); ?>">
        <?php endif; ?>
        <?php if(request()->filled('entity_id')): ?>
            <input type="hidden" name="entity_id" value="<?php echo e(request('entity_id')); ?>">
        <?php endif; ?>
        <input
            type="text"
            name="q"
            value="<?php echo e(request('q')); ?>"
            placeholder="Search by actor, action, or partner…"
            class="field-input flex-1"
        >
        <button type="submit" class="btn-primary">Search</button>
        <?php if(request()->filled('q')): ?>
            <a href="<?php echo e(route('audit.index', request()->except('q'))); ?>" class="btn-outline">Clear</a>
        <?php endif; ?>
    </form>

    <div class="mt-6 space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="badge"><?php echo e(str(str_replace('.', ' ', $log->action))->headline()); ?></span>
                        <span class="text-sm text-muted-foreground"><?php echo e($log->actor_email ?? 'System'); ?></span>
                    </div>
                    <span class="text-xs text-muted-foreground whitespace-nowrap">
                        <?php echo e($log->created_at->format('M j, Y g:ia')); ?>

                    </span>
                </div>

                <div class="mt-2 text-sm">
                    <?php if($log->action === 'giving.recorded'): ?>
                        <p>
                            Recorded a gift from <strong><?php echo e($log->details['partner'] ?? 'Unknown partner'); ?></strong>.
                        </p>
                        <?php if(!empty($log->details['changes'])): ?>
                            <ul class="mt-2 space-y-1 text-muted-foreground">
                                <?php $__currentLoopData = $log->details['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm => $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <span class="font-medium text-foreground"><?php echo e(\App\Support\Arms::label($arm)); ?>:</span>
                                        <?php echo e(number_format($change['before'], 2)); ?>

                                        <span class="text-primary">+<?php echo e(number_format($change['added'], 2)); ?></span>
                                        &rarr; <?php echo e(number_format($change['after'], 2)); ?> ESPEES
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted-foreground">
                            <?php echo e($log->entity_type ? class_basename($log->entity_type) : 'Entity'); ?>

                            <?php if($log->entity_id): ?> #<?php echo e($log->entity_id); ?> <?php endif; ?>
                        </p>
                        <?php if(!empty($log->details)): ?>
                            <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-muted-foreground sm:grid-cols-3">
                                <?php $__currentLoopData = $log->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!is_array($value)): ?>
                                        <div>
                                            <dt class="font-medium text-foreground"><?php echo e(str($key)->headline()); ?></dt>
                                            <dd><?php echo e($value); ?></dd>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </dl>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card p-6 text-center text-muted-foreground">
                <?php if(request()->filled('q')): ?>
                    No activity matches "<?php echo e(request('q')); ?>".
                <?php else: ?>
                    No activity recorded yet.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($logs->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/audit/index.blade.php ENDPATH**/ ?>