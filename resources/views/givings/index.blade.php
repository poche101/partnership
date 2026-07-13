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

    <div class="table-shell card mt-4 overflow-x-auto">
        <table>
            <thead><tr><th>Date</th><th>Partner</th><th>Church</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse ($view as $row)
                    <tr>
                        <td>{{ $row['entry']->recorded_at?->format('M j, Y') }}</td>
                        <td class="font-medium">{{ $row['entry']->partner?->fullName() }}</td>
                        <td>{{ $row['entry']->church?->name }}</td>
                        <td class="font-mono">{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-muted-foreground">No givings recorded for this filter.</td></tr>
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
                <select name="partner_id" required class="field-input">
                    <option value="">Select partner…</option>
                    @foreach ($partners as $p)
                        <option value="{{ $p->id }}">{{ $p->fullName() }}</option>
                    @endforeach
                </select>
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
@endsection
