@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Dashboard</h1>
    <p class="mt-1 text-sm text-muted-foreground">Overview of partnership giving within your scope.</p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Total Giving</div>
            <div class="mt-2 font-display text-2xl text-primary">{{ number_format($total, 2) }} <span class="text-sm font-sans text-muted-foreground">ESPEES</span></div>
        </div>
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Partners</div>
            <div class="mt-2 font-display text-2xl text-primary">{{ number_format($countPartners) }}</div>
        </div>
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Churches</div>
            <div class="mt-2 font-display text-2xl text-primary">{{ number_format($countChurches) }}</div>
        </div>
        <div class="card p-5">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">Group Churches</div>
            <div class="mt-2 font-display text-2xl text-primary">{{ number_format($countGroups) }}</div>
        </div>
    </div>

    @if (count($series))
    <div class="card mt-6 p-5">
        <h2 class="font-display text-lg text-primary">Giving trend (last 30 days)</h2>
        <canvas id="trendChart" class="mt-4" height="90"></canvas>
    </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h2 class="font-display text-lg text-primary">Giving by arm</h2>
            <div class="mt-4 space-y-3">
                @foreach ($arms as $arm)
                    @php $amt = $armTotals[$arm['key']] ?? 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm"><span>{{ $arm['label'] }}</span><span class="font-mono">{{ number_format($amt, 2) }}</span></div>
                        <div class="mt-1 h-1.5 w-full rounded-full bg-muted">
                            <div class="h-1.5 rounded-full bg-accent" style="width: {{ $total > 0 ? min(100, round($amt / $total * 100)) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-display text-lg text-primary">Top churches</h2>
            <div class="mt-4 space-y-2">
                @forelse ($top as $c)
                    <div class="flex items-center justify-between border-b border-border py-2 text-sm last:border-0">
                        <span>{{ $c['name'] }}</span>
                        <span class="font-mono">{{ number_format($c['total'], 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-muted-foreground">No giving recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if (count($series))
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('trendChart');
        if (!ctx || typeof window.Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json(collect($series)->pluck('date')),
                datasets: [{
                    label: 'ESPEES',
                    data: @json(collect($series)->pluck('total')),
                    borderColor: '#B98D4C',
                    backgroundColor: 'rgba(185,141,76,0.15)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    });
</script>
@endpush
@endif
@endsection