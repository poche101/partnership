@extends('layouts.app')
@section('title', 'Givings')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-xl sm:text-2xl text-primary">Givings</h1>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <span class="text-sm text-muted-foreground">Showing: <strong>{{ $armLabel }}</strong></span>
                <span class="givings-total-badge">
                    <span class="givings-total-label">Total</span>
                    <span class="givings-total-amount font-mono">{{ number_format($totalShown, 2) }}</span>
                    <span class="givings-total-currency">ESPEES</span>
                </span>
            </div>
        </div>
        <button data-open-modal="new-giving" class="btn-primary">+ Record Giving</button>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('givings.index') }}" class="badge {{ $armFilter === 'all' ? 'bg-accent text-accent-foreground' : '' }}">All Arms</a>
        @foreach ($arms as $arm)
            <a href="{{ route('givings.index', ['arm' => $arm['key']]) }}" class="badge {{ $armFilter === $arm['key'] ? 'bg-accent text-accent-foreground' : '' }}">{{ $arm['label'] }}</a>
        @endforeach
    </div>

    <div class="registry mt-6">
        <div class="registry-scroll">
            <table class="registry-table">
                <thead>
                    <tr class="registry-group-row">
                        <th colspan="5" class="registry-group-header registry-group-partner">Partner Details</th>
                        <th colspan="5" class="registry-group-header registry-group-spouse">Spouse Details</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Last Giving</th>
                        <th rowspan="2" class="registry-group-header registry-group-giving">Amount<br><span class="registry-group-subtext">(ESPEES)</span></th>
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
                    @forelse ($view as $row)
                        @php
                            $entry = $row['entry'];
                            $partner = $entry->partner;
                            $hasSpouse = $partner && filled($partner->spouse_first_name);

                            $partnerName = $partner
                                ? trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name)
                                : '—';

                            $spouseName = $hasSpouse
                                ? trim(
                                    ($partner->spouse_title ?? '').' '.
                                    $partner->spouse_first_name.' '.
                                    ($partner->spouse_last_name ?: $partner->last_name)
                                )
                                : null;

                            $initials = $partner
                                ? strtoupper(mb_substr($partner->first_name ?? '?', 0, 1).mb_substr($partner->last_name ?? '', 0, 1))
                                : '?';
                            $palette = ['#3B5A73', '#7A5C3E', '#4E6E58', '#6B5B95', '#8A5A44', '#3E6B6B'];
                            $swatch = $palette[crc32(($partner->id ?? 0).($partner->first_name ?? '')) % count($palette)];
                        @endphp
                        <tr>
                            {{-- Partner Name --}}
                            <td>
                                <div class="registry-partner">
                                    <span class="registry-avatar" style="background: {{ $swatch }}">{{ $initials }}</span>
                                    <div class="registry-partner-text">
                                        <div class="registry-name">{{ $partnerName }}</div>
                                        <div class="registry-church">{{ $entry->church?->name ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            {{-- Partner Category --}}
                            <td>
                                @if($partner?->delegate_category)
                                    <span class="badge">{{ $partner->delegate_category }}</span>
                                @else
                                    <span class="registry-muted">—</span>
                                @endif
                            </td>
                            {{-- Partner Phone --}}
                            <td>{{ $partner?->phone ?: '—' }}</td>
                            {{-- Partner Email --}}
                            <td>{{ $partner?->email ?: '—' }}</td>
                            {{-- Partner KingsChat --}}
                            <td>
                                @if($partner?->kingschat_username)
                                    <span class="registry-handle">{{ '@'.$partner->kingschat_username }}</span>
                                @else
                                    <span class="registry-muted">—</span>
                                @endif
                            </td>

                            {{-- Spouse Name --}}
                            <td class="registry-divider">
                                @if($hasSpouse)
                                    <div class="registry-name">{{ $spouseName }}</div>
                                @else
                                    <span class="registry-muted">—</span>
                                @endif
                            </td>
                            {{-- Spouse Category --}}
                            <td>
                                @if($hasSpouse && !empty($partner->spouse_delegate_category))
                                    <span class="badge">{{ $partner->spouse_delegate_category }}</span>
                                @else
                                    <span class="registry-muted">—</span>
                                @endif
                            </td>
                            {{-- Spouse Phone --}}
                            <td>{{ $hasSpouse && $partner->spouse_phone ? $partner->spouse_phone : '—' }}</td>
                            {{-- Spouse Email --}}
                            <td>{{ $hasSpouse && $partner->spouse_email ? $partner->spouse_email : '—' }}</td>
                            {{-- Spouse KingsChat --}}
                            <td>
                                @if($hasSpouse && $partner->spouse_kingschat)
                                    <span class="registry-handle">{{ '@'.$partner->spouse_kingschat }}</span>
                                @else
                                    <span class="registry-muted">—</span>
                                @endif
                            </td>

                            {{-- One shared amount for the partner + spouse giving --}}
                            <td>{{ $entry->recorded_at?->format('M j, Y') }}</td>
                            <td class="font-mono">{{ number_format($row['amount'], 2) }}</td>
                            <td>
                                <div class="registry-actions">
                                    <a href="{{ route('audit.index', ['entity_type' => \App\Models\PartnershipEntry::class, 'entity_id' => $entry->id]) }}"
                                       class="text-xs text-muted-foreground underline">
                                        History
                                    </a>
                                    <button type="button" data-open-modal="edit-giving-{{ $entry->id }}"
                                        class="btn-icon-only" title="Edit giving record">
                                        <svg viewBox="0 0 20 20" fill="none" class="btn-svg">
                                            <path d="M13.5 3.5l3 3L6 17H3v-3L13.5 3.5z" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <button type="button" data-open-modal="delete-giving-{{ $entry->id }}"
                                        class="btn-icon-only btn-icon-danger" title="Delete giving record">
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
                        <tr><td colspan="13" class="registry-empty">No givings recorded for this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{--
    Edit and Delete modals for each giving entry are rendered here, OUTSIDE
    the table, using the same two-layer centering wrapper as the rest of the
    app (outer = fixed + overflow-y-auto, inner = flex items-center
    justify-center). Nesting them inside the table would hide them behind a
    display:none ancestor no matter what the modal's own class says.
--}}
@foreach ($view as $row)
    @php
        $entry = $row['entry'];
        $partner = $entry->partner;
        $partnerName = $partner
            ? trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name)
            : 'this partner';
    @endphp

    {{-- Edit Giving Modal --}}
    <div id="edit-giving-{{ $entry->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="card w-full max-w-2xl p-4 sm:p-6">
                <h2 class="font-display text-lg text-primary">Edit Giving Record</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $partnerName }} &middot; {{ $entry->church?->name ?? '—' }}</p>
                <form method="POST" action="{{ route('givings.update', $entry) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($arms as $arm)
                            <div>
                                <label class="field-label">{{ $arm['label'] }}</label>
                                <input type="number" step="0.01" min="0" name="{{ $arm['key'] }}"
                                    value="{{ $entry->{$arm['key']} }}" class="field-input">
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="field-label">Note</label>
                        <textarea name="note" rows="2" class="field-input">{{ $entry->note }}</textarea>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <button type="button" data-close-modal="edit-giving-{{ $entry->id }}"
                            class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Giving Confirmation Modal --}}
    <div id="delete-giving-{{ $entry->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="card w-full max-w-md p-4 sm:p-6">
                <h2 class="font-display text-lg text-primary">Delete Giving Record</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Are you sure you want to delete this giving record for <strong>{{ $partnerName }}</strong>
                    ({{ number_format($entry->total_espees, 2) }} ESPEES)? This action cannot be undone.
                </p>
                <form method="POST" action="{{ route('givings.destroy', $entry) }}" class="mt-6 flex flex-wrap justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" data-close-modal="delete-giving-{{ $entry->id }}"
                        class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div id="new-giving" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="card w-full max-w-2xl p-4 sm:p-6">
            <h2 class="font-display text-lg text-primary">Record Giving</h2>
            <form id="new-giving-form" method="POST" action="{{ route('givings.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="field-label">Partner</label>

                    <div class="combobox relative" id="partner-combobox">
                        <button
                            type="button"
                            id="partner-trigger"
                            class="field-input flex w-full items-center justify-between gap-2 text-left"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span id="partner-trigger-label" class="truncate text-muted-foreground">Select partner…</span>
                            <svg viewBox="0 0 20 20" fill="none" class="combobox-chevron">
                                <path d="M5.5 7.5L10 12l4.5-4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div id="partner-panel" class="combobox-panel hidden">
                            <input
                                type="text"
                                id="partner-search"
                                class="combobox-search"
                                placeholder="Search partners…"
                                autocomplete="off"
                            >
                            <ul id="partner-options" class="combobox-options" role="listbox">
                                @foreach ($partners as $p)
                                    <li role="option" class="combobox-option" data-id="{{ $p->id }}" data-name="{{ $p->fullName() }}" data-spouse="{{ $p->spouse_name }}">
                                        {{ $p->fullName() }}
                                    </li>
                                @endforeach
                            </ul>
                            <p id="partner-no-results" class="combobox-empty hidden">No partners match your search.</p>
                        </div>
                    </div>

                    <input type="hidden" name="partner_id" id="partner-select" value="">
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="include_spouse" value="1" id="include-spouse-toggle">
                        Include spouse
                    </label>
                    <input
                        type="text"
                        name="spouse_name"
                        id="spouse-name-input"
                        placeholder="Spouse's name"
                        class="field-input mt-2 hidden"
                    >
                </div>

                {{--
                    Arm amount inputs are generated from $arms, which the
                    controller populates from the live partnership-arms
                    list. Any arm added there shows up here automatically
                    on the next page load — nothing in this view is
                    hardcoded, so new arms take effect immediately with no
                    template changes needed.
                --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($arms as $arm)
                        <div>
                            <label class="field-label">{{ $arm['label'] }}</label>
                            <input type="number" step="0.01" min="0" name="{{ $arm['key'] }}" value="0" class="field-input">
                        </div>
                    @endforeach
                </div>
                <div>
                    <label class="field-label">Note</label>
                    <textarea name="note" rows="2" class="field-input"></textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button type="button" data-close-modal="new-giving" class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary">Save Giving</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('include-spouse-toggle');
    const spouseInput = document.getElementById('spouse-name-input');

    // Partner combobox: a button showing the current selection opens a
    // panel with the search box built in above the option list. Selecting
    // an option sets the hidden #partner-select input (what the form
    // actually submits as partner_id) and updates the trigger label, then
    // closes the panel — and drives the same include-spouse auto-fill
    // behavior the plain <select> used to trigger on 'change'.
    const root = document.getElementById('partner-combobox');
    const trigger = document.getElementById('partner-trigger');
    const triggerLabel = document.getElementById('partner-trigger-label');
    const panel = document.getElementById('partner-panel');
    const search = document.getElementById('partner-search');
    const optionsList = document.getElementById('partner-options');
    const noResults = document.getElementById('partner-no-results');
    const hiddenInput = document.getElementById('partner-select');
    const form = document.getElementById('new-giving-form');

    if (root && trigger && panel && search && optionsList && hiddenInput) {
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

                const spouse = opt.dataset.spouse || '';
                if (toggle && spouseInput) {
                    if (spouse) {
                        toggle.checked = true;
                        spouseInput.classList.remove('hidden');
                        spouseInput.value = spouse;
                    } else {
                        toggle.checked = false;
                        spouseInput.classList.add('hidden');
                        spouseInput.value = '';
                    }
                }
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
    }

    if (toggle && spouseInput) {
        toggle.addEventListener('change', () => {
            spouseInput.classList.toggle('hidden', !toggle.checked);
            if (!toggle.checked) spouseInput.value = '';
        });
    }
});
</script>

