<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Partnership Tracker'); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-background text-foreground antialiased">
    <div class="flex min-h-screen w-full">
        <aside class="hidden w-64 flex-col bg-sidebar text-sidebar-foreground md:flex">
            <div class="flex items-center gap-2 px-5 py-5">
                <div class="grid h-9 w-9 place-items-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>
                </div>
                <div>
                    <div class="text-sm font-semibold">Partnership</div>
                    <div class="text-xs text-sidebar-foreground/70">Tracker</div>
                </div>
            </div>
            <nav class="flex-1 space-y-0.5 px-3">
                <?php
                    $user = auth()->user();
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['zone_admin','group_admin','church_admin']],
                        ['route' => 'search', 'label' => 'Search', 'roles' => ['zone_admin']],
                        ['route' => 'groups.index', 'label' => 'Group Churches', 'roles' => ['zone_admin']],
                        ['route' => 'churches.index', 'label' => 'Churches', 'roles' => ['zone_admin','group_admin']],
                        ['route' => 'partners.index', 'label' => 'Partners', 'roles' => ['zone_admin','group_admin','church_admin']],
                        ['route' => 'givings.index', 'label' => 'Givings', 'roles' => ['zone_admin','group_admin','church_admin']],
                        ['route' => 'statements.index', 'label' => 'Giving Statements', 'roles' => ['zone_admin']],
                        ['route' => 'alerts.index', 'label' => 'Giving Alerts', 'roles' => ['zone_admin','group_admin','church_admin']],
                        ['route' => 'upload.index', 'label' => 'Bulk Upload', 'roles' => ['zone_admin','group_admin','church_admin']],
                        ['route' => 'arms.index', 'label' => 'Partnership Arms', 'roles' => ['zone_admin']],
                        ['route' => 'audit.index', 'label' => 'Audit Logs', 'roles' => ['zone_admin']],
                    ];
                ?>
                <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(in_array($user->role, $item['roles'], true)): ?>
                        <a href="<?php echo e(route($item['route'])); ?>"
                           class="flex items-center gap-3 rounded-md px-3 py-2 text-sm transition <?php echo e(request()->routeIs($item['route']) || request()->routeIs(explode('.', $item['route'])[0].'.*') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent'); ?>">
                            <?php echo e($item['label']); ?>

                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
            <div class="border-t border-sidebar-border p-4">
                <div class="text-sm font-medium"><?php echo e($user->name); ?></div>
                <div class="text-xs text-sidebar-foreground/70"><?php echo e($user->roleLabel()); ?></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-3">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm text-sidebar-foreground hover:bg-sidebar-accent">
                        Sign out
                    </button>
                </form>
            </div>
        </aside>
        <main class="flex-1 bg-background">
            <?php if(session('success')): ?>
                <div class="m-6 mb-0 rounded-md border border-accent/40 bg-accent/10 px-4 py-3 text-sm text-foreground"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="m-6 mb-0 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-4">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</body>
</html>
<?php /**PATH C:\Users\kings\partnership\partnership\resources\views/layouts/app.blade.php ENDPATH**/ ?>