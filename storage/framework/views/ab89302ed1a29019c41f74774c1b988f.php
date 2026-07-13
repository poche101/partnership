<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Partnership Tracker</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="<?php echo e(route('home')); ?>" class="font-display text-3xl tracking-tight text-primary">Partnership Tracker</a>
                <div class="mx-auto mt-3 h-px w-24 bg-accent"></div>
                <p class="mt-3 font-mono text-xs uppercase tracking-widest text-muted-foreground">Zone &middot; Group &middot; Church access</p>
            </div>

            <div class="card p-8">
                <h1 class="font-display text-xl text-foreground">Sign in to your account</h1>
                <p class="mt-1 text-sm text-muted-foreground">Use the email and password issued to you by your zone or group administrator.</p>

                <?php if($errors->any()): ?>
                    <div class="mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('login.attempt')); ?>" class="mt-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="field-label" for="email">Email address</label>
                        <input id="email" name="email" type="email" required autofocus value="<?php echo e(old('email')); ?>" class="field-input" placeholder="you@example.org">
                    </div>
                    <div>
                        <label class="field-label" for="password">Password</label>
                        <input id="password" name="password" type="password" required class="field-input" placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-primary w-full">Sign in</button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-muted-foreground">
                Don't have login details? Contact your zone administrator to have an account created for your group or church.
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\kings\Downloads\partnership-tracker-laravel\pt-laravel\resources\views/auth/login.blade.php ENDPATH**/ ?>