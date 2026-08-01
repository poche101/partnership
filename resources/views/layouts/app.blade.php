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
        @php
            $user = auth()->user();
            $navItems = [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['zone_admin','group_admin','church_admin'], 'icon' => 'dashboard'],
                ['route' => 'search', 'label' => 'Search', 'roles' => ['zone_admin'], 'icon' => 'search'],
                ['route' => 'groups.index', 'label' => 'Group Churches', 'roles' => ['zone_admin'], 'icon' => 'network'],
                ['route' => 'churches.index', 'label' => 'Churches', 'roles' => ['zone_admin','group_admin'], 'icon' => 'building'],
                ['route' => 'partners.index', 'label' => 'Partners', 'roles' => ['zone_admin','group_admin','church_admin'], 'icon' => 'handshake'],
                ['route' => 'givings.index', 'label' => 'Givings', 'roles' => ['zone_admin','group_admin','church_admin'], 'icon' => 'heart'],
                ['route' => 'statements.index', 'label' => 'Giving Statements', 'roles' => ['zone_admin'], 'icon' => 'file-text'],
                ['route' => 'alerts.index', 'label' => 'Giving Alerts', 'roles' => ['zone_admin','group_admin','church_admin'], 'icon' => 'bell'],
                ['route' => 'upload.index', 'label' => 'Bulk Upload', 'roles' => ['zone_admin','group_admin','church_admin'], 'icon' => 'upload'],
                ['route' => 'arms.index', 'label' => 'Partnership Arms', 'roles' => ['zone_admin'], 'icon' => 'branch'],
                ['route' => 'audit.index', 'label' => 'Audit Logs', 'roles' => ['zone_admin'], 'icon' => 'shield'],
            ];

            $icons = [
                'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
                'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
                'network' => '<circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="19" r="2.5"/><circle cx="19" cy="19" r="2.5"/><path d="M12 7.5v4M12 11.5 6.5 17M12 11.5 17.5 17"/>',
                'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M10 22v-4h4v4"/><path d="M10 7h.01M14 7h.01M10 11h.01M14 11h.01M10 15h.01M14 15h.01"/>',
                'handshake' => '<path d="M8.5 14.5 4 10l3.5-3.5a2 2 0 0 1 2.83 0L11 7.17"/><path d="m13 7.17.67-.67a2 2 0 0 1 2.83 0L20 10l-4.5 4.5"/><path d="M8.5 14.5 11 17a1.5 1.5 0 0 0 2.12-2.12"/><path d="m13.12 14.88 1.13 1.12a1.5 1.5 0 0 0 2.12-2.12"/><path d="m9.5 9.5 4 4"/>',
                'heart' => '<path d="M19 14c1.5-1.5 3-3.5 3-5.5A4.5 4.5 0 0 0 17.5 4c-1.7 0-3.15.9-4 2.2C12.65 4.9 11.2 4 9.5 4A4.5 4.5 0 0 0 5 8.5c0 2 1.5 4 3 5.5l6 6Z"/>',
                'file-text' => '<path d="M14.5 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7.5Z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M9 13h6M9 17h6M9 9h1"/>',
                'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 4.5 1.5 6 2 7H4c.5-1 2-2.5 2-7"/><path d="M10 19a2 2 0 0 0 4 0"/>',
                'upload' => '<path d="M12 15V4"/><path d="m7 8 5-5 5 5"/><path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
                'branch' => '<circle cx="6" cy="5" r="2"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="12" r="2"/><path d="M6 7v10"/><path d="M6 12h6a4 4 0 0 0 4-4V7"/>',
                'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
            ];

            $logoSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';

            $navMarkup = function () use ($navItems, $icons, $user) {
                foreach ($navItems as $item) {
                    if (! in_array($user->role, $item['roles'], true)) {
                        continue;
                    }
                    $active = request()->routeIs($item['route']) || request()->routeIs(explode('.', $item['route'])[0] . '.*');
                    yield ['item' => $item, 'active' => $active];
                }
            };
        @endphp

        {{-- Mobile top bar --}}
        <header class="fixed inset-x-0 top-0 z-30 flex h-14 items-center justify-between border-b border-sidebar-border bg-sidebar px-4 text-sidebar-foreground md:hidden">
            <div class="flex items-center gap-2">
                <div class="grid h-8 w-8 place-items-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                    <span class="h-4 w-4">{!! $logoSvg !!}</span>
                </div>
                <div class="text-sm font-semibold leading-none">Partnership Tracker</div>
            </div>
            <button
                type="button"
                data-open-menu="mobile-nav"
                aria-label="Open menu"
                aria-controls="mobile-nav"
                class="grid h-9 w-9 place-items-center rounded-md hover:bg-sidebar-accent"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </header>

        {{-- Mobile overlay --}}
        <div
            id="mobile-nav-overlay"
            data-close-menu="mobile-nav"
            class="fixed inset-0 z-40 hidden bg-black/50 md:hidden"
        ></div>

        {{-- Mobile slide-out drawer --}}
        <aside
            id="mobile-nav"
            class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col bg-sidebar text-sidebar-foreground md:hidden"
        >
            <div class="flex items-center justify-between px-5 py-5">
                <div class="flex items-center gap-2">
                    <div class="grid h-9 w-9 place-items-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                        <span class="h-5 w-5">{!! $logoSvg !!}</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold">Partnership</div>
                        <div class="text-xs text-sidebar-foreground/70">Tracker</div>
                    </div>
                </div>
                <button
                    type="button"
                    data-close-menu="mobile-nav"
                    aria-label="Close menu"
                    class="grid h-8 w-8 place-items-center rounded-md hover:bg-sidebar-accent"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <nav class="flex-1 space-y-0.5 px-3">
                @foreach ($navMarkup() as $row)
                    <a href="{{ route($row['item']['route']) }}"
                       data-close-menu="mobile-nav"
                       class="flex items-center gap-3 rounded-md px-3 py-2 text-sm transition {{ $row['active'] ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$row['item']['icon']] !!}</svg>
                        <span>{{ $row['item']['label'] }}</span>
                    </a>
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

        {{-- Desktop sidebar --}}
        <aside class="hidden w-64 flex-col bg-sidebar text-sidebar-foreground md:flex">
            <div class="flex items-center gap-2 px-5 py-5">
                <div class="grid h-9 w-9 place-items-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                    <span class="h-5 w-5">{!! $logoSvg !!}</span>
                </div>
                <div>
                    <div class="text-sm font-semibold">Partnership</div>
                    <div class="text-xs text-sidebar-foreground/70">Tracker</div>
                </div>
            </div>
            <nav class="flex-1 space-y-0.5 px-3">
                @foreach ($navMarkup() as $row)
                    <a href="{{ route($row['item']['route']) }}"
                       class="flex items-center gap-3 rounded-md px-3 py-2 text-sm transition {{ $row['active'] ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$row['item']['icon']] !!}</svg>
                        <span>{{ $row['item']['label'] }}</span>
                    </a>
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

        <main class="flex-1 bg-background pt-14 md:pt-0">
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