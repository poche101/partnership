
<?php $__env->startSection('title', 'Givings'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl text-primary">Givings</h1>
            <p class="mt-1 text-sm text-muted-foreground">Showing: <strong><?php echo e($armLabel); ?></strong> &middot; Total: <span class="font-mono"><?php echo e(number_format($totalShown, 2)); ?></span> ESPEES</p>
        </div>
        <button data-open-modal="new-giving" class="btn-primary">+ Record Giving</button>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <a href="<?php echo e(route('givings.index')); ?>" class="badge <?php echo e($armFilter === 'all' ? 'bg-accent text-accent-foreground' : ''); ?>">All Arms</a>
        <?php $__currentLoopData = $arms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('givings.index', ['arm' => $arm['key']])); ?>" class="badge <?php echo e($armFilter === $arm['key'] ? 'bg-accent text-accent-foreground' : ''); ?>"><?php echo e($arm['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="registry mt-6">
        <table class="registry-table">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th>Category</th>
                    <th>Contact</th>
                    <th>KingsChat</th>
                    <th>Spouse Contact</th>
                    <th>Last Giving</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $view; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $partner = $row['entry']->partner;
                        $hasSpouse = $partner && filled($partner->spouse_first_name);

                        if ($hasSpouse) {
                            $left = trim(($partner->title ?? '').' '.$partner->first_name);
                            $right = trim(($partner->spouse_title ?? '').' '.$partner->spouse_first_name);
                            $surname = $partner->spouse_last_name ?: $partner->last_name;
                            $displayName = trim("{$left} & {$right} {$surname}");
                        } else {
                            $displayName = $partner?->fullName() ?? '—';
                        }

                        $initials = $partner
                            ? strtoupper(mb_substr($partner->first_name ?? '?', 0, 1).mb_substr($partner->last_name ?? '', 0, 1))
                            : '?';
                        $palette = ['#3B5A73', '#7A5C3E', '#4E6E58', '#6B5B95', '#8A5A44', '#3E6B6B'];
                        $swatch = $palette[crc32(($partner->id ?? 0).($partner->first_name ?? '')) % count($palette)];

                        $spouseContactLines = collect([
                            $partner?->spouse_kingschat ? '@'.$partner->spouse_kingschat : null,
                            $partner?->spouse_phone,
                            $partner?->spouse_email,
                        ])->filter();
                    ?>
                    <tr>
                        <td>
                            <div class="registry-partner">
                                <span class="registry-avatar" style="background: <?php echo e($swatch); ?>"><?php echo e($initials); ?></span>
                                <div class="registry-partner-text">
                                    <div class="registry-name"><?php echo e($displayName); ?></div>
                                    <div class="registry-church"><?php echo e($row['entry']->church?->name ?? '—'); ?></div>
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
                        <td>
                            <div class="registry-stack">
                                <span><?php echo e($partner?->phone ?: '—'); ?></span>
                                <span class="registry-muted"><?php echo e($partner?->email ?: '—'); ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if($partner?->kingschat_username): ?>
                                <span class="registry-handle"><?php echo e('@'.$partner->kingschat_username); ?></span>
                            <?php else: ?>
                                <span class="registry-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($spouseContactLines->isNotEmpty()): ?>
                                <div class="registry-stack">
                                    <?php $__currentLoopData = $spouseContactLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span><?php echo e($line); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <span class="registry-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($row['entry']->recorded_at?->format('M j, Y')); ?></td>
                        <td class="font-mono"><?php echo e(number_format($row['amount'], 2)); ?></td>
                        <td>
                            <a href="<?php echo e(route('audit.index', ['entity_type' => \App\Models\PartnershipEntry::class, 'entity_id' => $row['entry']->id])); ?>"
                               class="text-xs text-muted-foreground underline">
                                History
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="registry-empty">No givings recorded for this filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="new-giving" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 overflow-y-auto">
    <div class="card my-8 w-full max-w-2xl p-6">
        <h2 class="font-display text-lg text-primary">Record Giving</h2>
        <form method="POST" action="<?php echo e(route('givings.store')); ?>" class="mt-4 space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="field-label">Partner</label>
                <select name="partner_id" id="partner-select" required class="field-input">
                    <option value="">Select partner…</option>
                    <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" data-spouse="<?php echo e($p->spouse_name); ?>"><?php echo e($p->fullName()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="include_spouse" value="1" id="include-spouse-toggle">
                    Include spouse
                </label>
                <input
                    type="text"
                    name="spouse_name"
                    id="spouse-name-input"
                    placeholder="Spouse's name"
                    class="field-input mt-2 hidden"
                >
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <?php $__currentLoopData = $arms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <label class="field-label"><?php echo e($arm['label']); ?></label>
                        <input type="number" step="0.01" min="0" name="<?php echo e($arm['key']); ?>" value="0" class="field-input">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div>
                <label class="field-label">Note</label>
                <textarea name="note" rows="2" class="field-input"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal="new-giving" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Save Giving</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('include-spouse-toggle');
    const input = document.getElementById('spouse-name-input');
    const partnerSelect = document.getElementById('partner-select');

    if (toggle && input && partnerSelect) {
        toggle.addEventListener('change', () => {
            input.classList.toggle('hidden', !toggle.checked);
            if (!toggle.checked) input.value = '';
        });

        partnerSelect.addEventListener('change', () => {
            const selected = partnerSelect.options[partnerSelect.selectedIndex];
            const spouse = selected?.dataset.spouse || '';
            if (spouse) {
                toggle.checked = true;
                input.classList.remove('hidden');
                input.value = spouse;
            } else {
                toggle.checked = false;
                input.classList.add('hidden');
                input.value = '';
            }
        });
    }
});
</script>

<style>
    .registry {
        border: 1px solid var(--border, #E5E1D8);
        border-radius: 8px;
        overflow: hidden;
        background: var(--card, #fff);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .registry-table {
        width: 100%;
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
        padding: 0.85rem 1.1rem;
        border-bottom: 2px solid var(--border, #E5E1D8);
        background: var(--muted, #FAFAF7);
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
    .registry-church {
        font-size: 0.75rem;
        color: var(--muted-foreground, #8A8578);
        margin-top: 0.1rem;
    }

    .registry-stack {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        line-height: 1.4;
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/givings/index.blade.php ENDPATH**/ ?>