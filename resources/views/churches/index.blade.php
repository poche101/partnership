@extends('layouts.app')
@section('title', 'Churches')
@section('content')
@php
    $user = auth()->user();
    $isZone = $user->isZoneAdmin();
    // Zone admins and group admins can manage churches; church admins only edit their own pastor info.
    $canManage = ! $user->isChurchAdmin();
    $colCount = ($isZone ? 8 : 7);
@endphp
<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl text-primary">Churches</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                @if ($isZone)
                    All churches, across every group.
                @else
                    Churches in your group.
                @endif
            </p>
        </div>
        <button data-open-modal="new-church" class="btn-primary">+ New Church</button>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('churches.index') }}" class="mt-6 flex items-center gap-2">
        <div class="relative w-full max-w-md">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by church, pastor, email, phone{{ $isZone ? ', group' : '' }}…" class="field-input pl-9">
        </div>
        <button type="submit" class="btn-primary">Search</button>
        @if (request()->filled('q'))
            <a href="{{ route('churches.index') }}" class="btn-outline">Clear</a>
        @endif
    </form>

    @if (session('success'))
        <div class="mt-4 rounded-md border border-border bg-muted/40 px-4 py-3 text-sm text-primary">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>Church</th>
                    @if ($isZone)
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
                        @if ($isZone)
                            <td>{{ $church->groupChurch?->name ?? '—' }}</td>
                        @endif
                        <td>{{ $church->category ?? '—' }}</td>
                        <td>{{ $church->pastor_name ?? '—' }}</td>
                        <td>{{ $church->pastor_email ?? '—' }}</td>
                        <td>{{ $church->pastor_phone ?? '—' }}</td>
                        <td>{{ $church->pastor_kingschat ?? '—' }}</td>
                        <td class="whitespace-nowrap text-right">
                            @if ($canManage)
                                <button type="button"
                                    class="text-sm font-medium text-primary hover:underline"
                                    data-edit-church
                                    data-action="{{ route('churches.update', $church) }}"
                                    data-name="{{ $church->name }}"
                                    data-category="{{ $church->category }}"
                                    data-group-id="{{ $church->group_id }}"
                                    data-pastor-name="{{ $church->pastor_name }}"
                                    data-pastor-email="{{ $church->pastor_email }}"
                                    data-pastor-phone="{{ $church->pastor_phone }}"
                                    data-pastor-kingschat="{{ $church->pastor_kingschat }}">Edit</button>
                                <button type="button"
                                    class="ml-3 text-sm font-medium text-red-600 hover:underline"
                                    data-delete-church
                                    data-action="{{ route('churches.destroy', $church) }}"
                                    data-name="{{ $church->name }}">Delete</button>
                            @elseif ($user->isChurchAdmin() && $user->church_id === $church->id)
                                <a href="{{ route('church.pastor.edit') }}" class="text-sm font-medium text-primary hover:underline">Edit pastor info</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colCount }}" class="py-6 text-center text-muted-foreground">
                            @if (request()->filled('q'))
                                No churches match "{{ request('q') }}".
                            @else
                                No churches yet.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($churches, 'hasPages') && $churches->hasPages())
        <div class="mt-4">{{ $churches->withQueryString()->links() }}</div>
    @endif
</div>

{{-- New church modal --}}
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
            @if ($isZone)
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

{{-- Edit church modal --}}
<div id="edit-church" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="card max-h-[90vh] w-full max-w-md overflow-y-auto p-6">
        <h2 class="font-display text-lg text-primary">Edit Church</h2>
        <form id="edit-church-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="field-label">Church name</label>
                <input name="church_name" id="edit-church-name" required class="field-input">
            </div>
            <div>
                <label class="field-label">Category</label>
                <select name="category" id="edit-church-category" class="field-input">
                    <option value="">— None —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @if ($isZone)
                <div>
                    <label class="field-label">Group church</label>
                    <select name="group_id" id="edit-church-group" required class="field-input">
                        <option value="">Select a group…</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="border-t border-border pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Pastor</p>
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="field-label">Name</label>
                        <input name="pastor_name" id="edit-pastor-name" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="pastor_email" id="edit-pastor-email" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input name="pastor_phone" id="edit-pastor-phone" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">KingsChat</label>
                        <input name="pastor_kingschat" id="edit-pastor-kingschat" class="field-input">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-dismiss-modal="edit-church" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div id="delete-church" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="card w-full max-w-sm p-6">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
        </div>
        <h2 class="mt-4 font-display text-lg text-primary">Delete church?</h2>
        <p class="mt-2 text-sm text-muted-foreground">
            You are about to delete <span id="delete-church-name" class="font-semibold text-primary"></span>.
            This action cannot be undone.
        </p>
        <form id="delete-church-form" method="POST" action="" class="mt-6 flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" data-dismiss-modal="delete-church" class="btn-outline">Cancel</button>
            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Yes, delete</button>
        </form>
    </div>
</div>

<script>
(function () {
    const show = (id) => {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
    };
    const hide = (id) => {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    };
    const set = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.value = value ?? '';
    };

    // Edit
    document.querySelectorAll('[data-edit-church]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('edit-church-form').action = d.action;
            set('edit-church-name', d.name);
            set('edit-church-category', d.category);
            set('edit-church-group', d.groupId);
            set('edit-pastor-name', d.pastorName);
            set('edit-pastor-email', d.pastorEmail);
            set('edit-pastor-phone', d.pastorPhone);
            set('edit-pastor-kingschat', d.pastorKingschat);
            show('edit-church');
        });
    });

    // Delete
    document.querySelectorAll('[data-delete-church]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('delete-church-form').action = btn.dataset.action;
            document.getElementById('delete-church-name').textContent = btn.dataset.name;
            show('delete-church');
        });
    });

    // Close: cancel buttons, backdrop click, Escape
    document.querySelectorAll('[data-dismiss-modal]').forEach((btn) => {
        btn.addEventListener('click', () => hide(btn.dataset.dismissModal));
    });
    ['edit-church', 'delete-church'].forEach((id) => {
        document.getElementById(id).addEventListener('click', (e) => {
            if (e.target.id === id) hide(id);
        });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') ['edit-church', 'delete-church'].forEach(hide);
    });
})();
</script>
@endsection