<style>
    .registry {
        border: 1px solid var(--border, #E5E1D8);
        border-radius: 8px;
        overflow: hidden;
        background: var(--card, #fff);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .registry-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .registry-scroll::-webkit-scrollbar { height: 8px; }
    .registry-scroll::-webkit-scrollbar-thumb {
        background: var(--border, #E5E1D8);
        border-radius: 999px;
    }

    .registry-table {
        width: 100%;
        min-width: 1420px;
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
    .registry-group-giving {
        background: var(--muted, #F3F2ED);
        color: var(--primary, #3B5A73);
    }
    .registry-group-actions {
        background: var(--muted, #F3F2ED);
        color: var(--muted-foreground, #7A756B);
    }
    .registry-group-subtext {
        font-size: 0.6rem;
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
        color: var(--muted-foreground, #7A756B);
    }

    .registry-table tbody tr {
        border-bottom: 1px solid var(--border, #EEEBE3);
        transition: background-color 0.12s ease;
    }
    .registry-table tbody tr:last-child { border-bottom: none; }
    .registry-table tbody tr:hover { background: var(--muted, #FAFAF7); }
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
    .registry-partner-text { min-width: 0; }
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

    .registry-muted { color: var(--muted-foreground, #B3AEA1); }
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
        gap: 0.55rem;
    }

    .btn-icon-only {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
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
        width: 15px;
        height: 15px;
    }

    .btn-danger {
        background: #B3261E;
        border-color: #B3261E;
    }
    .btn-danger:hover {
        background: #922019;
        border-color: #922019;
    }

    /* Header total-given badge, explicitly labelled in ESPEES */
    .givings-total-badge {
        display: inline-flex;
        align-items: baseline;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: var(--muted, #F3F2ED);
        border: 1px solid var(--border, #E5E1D8);
    }
    .givings-total-label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--muted-foreground, #7A756B);
    }
    .givings-total-amount {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--primary, #3B5A73);
    }
    .givings-total-currency {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: var(--muted-foreground, #7A756B);
    }

    /* Partner combobox (search bar built into the dropdown panel) */
    .combobox-chevron {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--muted-foreground, #7A756B);
        transition: transform 0.12s ease;
    }
    #partner-trigger[aria-expanded="true"] .combobox-chevron {
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
        box-shadow: 0 8px 24px -8px rgba(0,0,0,0.18), 0 2px 6px rgba(0,0,0,0.06);
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