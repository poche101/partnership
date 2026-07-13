<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Partnership Tracker')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                @php
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
                @endphp
                @foreach ($navItems as $item)
                    @if (in_array($user->role, $item['roles'], true))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-md px-3 py-2 text-sm transition {{ request()->routeIs($item['route']) || request()->routeIs(explode('.', $item['route'])[0].'.*') ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="border-t border-sidebar-border p-4">
                <div class="text-sm font-medium">{{ $user->name }}</div>
                <div class="text-xs text-sidebar-foreground/70">{{ $user->roleLabel() }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm text-sidebar-foreground hover:bg-sidebar-accent">
                        Sign out
                    </button>
                </form>
            </div>
        </aside>
        <main class="flex-1 bg-background">
            @if (session('success'))
                <div class="m-6 mb-0 rounded-md border border-accent/40 bg-accent/10 px-4 py-3 text-sm text-foreground">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="m-6 mb-0 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
