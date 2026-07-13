@extends('layouts.app')
@section('title', 'Partners')
@section('content')
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl text-primary">Partners</h1>
            <p class="mt-1 text-sm text-muted-foreground">All partners visible within your scope.</p>
        </div>
        <button data-open-modal="new-partner" class="btn-primary">+ New Partner</button>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search name, email, KingsChat..." class="field-input max-w-sm">
        @auth
            @if (auth()->user()->isZoneAdmin())
                <label class="flex items-center gap-2 text-sm text-muted-foreground">
                    <input type="checkbox" name="ai" value="1" {{ $aiMode ? 'checked' : '' }}> AI semantic search
                </label>
            @endif
        @endauth
        <button type="submit" class="btn-outline">Search</button>
    </form>

    <div class="table-shell card mt-4 overflow-x-auto">
        <table>
            <thead><tr><th>Name</th><th>Church</th><th>Category</th><th>Phone</th><th>Email</th></tr></thead>
            <tbody>
                @forelse ($partners as $p)
                    <tr>
                        <td class="font-medium">{{ $p->fullName() }}</td>
                        <td>{{ $p->church?->name }}</td>
                        <td>{{ $p->delegate_category ?: '—' }}</td>
                        <td>{{ $p->phone ?: '—' }}</td>
                        <td>{{ $p->email ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-muted-foreground">No partners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="new-partner" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 overflow-y-auto">
    <div class="card my-8 w-full max-w-2xl p-6">
        <h2 class="font-display text-lg text-primary">New Partner</h2>
        <form method="POST" action="{{ route('partners.store') }}" class="mt-4 space-y-4">
            @csrf
            @if ($churches->count() > 1 || !auth()->user()->isChurchAdmin())
                <div>
                    <label class="field-label">Church</label>
                    <select name="church_id" required class="field-input">
                        @foreach ($churches as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="grid grid-cols-3 gap-3">
                <div><label class="field-label">Title</label><input name="title" class="field-input"></div>
                <div class="col-span-1"><label class="field-label">First name</label><input name="first_name" required class="field-input"></div>
                <div><label class="field-label">Last name</label><input name="last_name" class="field-input"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Delegate category</label>
                    <select name="delegate_category" class="field-input">
                        <option value="">—</option>
                        @foreach ($delegateCategories as $dc)
                            <option value="{{ $dc }}">{{ $dc }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="field-label">KingsChat username</label><input name="kingschat_username" class="field-input"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="field-label">Phone</label><input name="phone" class="field-input"></div>
                <div><label class="field-label">Email</label><input type="email" name="email" class="field-input"></div>
            </div>
            <details class="rounded-md border border-border p-3">
                <summary class="cursor-pointer text-sm font-medium text-foreground">Spouse details (optional)</summary>
                <div class="mt-3 grid grid-cols-3 gap-3">
                    <div><label class="field-label">Title</label><input name="spouse_title" class="field-input"></div>
                    <div class="col-span-2"><label class="field-label">First name</label><input name="spouse_first_name" class="field-input"></div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div><label class="field-label">KingsChat</label><input name="spouse_kingschat" class="field-input"></div>
                    <div><label class="field-label">Phone</label><input name="spouse_phone" class="field-input"></div>
                </div>
                <div class="mt-3"><label class="field-label">Email</label><input type="email" name="spouse_email" class="field-input"></div>
            </details>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal="new-partner" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Save Partner</button>
            </div>
        </form>
    </div>
</div>
@endsection
