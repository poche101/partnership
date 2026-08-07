@extends('layouts.app')
@section('title', 'Churches')
@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl text-primary">Churches</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                @if (auth()->user()->isZoneAdmin())
                    All churches, across every group.
                @else
                    Churches in your group.
                @endif
            </p>
        </div>
        <button data-open-modal="new-church" class="btn-primary">+ New Church</button>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-md border border-border bg-muted/40 px-4 py-3 text-sm text-primary">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>Church</th>
                    @if (auth()->user()->isZoneAdmin())
                        <th>Group</th>
                    @endif
                    <th>Category</th>
                    <th>Pastor</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>KingsChat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($churches as $church)
                    <tr>
                        <td class="font-medium">{{ $church->name }}</td>
                        @if (auth()->user()->isZoneAdmin())
                            <td>{{ $church->groupChurch?->name ?? '—' }}</td>
                        @endif
                        <td>{{ $church->category ?? '—' }}</td>
                        <td>{{ $church->pastor_name ?? '—' }}</td>
                        <td>{{ $church->pastor_email ?? '—' }}</td>
                        <td>{{ $church->pastor_phone ?? '—' }}</td>
                        <td>{{ $church->pastor_kingschat ?? '—' }}</td>
                        <td class="text-right">
                            @if (auth()->user()->isChurchAdmin() && auth()->user()->church_id === $church->id)
                                <a href="{{ route('church.pastor.edit') }}" class="text-sm font-medium text-primary hover:underline">Edit pastor info</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-muted-foreground">No churches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="new-church" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="card w-full max-w-md p-6">
        <h2 class="font-display text-lg text-primary">New Church</h2>
        <form method="POST" action="{{ route('churches.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="field-label">Church name</label>
                <input name="church_name" required class="field-input">
            </div>
            <div>
                <label class="field-label">Category</label>
                <select name="category" class="field-input">
                    <option value="">— None —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth()->user()->isZoneAdmin())
                <div>
                    <label class="field-label">Group church</label>
                    <select name="group_id" required class="field-input">
                        <option value="">Select a group…</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="border-t border-border pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Church Admin</p>
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
                <button type="button" data-close-modal="new-church" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
