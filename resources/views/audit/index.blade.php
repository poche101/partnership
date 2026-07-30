@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Audit Logs</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        @if(request()->filled('entity_type') || request()->filled('entity_id'))
            Filtered activity trail.
            <a href="{{ route('audit.index') }}" class="underline">Clear filter</a>
        @else
            System-wide activity trail.
        @endif
    </p>

    <form method="GET" action="{{ route('audit.index') }}" class="mt-4 flex gap-2">
        @if(request()->filled('entity_type'))
            <input type="hidden" name="entity_type" value="{{ request('entity_type') }}">
        @endif
        @if(request()->filled('entity_id'))
            <input type="hidden" name="entity_id" value="{{ request('entity_id') }}">
        @endif
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search by actor, action, or partner…"
            class="field-input flex-1"
        >
        <button type="submit" class="btn-primary">Search</button>
        @if(request()->filled('q'))
            <a href="{{ route('audit.index', request()->except('q')) }}" class="btn-outline">Clear</a>
        @endif
    </form>

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
                                        <span class="text-primary">+{{ number_format($change['added'], 2) }}</span>
                                        &rarr; {{ number_format($change['after'], 2) }} ESPEES
                                    </li>
                                @endforeach
                            </ul>
                        @endif
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
                @if(request()->filled('q'))
                    No activity matches "{{ request('q') }}".
                @else
                    No activity recorded yet.
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</div>
@endsection