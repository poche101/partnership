
<?php $__env->startSection('title', 'Givings'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-xl sm:text-2xl text-primary">Givings</h1>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <span class="text-sm text-muted-foreground">Showing: <strong><?php echo e($armLabel); ?></strong></span>
                <span class="givings-total-badge">
                    <span class="givings-total-label">Total</span>
                    <span class="givings-total-amount font-mono"><?php echo e(number_format($totalShown, 2)); ?></span>
                    <span class="givings-total-currency">ESPEES</span>
                </span>
            </div>
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
        <div class="registry-scroll">
            <table class="registry-table">
                <thead>
                    <tr class="registry-group-row">
                        <th colspan="5" class="registry-group-header registry-group-partner">Partner Details</th>
                        <th colspan="5" class="registry-group-header registry-group-spouse">Spouse Details</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Last Giving</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Amount<br><span class="registry-group-subtext">(ESPEES)</span></th>
                        <th rowspan="2" class="registry-group-header registry-group-actions">Actions</th>
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
                    <?php $__empty_1 = true; $__currentLoopData = $view; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $entry = $row['entry'];
                            $partner = $entry->partner;
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
                                        <div class="registry-church"><?php echo e($entry->church?->name ?? '—'); ?></div>
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

                            
                            <td><?php echo e($entry->recorded_at?->format('M j, Y')); ?></td>
                            <td class="font-mono"><?php echo e(number_format($row['amount'], 2)); ?></td>
                            <td>
                                <div class="registry-actions">
                                    <a href="<?php echo e(route('audit.index', ['entity_type' => \App\Models\PartnershipEntry::class, 'entity_id' => $entry->id])); ?>"
                                       class="text-xs text-muted-foreground underline">
                                        History
                                    </a>
                                    <button type="button" data-open-modal="edit-giving-<?php echo e($entry->id); ?>"
                                        class="btn-icon-only" title="Edit giving record">
                                        <svg viewBox="0 0 20 20" fill="none" class="btn-svg">
                                            <path d="M13.5 3.5l3 3L6 17H3v-3L13.5 3.5z" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <button type="button" data-open-modal="delete-giving-<?php echo e($entry->id); ?>"
                                        class="btn-icon-only btn-icon-danger" title="Delete giving record">
                                        <svg viewBox="0 0 20 20" fill="none" class="btn-svg">
                                            <path
                                                d="M4 6h12M8 6V4.5A1.5 1.5 0 019.5 3h1A1.5 1.5 0 0112 4.5V6m-6.5 0l.6 9.4a1.5 1.5 0 001.5 1.6h3.8a1.5 1.5 0 001.5-1.6L14.5 6"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="13" class="registry-empty">No givings recorded for this filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php $__currentLoopData = $view; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $entry = $row['entry'];
        $partner = $entry->partner;
        $partnerName = $partner
            ? trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name)
            : 'this partner';
    ?>

    
    <div id="edit-giving-<?php echo e($entry->id); ?>" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="card w-full max-w-2xl p-4 sm:p-6">
                <h2 class="font-display text-lg text-primary">Edit Giving Record</h2>
                <p class="mt-1 text-sm text-muted-foreground"><?php echo e($partnerName); ?> &middot; <?php echo e($entry->church?->name ?? '—'); ?></p>
                <form method="POST" action="<?php echo e(route('givings.update', $entry)); ?>" class="mt-4 space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <?php $__currentLoopData = $arms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <label class="field-label"><?php echo e($arm['label']); ?></label>
                                <input type="number" step="0.01" min="0" name="<?php echo e($arm['key']); ?>"
                                    value="<?php echo e($entry->{$arm['key']}); ?>" class="field-input">
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div>
                        <label class="field-label">Note</label>
                        <textarea name="note" rows="2" class="field-input"><?php echo e($entry->note); ?></textarea>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" data-close-modal="edit-giving-<?php echo e($entry->id); ?>"
                            class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div id="delete-giving-<?php echo e($entry->id); ?>" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="card w-full max-w-md p-4 sm:p-6">
                <h2 class="font-display text-lg text-primary">Delete Giving Record</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Are you sure you want to delete this giving record for <strong><?php echo e($partnerName); ?></strong>
                    (<?php echo e(number_format($entry->total_espees, 2)); ?> ESPEES)? This action cannot be undone.
                </p>
                <form method="POST" action="<?php echo e(route('givings.destroy', $entry)); ?>" class="mt-6 flex flex-wrap justify-end gap-2">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="button" data-close-modal="delete-giving-<?php echo e($entry->id); ?>"
                        class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div id="new-giving" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="card w-full max-w-2xl p-4 sm:p-6">
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
                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button type="button" data-close-modal="new-giving" class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary">Save Giving</button>
                </div>
            </form>
        </div>
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
        min-width: 1420px;
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
    .registry-group-subtext {
        font-size: 0.6rem;
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
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
    .registry-church {
        font-size: 0.75rem;
        color: var(--muted-foreground, #8A8578);
        margin-top: 0.1rem;
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

    .registry-actions {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .btn-icon-only {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        border: 1px solid var(--border, #E5E1D8);
        background: var(--card, #fff);
        color: var(--muted-foreground, #7A756B);
        cursor: pointer;
        transition: background-color 0.12s ease, color 0.12s ease, border-color 0.12s ease;
    }
    .btn-icon-only:hover {
        background: var(--muted, #FAFAF7);
        color: var(--primary, #3B5A73);
        border-color: var(--primary, #3B5A73);
    }
    .btn-icon-danger:hover {
        color: #B3261E;
        border-color: #B3261E;
        background: #FBEAE9;
    }
    .btn-icon-only .btn-svg {
        width: 15px;
        height: 15px;
    }

    .btn-danger {
        background: #B3261E;
        border-color: #B3261E;
    }
    .btn-danger:hover {
        background: #922019;
        border-color: #922019;
    }

    /* Header total-given badge, explicitly labelled in ESPEES */
    .givings-total-badge {
        display: inline-flex;
        align-items: baseline;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: var(--muted, #F3F2ED);
        border: 1px solid var(--border, #E5E1D8);
    }
    .givings-total-label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--muted-foreground, #7A756B);
    }
    .givings-total-amount {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--primary, #3B5A73);
    }
    .givings-total-currency {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: var(--muted-foreground, #7A756B);
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/givings/index.blade.php ENDPATH**/ ?>