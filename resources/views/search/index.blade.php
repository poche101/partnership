@extends('layouts.app')
@section('title', 'Search')
@section('content')
<div class="mx-auto max-w-4xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Semantic Search</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        Search partners using natural language — e.g. "partners in Lagos who gave to Rhapsody last month".
        Falls back to plain text search if no AI key is configured.
    </p>

    <form method="POST" action="{{ route('search.run') }}" class="mt-6 flex gap-2">
        @csrf
        <input type="text" name="query" required value="{{ $query }}" placeholder="Describe who you're looking for…" class="field-input">
        <button type="submit" class="btn-primary">Search</button>
    </form>

    @if ($ran)
        <div class="table-shell card mt-6 overflow-x-auto">
            <table>
                <thead><tr><th>Name</th><th>Church</th><th>Group</th><th>Email</th><th>Phone</th></tr></thead>
                <tbody>
                    @forelse ($results as $p)
                        <tr>
                            <td class="font-medium">{{ $p->fullName() }}</td>
                            <td>{{ $p->church?->name }}</td>
                            <td>{{ $p->church?->groupChurch?->name }}</td>
                            <td>{{ $p->email ?: '—' }}</td>
                            <td>{{ $p->phone ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-muted-foreground">No matches found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
