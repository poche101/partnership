@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Audit Logs</h1>
    <p class="mt-1 text-sm text-muted-foreground">System-wide activity trail (latest 500 events).</p>

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap">{{ $log->created_at->format('M j, Y g:ia') }}</td>
                        <td>{{ $log->actor_email ?? '—' }}</td>
                        <td><span class="badge">{{ $log->action }}</span></td>
                        <td>{{ $log->entity_type }}@if($log->entity_id) #{{ $log->entity_id }}@endif</td>
                        <td class="max-w-xs truncate font-mono text-xs" title="{{ json_encode($log->details) }}">{{ json_encode($log->details) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-muted-foreground">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
