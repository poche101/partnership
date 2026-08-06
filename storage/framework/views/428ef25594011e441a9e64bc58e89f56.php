
<?php $__env->startSection('title', 'Audit Logs'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
    <h1 class="font-display text-xl sm:text-2xl text-primary">Audit Logs</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        <?php if(request()->filled('entity_type') || request()->filled('entity_id')): ?>
            Filtered activity trail.
            <a href="<?php echo e(route('audit.index')); ?>" class="underline">Clear filter</a>
        <?php else: ?>
            System-wide activity trail.
        <?php endif; ?>
    </p>

    <form method="GET" action="<?php echo e(route('audit.index')); ?>" class="mt-4 flex flex-wrap items-end gap-2">
        <?php if(request()->filled('entity_type')): ?>
            <input type="hidden" name="entity_type" value="<?php echo e(request('entity_type')); ?>">
        <?php endif; ?>
        <?php if(request()->filled('entity_id')): ?>
            <input type="hidden" name="entity_id" value="<?php echo e(request('entity_id')); ?>">
        <?php endif; ?>

        <div class="flex flex-1 min-w-[200px] flex-col">
            <label for="q" class="mb-1 text-xs text-muted-foreground">Search</label>
            <input
                type="text"
                name="q"
                id="q"
                value="<?php echo e(request('q')); ?>"
                placeholder="Actor, action, partner, or arm…"
                class="field-input"
            >
        </div>

        <div class="flex flex-col">
            <label for="date_from" class="mb-1 text-xs text-muted-foreground">From</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo e(request('date_from')); ?>"
                class="field-input">
        </div>

        <div class="flex flex-col">
            <label for="date_to" class="mb-1 text-xs text-muted-foreground">To</label>
            <input type="date" name="date_to" id="date_to" value="<?php echo e(request('date_to')); ?>" class="field-input">
        </div>

        <button type="submit" class="btn-primary">Search</button>

        <?php if(request()->filled('q') || request()->filled('date_from') || request()->filled('date_to')): ?>
            <a href="<?php echo e(route('audit.index', request()->only(['entity_type', 'entity_id']))); ?>"
                class="btn-outline">Clear</a>
        <?php endif; ?>
    </form>

    <?php if(request()->filled('q') || request()->filled('date_from') || request()->filled('date_to')): ?>
        <?php
            $resultCount = method_exists($logs, 'total') ? $logs->total() : $logs->count();
        ?>
        <p class="mt-4 text-sm text-muted-foreground">
            <strong class="text-foreground"><?php echo e(number_format($resultCount)); ?></strong>
            <?php echo e(Str::plural('result', $resultCount)); ?>

            <?php if(request()->filled('q')): ?>
                for "<strong class="text-foreground"><?php echo e(request('q')); ?></strong>"
            <?php endif; ?>
            <?php if(request()->filled('date_from') || request()->filled('date_to')): ?>
                between
                <strong class="text-foreground"><?php echo e(request('date_from') ?: 'the start'); ?></strong>
                and
                <strong class="text-foreground"><?php echo e(request('date_to') ?: 'now'); ?></strong>
            <?php endif; ?>
        </p>
    <?php endif; ?>

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
                        <?php
                            $d = $log->details ?? [];
                            $hasGranularSpouse = !empty($d['spouse_first_name']);

                            if ($hasGranularSpouse) {
                                $surname = $d['spouse_last_name'] ?? $d['partner_last_name'] ?? '';
                                $displayName = collect([
                                    $d['partner_title'] ?? null,
                                    $d['partner_first_name'] ?? null,
                                    $d['spouse_title'] ?? null,
                                    $d['spouse_first_name'] ?? null,
                                    $surname ?: null,
                                ])->filter()->implode(', ');
                            } elseif (!empty($d['spouse_name'])) {
                                // Legacy entries: only flat name strings available
                                $displayName = ($d['partner'] ?? 'Unknown partner').' & '.$d['spouse_name'];
                            } else {
                                $displayName = $d['partner'] ?? 'Unknown partner';
                            }
                        ?>
                        <p>
                            Recorded a gift from <strong><?php echo e($displayName); ?></strong>.
                        </p>
                        <?php if(!empty($d['changes'])): ?>
                            <ul class="mt-2 space-y-1 text-muted-foreground">
                                <?php $__currentLoopData = $d['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm => $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <span class="font-medium text-foreground"><?php echo e(\App\Support\Arms::label($arm)); ?>:</span>
                                        <?php echo e(number_format($change['before'], 2)); ?>

                                        <?php if(array_key_exists('added', $change)): ?>
                                            <span class="text-primary">+<?php echo e(number_format($change['added'], 2)); ?></span>
                                        <?php endif; ?>
                                        &rarr; <?php echo e(number_format($change['after'], 2)); ?> ESPEES
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    <?php elseif($log->action === 'giving.updated'): ?>
                        <?php $d = $log->details ?? []; ?>
                        <p>
                            Updated a giving record for <strong><?php echo e($d['partner'] ?? 'Unknown partner'); ?></strong>.
                        </p>
                        <?php if(!empty($d['changes'])): ?>
                            <ul class="mt-2 space-y-1 text-muted-foreground">
                                <?php $__currentLoopData = $d['changes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm => $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <span class="font-medium text-foreground"><?php echo e(\App\Support\Arms::label($arm)); ?>:</span>
                                        <?php echo e(number_format($change['before'], 2)); ?>

                                        &rarr; <?php echo e(number_format($change['after'], 2)); ?> ESPEES
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    <?php elseif($log->action === 'giving.deleted'): ?>
                        <?php $d = $log->details ?? []; ?>
                        <p>
                            Deleted a giving record for <strong><?php echo e($d['partner'] ?? 'Unknown partner'); ?></strong>
                            <?php if(isset($d['total_espees'])): ?>
                                totaling <?php echo e(number_format($d['total_espees'], 2)); ?> ESPEES
                            <?php endif; ?>
                            .
                        </p>
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
                <?php if(request()->filled('q') || request()->filled('date_from') || request()->filled('date_to')): ?>
                    No activity matches your filters.
                <?php else: ?>
                    No activity recorded yet.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($logs->appends(request()->query())->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/audit/index.blade.php ENDPATH**/ ?>