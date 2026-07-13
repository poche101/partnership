<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-6xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Dashboard</h1>
    <p class="mt-1 text-sm text-muted-foreground">Overview of partnership giving within your scope.</p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Total Giving</div>
            <div class="mt-2 font-display text-2xl text-primary"><?php echo e(number_format($total, 2)); ?> <span class="text-sm font-sans text-muted-foreground">ESPEES</span></div>
        </div>
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Partners</div>
            <div class="mt-2 font-display text-2xl text-primary"><?php echo e(number_format($countPartners)); ?></div>
        </div>
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Churches</div>
            <div class="mt-2 font-display text-2xl text-primary"><?php echo e(number_format($countChurches)); ?></div>
        </div>
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Group Churches</div>
            <div class="mt-2 font-display text-2xl text-primary"><?php echo e(number_format($countGroups)); ?></div>
        </div>
    </div>

    <?php if(count($series)): ?>
    <div class="card mt-6 p-5">
        <h2 class="font-display text-lg text-primary">Giving trend (last 30 days)</h2>
        <canvas id="trendChart" class="mt-4" height="90"></canvas>
    </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h2 class="font-display text-lg text-primary">Giving by arm</h2>
            <div class="mt-4 space-y-3">
                <?php $__currentLoopData = $arms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $amt = $armTotals[$arm['key']] ?? 0; ?>
                    <div>
                        <div class="flex justify-between text-sm"><span><?php echo e($arm['label']); ?></span><span class="font-mono"><?php echo e(number_format($amt, 2)); ?></span></div>
                        <div class="mt-1 h-1.5 w-full rounded-full bg-muted">
                            <div class="h-1.5 rounded-full bg-accent" style="width: <?php echo e($total > 0 ? min(100, round($amt / $total * 100)) : 0); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-display text-lg text-primary">Top churches</h2>
            <div class="mt-4 space-y-2">
                <?php $__empty_1 = true; $__currentLoopData = $top; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between border-b border-border py-2 text-sm last:border-0">
                        <span><?php echo e($c['name']); ?></span>
                        <span class="font-mono"><?php echo e(number_format($c['total'], 2)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-muted-foreground">No giving recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(count($series)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('trendChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(collect($series)->pluck('date'), 15, 512) ?>,
            datasets: [{
                label: 'ESPEES',
                data: <?php echo json_encode(collect($series)->pluck('total'), 15, 512) ?>,
                borderColor: '#B98D4C',
                backgroundColor: 'rgba(185,141,76,0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 0,
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
        },
    });
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\Downloads\partnership-tracker-laravel\pt-laravel\resources\views/dashboard/index.blade.php ENDPATH**/ ?>