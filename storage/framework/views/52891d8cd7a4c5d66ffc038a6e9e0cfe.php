
<?php $__env->startSection('title', 'Partners'); ?>
<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-6xl px-6 py-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl text-primary">Partners</h1>
                <p class="mt-1 text-sm text-muted-foreground">All partners visible within your scope.</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('partners.export')); ?>" class="btn-outline btn-icon">
                    <svg viewBox="0 0 20 20" fill="none" class="btn-svg">
                        <path d="M10 3v10m0 0l-4-4m4 4l4-4M4 16h12" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Download Excel
                </a>
                <button data-open-modal="new-partner" class="btn-primary">+ New Partner</button>
            </div>
        </div>

        <form method="GET" class="mt-6 flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Search name, email, KingsChat..."
                class="field-input max-w-sm">
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->isZoneAdmin()): ?>
                    <label class="flex items-center gap-2 text-sm text-muted-foreground">
                        <input type="checkbox" name="ai" value="1" <?php echo e($aiMode ? 'checked' : ''); ?>> AI semantic search
                    </label>
                <?php endif; ?>
            <?php endif; ?>
            <button type="submit" class="btn-outline">Search</button>
        </form>

        <div class="registry mt-6">
            <table class="registry-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Category</th>
                        <th>Contact</th>
                        <th>KingsChat</th>
                        <th>Spouse Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $hasSpouse = filled($p->spouse_first_name);

                            if ($hasSpouse) {
                                $left = trim(($p->title ?? '') . ' ' . $p->first_name);
                                $right = trim(($p->spouse_title ?? '') . ' ' . $p->spouse_first_name);
                                $surname = $p->spouse_last_name ?: $p->last_name;
                                $displayName = trim("{$left} & {$right} {$surname}");
                            } else {
                                $displayName = $p->fullName();
                            }

                            $initials = strtoupper(
                                mb_substr($p->first_name ?? '?', 0, 1) . mb_substr($p->last_name ?? '', 0, 1),
                            );
                            $palette = ['#3B5A73', '#7A5C3E', '#4E6E58', '#6B5B95', '#8A5A44', '#3E6B6B'];
                            $swatch = $palette[crc32($p->id . $p->first_name) % count($palette)];

                            $spouseContactLines = collect([
                                $p->spouse_kingschat ? '@' . $p->spouse_kingschat : null,
                                $p->spouse_phone,
                                $p->spouse_email,
                            ])->filter();
                        ?>
                        <tr>
                            <td>
                                <div class="registry-partner">
                                    <span class="registry-avatar"
                                        style="background: <?php echo e($swatch); ?>"><?php echo e($initials); ?></span>
                                    <div class="registry-partner-text">
                                        <div class="registry-name"><?php echo e($displayName); ?></div>
                                        <div class="registry-church"><?php echo e($p->church?->name ?? '—'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($p->delegate_category): ?>
                                    <span class="badge"><?php echo e($p->delegate_category); ?></span>
                                <?php else: ?>
                                    <span class="registry-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="registry-stack">
                                    <span><?php echo e($p->phone ?: '—'); ?></span>
                                    <span class="registry-muted"><?php echo e($p->email ?: '—'); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if($p->kingschat_username): ?>
                                    <span class="registry-handle"><?php echo e('@' . $p->kingschat_username); ?></span>
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
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="registry-empty">No partners found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="new-partner" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 overflow-y-auto">
        <div class="card my-8 w-full max-w-2xl p-6">
            <h2 class="font-display text-lg text-primary">New Partner</h2>
            <form method="POST" action="<?php echo e(route('partners.store')); ?>" class="mt-4 space-y-4">
                <?php echo csrf_field(); ?>
                <?php if($churches->count() > 1 || !auth()->user()->isChurchAdmin()): ?>
                    <div>
                        <label class="field-label">Church</label>
                        <select name="church_id" required class="field-input">
                            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="grid grid-cols-3 gap-3">
                    <div><label class="field-label">Title</label><input name="title" class="field-input"></div>
                    <div class="col-span-1"><label class="field-label">First name</label><input name="first_name" required
                            class="field-input"></div>
                    <div><label class="field-label">Last name</label><input name="last_name" class="field-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Delegate category</label>
                        <select name="delegate_category" class="field-input">
                            <option value="">—</option>
                            <?php $__currentLoopData = $delegateCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dc); ?>"><?php echo e($dc); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="field-label">KingsChat username</label><input name="kingschat_username"
                            class="field-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="field-label">Phone</label><input name="phone" class="field-input"></div>
                    <div><label class="field-label">Email</label><input type="email" name="email" class="field-input">
                    </div>
                </div>
                <details class="rounded-md border border-border p-3">
                    <summary class="cursor-pointer text-sm font-medium text-foreground">Spouse details (optional)</summary>
                    <div class="mt-3 grid grid-cols-3 gap-3">
                        <div><label class="field-label">Title</label><input name="spouse_title" class="field-input"></div>
                        <div><label class="field-label">First name</label><input name="spouse_first_name"
                                class="field-input"></div>
                        <div><label class="field-label">Surname</label><input name="spouse_last_name" class="field-input">
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div><label class="field-label">KingsChat</label><input name="spouse_kingschat"
                                class="field-input"></div>
                        <div><label class="field-label">Phone</label><input name="spouse_phone" class="field-input">
                        </div>
                    </div>
                    <div class="mt-3"><label class="field-label">Email</label><input type="email"
                            name="spouse_email" class="field-input"></div>
                </details>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-close-modal="new-partner" class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary">Save Partner</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .registry {
            border: 1px solid var(--border, #E5E1D8);
            border-radius: 8px;
            overflow: hidden;
            background: var(--card, #fff);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
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

        .registry-table tbody tr:last-child {
            border-bottom: none;
        }

        .registry-table tbody tr:hover {
            background: var(--muted, #FAFAF7);
        }

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

        .registry-partner-text {
            min-width: 0;
        }

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

        .registry-muted {
            color: var(--muted-foreground, #B3AEA1);
        }

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/partners/index.blade.php ENDPATH**/ ?>