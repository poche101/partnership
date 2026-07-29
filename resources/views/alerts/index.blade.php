@extends('layouts.app')
@section('title', 'Giving Alerts')
@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl text-primary">Giving Alerts</h1>
            <p class="mt-1 text-sm text-muted-foreground">Alerts fire automatically when a giving entry meets a configured threshold for an arm.</p>
        </div>
        <a href="{{ route('alerts.export') }}" class="btn-outline btn-icon">
            <svg viewBox="0 0 20 20" fill="none" class="btn-svg"><path d="M10 3v10m0 0l-4-4m4 4l4-4M4 16h12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Download Excel
        </a>
    </div>

    @if (auth()->user()->isZoneAdmin())
        <div class="card mt-6 p-6">
            <h2 class="font-display text-lg text-primary">Thresholds</h2>
            <div class="mt-4 space-y-3">
                @foreach ($arms as $arm)
                    @php $t = $thresholds->get($arm['key']); @endphp
                    <form method="POST" action="{{ route('alerts.thresholds.save') }}" class="flex flex-wrap items-center gap-3">
                        @csrf
                        <input type="hidden" name="arm_key" value="{{ $arm['key'] }}">
                        <span class="w-48 text-sm">{{ $arm['label'] }}</span>
                        <input type="number" step="0.01" min="0.01" name="threshold_espees" value="{{ $t?->threshold_espees ?? '' }}" placeholder="Threshold" class="field-input max-w-[160px]">
                        <label class="flex items-center gap-1 text-xs text-muted-foreground">
                            <input type="checkbox" name="enabled" value="1" {{ $t?->enabled ?? true ? 'checked' : '' }}> Enabled
                        </label>
                        <button type="submit" class="btn-outline">Save</button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Date</th><th>Partner</th><th>Church</th><th>Arm</th><th>Amount</th><th>Threshold</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($alerts as $a)
                    <tr>
                        <td>{{ $a->created_at->format('M j, Y g:ia') }}</td>
                        <td class="font-medium">{{ $a->partner?->fullName() }}</td>
                        <td>{{ $a->church?->name }}</td>
                        <td>{{ \App\Support\Arms::label($a->arm_key) }}</td>
                        <td class="font-mono">{{ number_format($a->amount_espees, 2) }}</td>
                        <td class="font-mono">{{ number_format($a->threshold_espees, 2) }}</td>
                        <td>
                            @if ($a->acknowledged)
                                <span class="badge">Acknowledged</span>
                            @else
                                <span class="badge bg-accent/20 text-accent">New</span>
                            @endif
                        </td>
                        <td>
                            @unless ($a->acknowledged)
                                <form method="POST" action="{{ route('alerts.acknowledge', $a) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn-ghost text-xs">Acknowledge</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-muted-foreground">No alerts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection