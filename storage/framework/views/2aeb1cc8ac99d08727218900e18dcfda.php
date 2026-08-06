
<?php $__env->startSection('title', 'Giving Alerts'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-xl sm:text-2xl text-primary">Giving Alerts</h1>
            <p class="mt-1 text-sm text-muted-foreground">Alerts fire automatically when a giving entry meets a configured threshold for an arm.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('alerts.export')); ?>" class="btn-outline btn-icon">
                <svg viewBox="0 0 20 20" fill="none" class="btn-svg"><path d="M10 3v10m0 0l-4-4m4 4l4-4M4 16h12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Download Excel
            </a>
        </div>
    </div>

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

    <div class="registry mt-6">
        <div class="registry-scroll">
            <table class="registry-table">
                <thead>
                    <tr class="registry-group-row">
                        <th colspan="5" class="registry-group-header registry-group-partner">Partner Details</th>
                        <th colspan="5" class="registry-group-header registry-group-spouse">Spouse Details</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Date</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Church</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Arm</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Amount</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Threshold</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Status</th>
                        <th rowspan="2" class="registry-group-header registry-group-actions"></th>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>KingsChat</th>
                        <th class="registry-divider">Name</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>KingsChat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $partner = $a->partner;
                            $hasSpouse = $partner && filled($partner->spouse_first_name);

                            $partnerName = $partner
                                ? trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name)
                                : '—';

                            $spouseName = $hasSpouse
                                ? trim(
                                    ($partner->spouse_title ?? '').' '.
                                    $partner->spouse_first_name.' '.
                                    ($partner->spouse_last_name ?: $partner->last_name)
                                )
                                : null;

                            $initials = $partner
                                ? strtoupper(mb_substr($partner->first_name ?? '?', 0, 1).mb_substr($partner->last_name ?? '', 0, 1))
                                : '?';
                            $palette = ['#3B5A73', '#7A5C3E', '#4E6E58', '#6B5B95', '#8A5A44', '#3E6B6B'];
                            $swatch = $palette[crc32(($partner->id ?? 0).($partner->first_name ?? '')) % count($palette)];
                        ?>
                        <tr>
                            
                            <td>
                                <div class="registry-partner">
                                    <span class="registry-avatar" style="background: <?php echo e($swatch); ?>"><?php echo e($initials); ?></span>
                                    <div class="registry-partner-text">
                                        <div class="registry-name"><?php echo e($partnerName); ?></div>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <?php if($partner?->delegate_category): ?>
                                    <span class="badge"><?php echo e($partner->delegate_category); ?></span>
                                <?php else: ?>
                                    <span class="registry-muted">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo e($partner?->phone ?: '—'); ?></td>
                            
                            <td><?php echo e($partner?->email ?: '—'); ?></td>
                            
                            <td>
                                <?php if($partner?->kingschat_username): ?>
                                    <span class="registry-handle"><?php echo e('@'.$partner->kingschat_username); ?></span>
                                <?php else: ?>
                                    <span class="registry-muted">—</span>
                                <?php endif; ?>
                            </td>

                            
                            <td class="registry-divider">
                                <?php if($hasSpouse): ?>
                                    <div class="registry-name"><?php echo e($spouseName); ?></div>
                                <?php else: ?>
                                    <span class="registry-muted">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if($hasSpouse && !empty($partner->spouse_delegate_category)): ?>
                                    <span class="badge"><?php echo e($partner->spouse_delegate_category); ?></span>
                                <?php else: ?>
                                    <span class="registry-muted">—</span>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo e($hasSpouse && $partner->spouse_phone ? $partner->spouse_phone : '—'); ?></td>
                            
                            <td><?php echo e($hasSpouse && $partner->spouse_email ? $partner->spouse_email : '—'); ?></td>
                            
                            <td>
                                <?php if($hasSpouse && $partner->spouse_kingschat): ?>
                                    <span class="registry-handle"><?php echo e('@'.$partner->spouse_kingschat); ?></span>
                                <?php else: ?>
                                    <span class="registry-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td><?php echo e($a->created_at->format('M j, Y g:ia')); ?></td>
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
                        <tr><td colspan="17" class="registry-empty">No alerts yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .registry {
        border: 1px solid var(--border, #E5E1D8);
        border-radius: 8px;
        overflow: hidden;
        background: var(--card, #fff);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .registry-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .registry-scroll::-webkit-scrollbar { height: 8px; }
    .registry-scroll::-webkit-scrollbar-thumb {
        background: var(--border, #E5E1D8);
        border-radius: 999px;
    }

    .registry-table {
        width: 100%;
        min-width: 1500px;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .registry-table thead th {
        text-align: left;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--muted-foreground, #7A756B);
        padding: 0.7rem 1.1rem;
        border-bottom: 2px solid var(--border, #E5E1D8);
        background: var(--muted, #FAFAF7);
        white-space: nowrap;
    }

    .registry-group-header {
        text-align: center;
        font-size: 0.72rem;
        padding: 0.6rem 1.1rem;
        border-bottom: 1px solid var(--border, #E5E1D8);
    }
    .registry-group-partner {
        background: var(--muted, #F3F2ED);
        color: var(--primary, #3B5A73);
    }
    .registry-group-spouse {
        background: #EFE9E0;
        color: #7A5C3E;
    }
    .registry-group-giving {
        background: var(--muted, #F3F2ED);
        color: var(--primary, #3B5A73);
    }
    .registry-group-actions {
        background: var(--muted, #F3F2ED);
        color: var(--muted-foreground, #7A756B);
    }

    .registry-table tbody tr {
        border-bottom: 1px solid var(--border, #EEEBE3);
        transition: background-color 0.12s ease;
    }
    .registry-table tbody tr:last-child { border-bottom: none; }
    .registry-table tbody tr:hover { background: var(--muted, #FAFAF7); }
    .registry-table td {
        padding: 0.85rem 1.1rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Visual separation between the Partner block and the Spouse block */
    .registry-divider {
        border-left: 2px solid var(--border, #E5E1D8);
    }

    .registry-partner {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .registry-avatar {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .registry-partner-text { min-width: 0; }
    .registry-name {
        font-weight: 500;
        color: var(--foreground, #1F1B16);
        line-height: 1.3;
    }

    .registry-muted { color: var(--muted-foreground, #B3AEA1); }
    .registry-handle {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.8rem;
        color: var(--primary, #3B5A73);
    }
    .registry-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--muted-foreground, #B3AEA1);
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/alerts/index.blade.php ENDPATH**/ ?>