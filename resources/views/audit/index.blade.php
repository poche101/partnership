@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
    <h1 class="font-display text-xl sm:text-2xl text-primary">Audit Logs</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        @if(request()->filled('entity_type') || request()->filled('entity_id'))
            Filtered activity trail.
            <a href="{{ route('audit.index') }}" class="underline">Clear filter</a>
        @else
            System-wide activity trail.
        @endif
    </p>

    <form method="GET" action="{{ route('audit.index') }}" class="mt-4 flex flex-wrap items-end gap-2">
        @if(request()->filled('entity_type'))
            <input type="hidden" name="entity_type" value="{{ request('entity_type') }}">
        @endif
        @if(request()->filled('entity_id'))
            <input type="hidden" name="entity_id" value="{{ request('entity_id') }}">
        @endif

        <div class="flex flex-1 min-w-[200px] flex-col">
            <label for="q" class="mb-1 text-xs text-muted-foreground">Search</label>
            <input
                type="text"
                name="q"
                id="q"
                value="{{ request('q') }}"
                placeholder="Actor, action, partner, or arm…"
                class="field-input"
            >
        </div>

        <div class="flex flex-col">
            <label for="date_from" class="mb-1 text-xs text-muted-foreground">From</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                class="field-input">
        </div>

        <div class="flex flex-col">
            <label for="date_to" class="mb-1 text-xs text-muted-foreground">To</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="field-input">
        </div>

        <button type="submit" class="btn-primary">Search</button>

        @if(request()->filled('q') || request()->filled('date_from') || request()->filled('date_to'))
            <a href="{{ route('audit.index', request()->only(['entity_type', 'entity_id'])) }}"
                class="btn-outline">Clear</a>
        @endif
    </form>

    @if(request()->filled('q') || request()->filled('date_from') || request()->filled('date_to'))
        @php
            $resultCount = method_exists($logs, 'total') ? $logs->total() : $logs->count();
        @endphp
        <p class="mt-4 text-sm text-muted-foreground">
            <strong class="text-foreground">{{ number_format($resultCount) }}</strong>
            {{ Str::plural('result', $resultCount) }}
            @if(request()->filled('q'))
                for "<strong class="text-foreground">{{ request('q') }}</strong>"
            @endif
            @if(request()->filled('date_from') || request()->filled('date_to'))
                between
                <strong class="text-foreground">{{ request('date_from') ?: 'the start' }}</strong>
                and
                <strong class="text-foreground">{{ request('date_to') ?: 'now' }}</strong>
            @endif
        </p>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($logs as $log)
            <div class="card p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="badge">{{ str(str_replace('.', ' ', $log->action))->headline() }}</span>
                        <span class="text-sm text-muted-foreground">{{ $log->actor_email ?? 'System' }}</span>
                    </div>
                    <span class="text-xs text-muted-foreground whitespace-nowrap">
                        {{ $log->created_at->format('M j, Y g:ia') }}
                    </span>
                </div>

                <div class="mt-2 text-sm">
                    @if($log->action === 'giving.recorded')
                        @php
                            $d = $log->details ?? [];
                            $hasGranularSpouse = !empty($d['spouse_first_name']);

                            if ($hasGranularSpouse) {
                                $surname = $d['spouse_last_name'] ?? $d['partner_last_name'] ?? '';
                                $displayName = collect([
                                    $d['partner_title'] ?? null,
                                    $d['partner_first_name'] ?? null,
                                    $d['spouse_title'] ?? null,
                                    $d['spouse_first_name'] ?? null,
                                    $surname ?: null,
                                ])->filter()->implode(', ');
                            } elseif (!empty($d['spouse_name'])) {
                                // Legacy entries: only flat name strings available
                                $displayName = ($d['partner'] ?? 'Unknown partner').' & '.$d['spouse_name'];
                            } else {
                                $displayName = $d['partner'] ?? 'Unknown partner';
                            }
                        @endphp
                        <p>
                            Recorded a gift from <strong>{{ $displayName }}</strong>.
                        </p>
                        @if(!empty($d['changes']))
                            <ul class="mt-2 space-y-1 text-muted-foreground">
                                @foreach ($d['changes'] as $arm => $change)
                                    <li>
                                        <span class="font-medium text-foreground">{{ \App\Support\Arms::label($arm) }}:</span>
                                        {{ number_format($change['before'], 2) }}
                                        @if(array_key_exists('added', $change))
                                            <span class="text-primary">+{{ number_format($change['added'], 2) }}</span>
                                        @endif
                                        &rarr; {{ number_format($change['after'], 2) }} ESPEES
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif($log->action === 'giving.updated')
                        @php $d = $log->details ?? []; @endphp
                        <p>
                            Updated a giving record for <strong>{{ $d['partner'] ?? 'Unknown partner' }}</strong>.
                        </p>
                        @if(!empty($d['changes']))
                            <ul class="mt-2 space-y-1 text-muted-foreground">
                                @foreach ($d['changes'] as $arm => $change)
                                    <li>
                                        <span class="font-medium text-foreground">{{ \App\Support\Arms::label($arm) }}:</span>
                                        {{ number_format($change['before'], 2) }}
                                        &rarr; {{ number_format($change['after'], 2) }} ESPEES
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif($log->action === 'giving.deleted')
                        @php $d = $log->details ?? []; @endphp
                        <p>
                            Deleted a giving record for <strong>{{ $d['partner'] ?? 'Unknown partner' }}</strong>
                            @if(isset($d['total_espees']))
                                totaling {{ number_format($d['total_espees'], 2) }} ESPEES
                            @endif
                            .
                        </p>
                    @else
                        <p class="text-muted-foreground">
                            {{ $log->entity_type ? class_basename($log->entity_type) : 'Entity' }}
                            @if($log->entity_id) #{{ $log->entity_id }} @endif
                        </p>
                        @if(!empty($log->details))
                            <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-muted-foreground sm:grid-cols-3">
                                @foreach ($log->details as $key => $value)
                                    @if(!is_array($value))
                                        <div>
                                            <dt class="font-medium text-foreground">{{ str($key)->headline() }}</dt>
                                            <dd>{{ $value }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="card p-6 text-center text-muted-foreground">
                @if(request()->filled('q') || request()->filled('date_from') || request()->filled('date_to'))
                    No activity matches your filters.
                @else
                    No activity recorded yet.
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
@endsection