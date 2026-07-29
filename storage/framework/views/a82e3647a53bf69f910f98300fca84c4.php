<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partnership Tracker</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <header class="border-b border-border">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <span class="font-display text-xl text-primary">Partnership Tracker</span>
            <a href="<?php echo e(route('login')); ?>" class="btn-primary">Sign in</a>
        </div>
    </header>

    <section class="mx-auto max-w-4xl px-6 py-24 text-center">
        <p class="font-mono text-xs uppercase tracking-widest text-accent">Church partnership &amp; giving</p>
        <h1 class="font-display mt-4 text-4xl leading-tight text-primary sm:text-5xl">
            Track partner giving across every arm of ministry, in one place.
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-muted-foreground">
            Partnership Tracker helps zone, group and church administrators record partner details,
            log givings across ministry arms, watch for threshold alerts, and generate giving statements —
            with role-scoped access for every level of the organisation.
        </p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="<?php echo e(route('login')); ?>" class="btn-primary">Sign in to your dashboard</a>
        </div>
    </section>

    <section class="mx-auto grid max-w-5xl grid-cols-1 gap-6 px-6 pb-24 sm:grid-cols-3">
        <div class="card p-6">
            <h3 class="font-display text-lg text-primary">Role-based access</h3>
            <p class="mt-2 text-sm text-muted-foreground">Zone, group and church admins each see only what's relevant to their scope.</p>
        </div>
        <div class="card p-6">
            <h3 class="font-display text-lg text-primary">Giving across 13 arms</h3>
            <p class="mt-2 text-sm text-muted-foreground">Record partnership giving per ministry arm and watch totals roll up automatically.</p>
        </div>
        <div class="card p-6">
            <h3 class="font-display text-lg text-primary">Statements &amp; alerts</h3>
            <p class="mt-2 text-sm text-muted-foreground">Generate partner giving statements and get notified when thresholds are met.</p>
        </div>
    </section>
</body>
</html>
<?php /**PATH C:\Users\kings\partnership\partnership\resources\views/landing.blade.php ENDPATH**/ ?>