<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Partnership Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="font-display text-3xl tracking-tight text-primary">Partnership Tracker</a>
                <div class="mx-auto mt-3 h-px w-24 bg-accent"></div>
                <p class="mt-3 font-mono text-xs uppercase tracking-widest text-muted-foreground">Zone &middot; Group &middot; Church access</p>
            </div>

            <div class="card p-8">
                <h1 class="font-display text-xl text-foreground">Sign in to your account</h1>
                <p class="mt-1 text-sm text-muted-foreground">Use the email and password issued to you by your zone or group administrator.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="field-label" for="email">Email address</label>
                        <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" class="field-input" placeholder="you@example.org">
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
