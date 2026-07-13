<?php $__env->startSection('title', 'Search'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Semantic Search</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        Search partners using natural language — e.g. "partners in Lagos who gave to Rhapsody last month".
        Falls back to plain text search if no AI key is configured.
    </p>

    <form method="POST" action="<?php echo e(route('search.run')); ?>" class="mt-6 flex gap-2">
        <?php echo csrf_field(); ?>
        <input type="text" name="query" required value="<?php echo e($query); ?>" placeholder="Describe who you're looking for…" class="field-input">
        <button type="submit" class="btn-primary">Search</button>
    </form>

    <?php if($ran): ?>
        <div class="table-shell card mt-6 overflow-x-auto">
            <table>
                <thead><tr><th>Name</th><th>Church</th><th>Group</th><th>Email</th><th>Phone</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-medium"><?php echo e($p->fullName()); ?></td>
                            <td><?php echo e($p->church?->name); ?></td>
                            <td><?php echo e($p->church?->groupChurch?->name); ?></td>
                            <td><?php echo e($p->email ?: '—'); ?></td>
                            <td><?php echo e($p->phone ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="py-6 text-center text-muted-foreground">No matches found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\Downloads\partnership-tracker-laravel\pt-laravel\resources\views/search/index.blade.php ENDPATH**/ ?>