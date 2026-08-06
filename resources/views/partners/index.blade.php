@extends('layouts.app')
@section('title', 'Partners')
@section('content')
    @php
        $titleOptions = ['Brother', 'Sister', 'Deacon', 'Deaconess', 'Pastor'];
    @endphp
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-xl sm:text-2xl text-primary">Partners</h1>
                <p class="mt-1 text-sm text-muted-foreground">All partners visible within your scope.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('partners.export') }}" class="btn-outline btn-icon">
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
            <input type="text" name="q" value="{{ $q }}" placeholder="Search name, email, KingsChat..."
                class="field-input w-full sm:max-w-sm">
            @auth
                @if (auth()->user()->isZoneAdmin())
                    <label class="flex items-center gap-2 text-sm text-muted-foreground">
                        <input type="checkbox" name="ai" value="1" {{ $aiMode ? 'checked' : '' }}> AI semantic search
                    </label>
                @endif
            @endauth
            <button type="submit" class="btn-outline">Search</button>
        </form>

        <div class="registry mt-6">
            <div class="registry-scroll">
                <table class="registry-table">
                    <thead>
                        <tr class="registry-group-row">
                            <th colspan="5" class="registry-group-header registry-group-partner">Partner Details</th>
                            <th colspan="5" class="registry-group-header registry-group-spouse">Spouse Details</th>
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
                        @forelse ($partners as $p)
                            @php
                                $hasSpouse = filled($p->spouse_first_name);

                                $partnerName = trim(($p->title ?? '') . ' ' . $p->first_name . ' ' . $p->last_name);

                                $spouseName = $hasSpouse
                                    ? trim(
                                        ($p->spouse_title ?? '') .
                                            ' ' .
                                            $p->spouse_first_name .
                                            ' ' .
                                            ($p->spouse_last_name ?: $p->last_name),
                                    )
                                    : null;

                                $initials = strtoupper(
                                    mb_substr($p->first_name ?? '?', 0, 1) . mb_substr($p->last_name ?? '', 0, 1),
                                );
                                $palette = ['#3B5A73', '#7A5C3E', '#4E6E58', '#6B5B95', '#8A5A44', '#3E6B6B'];
                                $swatch = $palette[crc32($p->id . $p->first_name) % count($palette)];
                            @endphp
                            <tr>
                                {{-- Partner Name --}}
                                <td>
                                    <div class="registry-partner">
                                        <span class="registry-avatar"
                                            style="background: {{ $swatch }}">{{ $initials }}</span>
                                        <div class="registry-partner-text">
                                            <div class="registry-name">{{ $partnerName }}</div>
                                            <div class="registry-church">{{ $p->church?->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- Partner Category --}}
                                <td>
                                    @if ($p->delegate_category)
                                        <span class="badge">{{ $p->delegate_category }}</span>
                                    @else
                                        <span class="registry-muted">—</span>
                                    @endif
                                </td>
                                {{-- Partner Phone --}}
                                <td>{{ $p->phone ?: '—' }}</td>
                                {{-- Partner Email --}}
                                <td>{{ $p->email ?: '—' }}</td>
                                {{-- Partner KingsChat --}}
                                <td>
                                    @if ($p->kingschat_username)
                                        <span class="registry-handle">{{ '@' . $p->kingschat_username }}</span>
                                    @else
                                        <span class="registry-muted">—</span>
                                    @endif
                                </td>

                                {{-- Spouse Name --}}
                                <td class="registry-divider">
                                    @if ($hasSpouse)
                                        <div class="registry-name">{{ $spouseName }}</div>
                                    @else
                                        <span class="registry-muted">—</span>
                                    @endif
                                </td>
                                {{-- Spouse Category --}}
                                <td>
                                    @if ($hasSpouse && !empty($p->spouse_delegate_category))
                                        <span class="badge">{{ $p->spouse_delegate_category }}</span>
                                    @else
                                        <span class="registry-muted">—</span>
                                    @endif
                                </td>
                                {{-- Spouse Phone --}}
                                <td>{{ $hasSpouse && $p->spouse_phone ? $p->spouse_phone : '—' }}</td>
                                {{-- Spouse Email --}}
                                <td>{{ $hasSpouse && $p->spouse_email ? $p->spouse_email : '—' }}</td>
                                {{-- Spouse KingsChat --}}
                                <td>
                                    @if ($hasSpouse && $p->spouse_kingschat)
                                        <span class="registry-handle">{{ '@' . $p->spouse_kingschat }}</span>
                                    @else
                                        <span class="registry-muted">—</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="registry-actions">
                                        <button type="button" data-open-modal="edit-partner-{{ $p->id }}"
                                            class="btn-icon-only" title="Edit partner">
                                            <svg viewBox="0 0 20 20" fill="none" class="btn-svg">
                                                <path
                                                    d="M13.5 3.5l3 3L6 17H3v-3L13.5 3.5z"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                        <button type="button" data-open-modal="delete-partner-{{ $p->id }}"
                                            class="btn-icon-only btn-icon-danger" title="Delete partner">
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
                        @empty
                            <tr>
                                <td colspan="11" class="registry-empty">No partners found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{--
        Edit and Delete modals live OUTSIDE the table on purpose.
        Previously they were nested inside a hidden <tr>, and a
        display:none ancestor hides its children even when the modal
        itself is position:fixed — that's why "Edit" did nothing.

        Every modal now uses a two-layer wrapper:
          outer  = fixed inset-0, scrolls if content is taller than the
                   viewport (overflow-y-auto), holds the dim backdrop.
          inner  = min-h-full flex items-center justify-center, which is
                   what actually centers the card. Putting items-center on
                   the SAME element as overflow-y-auto clips/centers badly
                   once content is taller than the screen; splitting it into
                   two elements keeps the card centered on tall screens and
                   properly scrollable (from the top) on short ones.
    --}}
    @foreach ($partners as $p)
        @php
            $hasSpouse = filled($p->spouse_first_name);
            $partnerName = trim(($p->title ?? '') . ' ' . $p->first_name . ' ' . $p->last_name);
        @endphp

        {{-- Edit Partner Modal --}}
        <div id="edit-partner-{{ $p->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="card w-full max-w-2xl p-4 sm:p-6">
                    <h2 class="font-display text-lg text-primary">Edit Partner</h2>
                    <form method="POST" action="{{ route('partners.update', $p) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        @if ($churches->count() > 1 || !auth()->user()->isChurchAdmin())
                            <div>
                                <label class="field-label">Church</label>
                                <select name="church_id" required class="field-input">
                                    @foreach ($churches as $c)
                                        <option value="{{ $c->id }}"
                                            {{ $p->church_id == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="field-label">Title</label>
                                <select name="title" class="field-input">
                                    <option value="">—</option>
                                    @foreach ($titleOptions as $t)
                                        <option value="{{ $t }}" {{ $p->title === $t ? 'selected' : '' }}>
                                            {{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-1"><label class="field-label">First name</label><input
                                    name="first_name" required value="{{ $p->first_name }}" class="field-input">
                            </div>
                            <div><label class="field-label">Last name</label><input name="last_name"
                                    value="{{ $p->last_name }}" class="field-input"></div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Delegate category</label>
                                <select name="delegate_category" class="field-input">
                                    <option value="">—</option>
                                    @foreach ($delegateCategories as $dc)
                                        <option value="{{ $dc }}"
                                            {{ $p->delegate_category === $dc ? 'selected' : '' }}>{{ $dc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="field-label">KingsChat username</label><input
                                    name="kingschat_username" value="{{ $p->kingschat_username }}"
                                    class="field-input"></div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div><label class="field-label">Phone</label><input name="phone"
                                    value="{{ $p->phone }}" class="field-input"></div>
                            <div><label class="field-label">Email</label><input type="email" name="email"
                                    value="{{ $p->email }}" class="field-input"></div>
                        </div>
                        <details class="rounded-md border border-border p-3" {{ $hasSpouse ? 'open' : '' }}>
                            <summary class="cursor-pointer text-sm font-medium text-foreground">Spouse details
                                (optional)</summary>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="field-label">Title</label>
                                    <select name="spouse_title" class="field-input">
                                        <option value="">—</option>
                                        @foreach ($titleOptions as $t)
                                            <option value="{{ $t }}"
                                                {{ $p->spouse_title === $t ? 'selected' : '' }}>{{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><label class="field-label">First name</label><input
                                        name="spouse_first_name" value="{{ $p->spouse_first_name }}"
                                        class="field-input"></div>
                                <div><label class="field-label">Surname</label><input name="spouse_last_name"
                                        value="{{ $p->spouse_last_name }}" class="field-input"></div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Spouse delegate category</label>
                                    <select name="spouse_delegate_category" class="field-input">
                                        <option value="">—</option>
                                        @foreach ($delegateCategories as $dc)
                                            <option value="{{ $dc }}"
                                                {{ $p->spouse_delegate_category === $dc ? 'selected' : '' }}>
                                                {{ $dc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><label class="field-label">KingsChat</label><input name="spouse_kingschat"
                                        value="{{ $p->spouse_kingschat }}" class="field-input"></div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div><label class="field-label">Phone</label><input name="spouse_phone"
                                        value="{{ $p->spouse_phone }}" class="field-input"></div>
                                <div><label class="field-label">Email</label><input type="email"
                                        name="spouse_email" value="{{ $p->spouse_email }}" class="field-input">
                                </div>
                            </div>
                        </details>
                        <div class="flex flex-wrap justify-end gap-2 pt-2">
                            <button type="button" data-close-modal="edit-partner-{{ $p->id }}"
                                class="btn-outline">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete Partner Confirmation Modal --}}
        <div id="delete-partner-{{ $p->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="card w-full max-w-md p-4 sm:p-6">
                    <h2 class="font-display text-lg text-primary">Delete Partner</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Are you sure you want to delete <strong>{{ $partnerName }}</strong>?
                        This action cannot be undone.
                    </p>
                    <form method="POST" action="{{ route('partners.destroy', $p) }}"
                        class="mt-6 flex flex-wrap justify-end gap-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" data-close-modal="delete-partner-{{ $p->id }}"
                            class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div id="new-partner" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="card w-full max-w-2xl p-4 sm:p-6">
                <h2 class="font-display text-lg text-primary">New Partner</h2>
                <form id="new-partner-form" method="POST" action="{{ route('partners.store') }}" class="mt-4 space-y-4">
                    @csrf
                    @if ($churches->count() > 1 || !auth()->user()->isChurchAdmin())
                        <div>
                            <label class="field-label">Church</label>

                            <div class="combobox relative" id="new-partner-church-combobox">
                                <button
                                    type="button"
                                    id="new-partner-church-trigger"
                                    class="field-input flex w-full items-center justify-between gap-2 text-left"
                                    aria-haspopup="listbox"
                                    aria-expanded="false"
                                >
                                    <span id="new-partner-church-trigger-label" class="truncate text-muted-foreground">Select church…</span>
                                    <svg viewBox="0 0 20 20" fill="none" class="combobox-chevron">
                                        <path d="M5.5 7.5L10 12l4.5-4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>

                                <div id="new-partner-church-panel" class="combobox-panel hidden">
                                    <input
                                        type="text"
                                        id="new-partner-church-search"
                                        class="combobox-search"
                                        placeholder="Search churches…"
                                        autocomplete="off"
                                    >
                                    <ul id="new-partner-church-options" class="combobox-options" role="listbox">
                                        @foreach ($churches as $c)
                                            <li role="option" class="combobox-option" data-id="{{ $c->id }}" data-name="{{ $c->name }}">
                                                {{ $c->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    <p id="new-partner-church-no-results" class="combobox-empty hidden">No churches match your search.</p>
                                </div>
                            </div>

                            <input type="hidden" name="church_id" id="new-partner-church-select" value="">
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="field-label">Title</label>
                            <select name="title" class="field-input">
                                <option value="">—</option>
                                @foreach ($titleOptions as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-1"><label class="field-label">First name</label><input
                                name="first_name" required class="field-input"></div>
                        <div><label class="field-label">Last name</label><input name="last_name"
                                class="field-input"></div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Delegate category</label>
                            <select name="delegate_category" class="field-input">
                                <option value="">—</option>
                                @foreach ($delegateCategories as $dc)
                                    <option value="{{ $dc }}">{{ $dc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="field-label">KingsChat username</label><input name="kingschat_username"
                                class="field-input"></div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="field-label">Phone</label><input name="phone" class="field-input"></div>
                        <div><label class="field-label">Email</label><input type="email" name="email"
                                class="field-input"></div>
                    </div>
                    <details class="rounded-md border border-border p-3">
                        <summary class="cursor-pointer text-sm font-medium text-foreground">Spouse details
                            (optional)</summary>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="field-label">Title</label>
                                <select name="spouse_title" class="field-input">
                                    <option value="">—</option>
                                    @foreach ($titleOptions as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="field-label">First name</label><input name="spouse_first_name"
                                    class="field-input"></div>
                            <div><label class="field-label">Surname</label><input name="spouse_last_name"
                                    class="field-input"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Spouse delegate category</label>
                                <select name="spouse_delegate_category" class="field-input">
                                    <option value="">—</option>
                                    @foreach ($delegateCategories as $dc)
                                        <option value="{{ $dc }}">{{ $dc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="field-label">KingsChat</label><input name="spouse_kingschat"
                                    class="field-input"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div><label class="field-label">Phone</label><input name="spouse_phone"
                                    class="field-input"></div>
                            <div><label class="field-label">Email</label><input type="email" name="spouse_email"
                                    class="field-input"></div>
                        </div>
                    </details>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" data-close-modal="new-partner" class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary">Save Partner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('new-partner-church-combobox');
        const trigger = document.getElementById('new-partner-church-trigger');
        const triggerLabel = document.getElementById('new-partner-church-trigger-label');
        const panel = document.getElementById('new-partner-church-panel');
        const search = document.getElementById('new-partner-church-search');
        const optionsList = document.getElementById('new-partner-church-options');
        const noResults = document.getElementById('new-partner-church-no-results');
        const hiddenInput = document.getElementById('new-partner-church-select');
        const form = document.getElementById('new-partner-form');

        if (!root || !trigger || !panel || !search || !optionsList || !hiddenInput) return;

        const options = Array.from(optionsList.querySelectorAll('.combobox-option'));

        function openPanel() {
            panel.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            trigger.classList.remove('combobox-trigger-error');
            search.value = '';
            options.forEach((o) => { o.hidden = false; });
            noResults.classList.add('hidden');
            search.focus();
        }

        function closePanel() {
            panel.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', () => {
            const isOpen = !panel.classList.contains('hidden');
            if (isOpen) {
                closePanel();
            } else {
                openPanel();
            }
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) closePanel();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closePanel();
        });

        search.addEventListener('input', () => {
            const term = search.value.trim().toLowerCase();
            let anyVisible = false;

            options.forEach((o) => {
                const matches = !term || o.dataset.name.toLowerCase().includes(term);
                o.hidden = !matches;
                if (matches) anyVisible = true;
            });

            noResults.classList.toggle('hidden', anyVisible);
        });

        options.forEach((opt) => {
            opt.addEventListener('click', () => {
                hiddenInput.value = opt.dataset.id;
                triggerLabel.textContent = opt.dataset.name;
                triggerLabel.classList.remove('text-muted-foreground');
                closePanel();
            });
        });

        // The hidden input can't show the browser's native "please fill
        // this field" bubble, so validate on submit ourselves and reopen
        // the panel with a visible error state instead.
        form?.addEventListener('submit', (e) => {
            if (!hiddenInput.value) {
                e.preventDefault();
                trigger.classList.add('combobox-trigger-error');
                openPanel();
            }
        });
    });
    </script>

    <style>
        .registry {
            border: 1px solid var(--border, #E5E1D8);
            border-radius: 8px;
            overflow: hidden;
            background: var(--card, #fff);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        /* Horizontal auto-scroll wrapper for the wider two-block table */
        .registry-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .registry-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .registry-scroll::-webkit-scrollbar-thumb {
            background: var(--border, #E5E1D8);
            border-radius: 999px;
        }

        .registry-table {
            width: 100%;
            min-width: 1200px;
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

        .registry-group-actions {
            background: var(--muted, #F3F2ED);
            color: var(--muted-foreground, #7A756B);
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

        .registry-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-icon-only {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
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
            width: 16px;
            height: 16px;
        }

        .btn-danger {
            background: #B3261E;
            border-color: #B3261E;
        }

        .btn-danger:hover {
            background: #922019;
            border-color: #922019;
        }

        @media (max-width: 480px) {
            .registry-actions {
                gap: 0.3rem;
            }

            .btn-icon-only {
                width: 30px;
                height: 30px;
            }
        }

        /* Church combobox (New Partner modal) — search bar built into the dropdown panel */
        .combobox-chevron {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: var(--muted-foreground, #7A756B);
            transition: transform 0.12s ease;
        }

        #new-partner-church-trigger[aria-expanded="true"] .combobox-chevron {
            transform: rotate(180deg);
        }

        .combobox-trigger-error {
            border-color: #B3261E !important;
        }

        .combobox-panel {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 30;
            background: var(--card, #fff);
            border: 1px solid var(--border, #E5E1D8);
            border-radius: 8px;
            box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.18), 0 2px 6px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .combobox-search {
            width: 100%;
            border: none;
            border-bottom: 1px solid var(--border, #E5E1D8);
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            outline: none;
            background: var(--card, #fff);
            color: var(--foreground, #1F1B16);
        }

        .combobox-search:focus {
            background: var(--muted, #FAFAF7);
        }

        .combobox-options {
            list-style: none;
            margin: 0;
            padding: 0.25rem 0;
            max-height: 14rem;
            overflow-y: auto;
        }

        .combobox-option {
            padding: 0.55rem 0.85rem;
            font-size: 0.875rem;
            color: var(--foreground, #1F1B16);
            cursor: pointer;
        }

        .combobox-option:hover,
        .combobox-option.is-active {
            background: var(--muted, #FAFAF7);
            color: var(--primary, #3B5A73);
        }

        .combobox-empty {
            padding: 0.75rem 0.85rem;
            font-size: 0.8rem;
            color: var(--muted-foreground, #B3AEA1);
            text-align: center;
        }
    </style>
@endsection