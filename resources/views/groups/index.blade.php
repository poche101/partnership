@extends('layouts.app')
@section('title', 'Group Churches')
@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl text-primary">Group Churches</h1>
            <p class="mt-1 text-sm text-muted-foreground">All groups and the churches under each one.</p>
        </div>
        <button data-open-modal="new-group" class="btn-primary">+ New Group Church</button>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-md border border-border bg-muted/40 px-4 py-3 text-sm text-primary">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Name</th><th>Churches</th><th>Created</th></tr></thead>
            <tbody>
                @forelse ($groups as $g)
                    <tr>
                        <td class="font-medium">{{ $g->name }}</td>
                        <td>{{ $g->churches_count }}</td>
                        <td>{{ $g->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-muted-foreground">No group churches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="new-group" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="card w-full max-w-md p-6">
        <h2 class="font-display text-lg text-primary">New Group Church</h2>
        <form method="POST" action="{{ route('groups.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="field-label">Group church name</label>
                <input name="group_name" required class="field-input" placeholder="e.g. CE Lagos Zone 5">
            </div>
            <div class="border-t border-border pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Group Admin</p>
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="field-label">Full name</label>
                        <input name="admin_full_name" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="admin_email" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Password</label>
                        <input type="password" name="admin_password" required minlength="8" class="field-input">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal="new-group" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
