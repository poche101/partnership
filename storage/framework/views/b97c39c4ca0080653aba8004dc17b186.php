
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

    <div class="table-shell card mt-4 overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Church</th>
                    <th>Category</th>
                    <th>KingsChat</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Last Giving</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $view; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $partner = $row['entry']->partner;
                        $spouseCompact = trim(($partner?->spouse_first_name ?? '').' '.($partner?->spouse_last_name ?? ''));
                        $spouseTooltip = collect([$partner?->spouse_delegate_category, $partner?->spouse_kingschat, $partner?->spouse_phone, $partner?->spouse_email])
                            ->filter()->implode(' · ');
                    ?>
                    <tr>
                        <td class="font-medium">
                            <?php echo e($partner?->fullName()); ?>

                            <?php if($spouseCompact): ?>
                                <span class="text-muted-foreground font-normal" title="<?php echo e($spouseTooltip); ?>">&amp; <?php echo e($spouseCompact); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($row['entry']->church?->name); ?></td>
                        <td><?php echo e($partner?->delegate_category ?: '—'); ?></td>
                        <td><?php echo e($partner?->kingschat_username ?: '—'); ?></td>
                        <td><?php echo e($partner?->phone ?: '—'); ?></td>
                        <td><?php echo e($partner?->email ?: '—'); ?></td>
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
                    <tr><td colspan="9" class="py-6 text-center text-muted-foreground">No givings recorded for this filter.</td></tr>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/givings/index.blade.php ENDPATH**/ ?>