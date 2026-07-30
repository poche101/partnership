@extends('layouts.app')
@section('title', 'Givings')
@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl text-primary">Givings</h1>
            <p class="mt-1 text-sm text-muted-foreground">Showing: <strong>{{ $armLabel }}</strong> &middot; Total: <span class="font-mono">{{ number_format($totalShown, 2) }}</span> ESPEES</p>
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
        <table class="registry-table">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th>Category</th>
                    <th>Contact</th>
                    <th>KingsChat</th>
                    <th>Spouse Contact</th>
                    <th>Last Giving</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($view as $row)
                    @php
                        $partner = $row['entry']->partner;
                        $hasSpouse = $partner && filled($partner->spouse_first_name);

                        if ($hasSpouse) {
                            $left = trim(($partner->title ?? '').' '.$partner->first_name);
                            $right = trim(($partner->spouse_title ?? '').' '.$partner->spouse_first_name);
                            $surname = $partner->spouse_last_name ?: $partner->last_name;
                            $displayName = trim("{$left} & {$right} {$surname}");
                        } else {
                            $displayName = $partner?->fullName() ?? '—';
                        }

                        $initials = $partner
                            ? strtoupper(mb_substr($partner->first_name ?? '?', 0, 1).mb_substr($partner->last_name ?? '', 0, 1))
                            : '?';
                        $palette = ['#3B5A73', '#7A5C3E', '#4E6E58', '#6B5B95', '#8A5A44', '#3E6B6B'];
                        $swatch = $palette[crc32(($partner->id ?? 0).($partner->first_name ?? '')) % count($palette)];

                        $spouseContactLines = collect([
                            $partner?->spouse_kingschat ? '@'.$partner->spouse_kingschat : null,
                            $partner?->spouse_phone,
                            $partner?->spouse_email,
                        ])->filter();
                    @endphp
                    <tr>
                        <td>
                            <div class="registry-partner">
                                <span class="registry-avatar" style="background: {{ $swatch }}">{{ $initials }}</span>
                                <div class="registry-partner-text">
                                    <div class="registry-name">{{ $displayName }}</div>
                                    <div class="registry-church">{{ $row['entry']->church?->name ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($partner?->delegate_category)
                                <span class="badge">{{ $partner->delegate_category }}</span>
                            @else
                                <span class="registry-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="registry-stack">
                                <span>{{ $partner?->phone ?: '—' }}</span>
                                <span class="registry-muted">{{ $partner?->email ?: '—' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($partner?->kingschat_username)
                                <span class="registry-handle">{{ '@'.$partner->kingschat_username }}</span>
                            @else
                                <span class="registry-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($spouseContactLines->isNotEmpty())
                                <div class="registry-stack">
                                    @foreach ($spouseContactLines as $line)
                                        <span>{{ $line }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="registry-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $row['entry']->recorded_at?->format('M j, Y') }}</td>
                        <td class="font-mono">{{ number_format($row['amount'], 2) }}</td>
                        <td>
                            <a href="{{ route('audit.index', ['entity_type' => \App\Models\PartnershipEntry::class, 'entity_id' => $row['entry']->id]) }}"
                               class="text-xs text-muted-foreground underline">
                                History
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="registry-empty">No givings recorded for this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="new-giving" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 overflow-y-auto">
    <div class="card my-8 w-full max-w-2xl p-6">
        <h2 class="font-display text-lg text-primary">Record Giving</h2>
        <form method="POST" action="{{ route('givings.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="field-label">Partner</label>
                <select name="partner_id" id="partner-select" required class="field-input">
                    <option value="">Select partner…</option>
                    @foreach ($partners as $p)
                        <option value="{{ $p->id }}" data-spouse="{{ $p->spouse_name }}">{{ $p->fullName() }}</option>
                    @endforeach
                </select>
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
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal="new-giving" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Save Giving</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('include-spouse-toggle');
    const input = document.getElementById('spouse-name-input');
    const partnerSelect = document.getElementById('partner-select');

    if (toggle && input && partnerSelect) {
        toggle.addEventListener('change', () => {
            input.classList.toggle('hidden', !toggle.checked);
            if (!toggle.checked) input.value = '';
        });

        partnerSelect.addEventListener('change', () => {
            const selected = partnerSelect.options[partnerSelect.selectedIndex];
            const spouse = selected?.dataset.spouse || '';
            if (spouse) {
                toggle.checked = true;
                input.classList.remove('hidden');
                input.value = spouse;
            } else {
                toggle.checked = false;
                input.classList.add('hidden');
                input.value = '';
            }
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
    .registry-table {
        width: 100%;
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
        padding: 0.85rem 1.1rem;
        border-bottom: 2px solid var(--border, #E5E1D8);
        background: var(--muted, #FAFAF7);
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

    .registry-stack {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        line-height: 1.4;
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
</style>
@endsection