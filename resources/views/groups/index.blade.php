@extends('layouts.app')
@section('title', 'Group Churches')
@section('content')
@php
    $total = method_exists($groups, 'total') ? $groups->total() : $groups->count();
@endphp

<style>
    .search-form { display: flex; align-items: center; gap: 8px; margin-top: 24px; }
    .search-wrap { position: relative; flex: 1; max-width: 36rem; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #6b7280; pointer-events: none; }
    .search-wrap input { width: 100%; height: 44px; padding: 0 12px 0 38px; border: 1px solid #e3ddd0; border-radius: 8px; background: #fff; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.05); outline: none; box-sizing: border-box; }
    .search-wrap input:focus { border-color: #1a2340; box-shadow: 0 0 0 3px rgba(26,35,64,.15); }
    .search-clear { font-size: 14px; color: #6b7280; }
    .search-clear:hover { text-decoration: underline; }

    .gc-modal { position: fixed; inset: 0; z-index: 50; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,.4); padding: 16px; }
    .gc-modal.is-open { display: flex; }
    .gc-modal-box { width: 100%; max-width: 28rem; max-height: 90vh; overflow-y: auto; padding: 24px; }
    .gc-modal-box.sm { max-width: 24rem; }
    .gc-divider { border-top: 1px solid #e3ddd0; margin-top: 16px; padding-top: 16px; }
    .gc-btn-danger { display: inline-flex; align-items: center; border-radius: 6px; background: #dc2626; color: #fff; padding: 8px 16px; font-size: 14px; font-weight: 500; }
    .gc-btn-danger:hover { background: #b91c1c; }
    .gc-link { font-size: 14px; font-weight: 500; }
    .gc-link:hover { text-decoration: underline; }

    .pw-wrap { position: relative; }
    .pw-wrap .field-input { padding-right: 40px; }
    .pw-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; padding: 4px; background: none; border: 0; cursor: pointer; color: #6b7280; }
    .pw-toggle:hover { color: #1a2340; }
    .pw-toggle:focus-visible { outline: 2px solid #1a2340; outline-offset: 2px; border-radius: 4px; }
</style>

<div class="mx-auto max-w-6xl px-6 py-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl text-primary">Group Churches</h1>
            <p class="mt-1 text-sm text-muted-foreground">All groups and the churches under each one.</p>
        </div>
        <button data-open-modal="new-group" class="btn-primary">+ New Group Church</button>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('groups.index') }}" class="search-form" role="search">
        <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search group churches…">
        </div>
        <button type="submit" class="btn-primary">Search</button>
        @if (request()->filled('q'))
            <a href="{{ route('groups.index') }}" class="search-clear">Clear</a>
        @endif
    </form>
    @if (request()->filled('q'))
        <p class="mt-2 text-xs text-muted-foreground">
            {{ $total }} {{ \Illuminate\Support\Str::plural('result', $total) }} for
            <span class="font-medium text-primary">"{{ request('q') }}"</span>
        </p>
    @endif

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
            <thead><tr><th>Name</th><th>Churches</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @forelse ($groups as $g)
                    @php $admin = $admins->get($g->id); @endphp
                    <tr>
                        <td class="font-medium">{{ $g->name }}</td>
                        <td>{{ $g->churches_count }}</td>
                        <td>{{ $g->created_at->format('M j, Y') }}</td>
                        <td class="whitespace-nowrap">
                            <div class="flex items-center justify-end gap-3 text-primary">
                                <button type="button" class="gc-link" title="Edit group church"
                                    data-edit-group
                                    data-action="{{ route('groups.update', $g) }}"
                                    data-name="{{ $g->name }}"
                                    data-admin-name="{{ $admin->name ?? '' }}"
                                    data-admin-email="{{ $admin->email ?? '' }}">Edit</button>
                                <button type="button" class="gc-link" title="Delete group church"
                                    data-delete-group
                                    data-action="{{ route('groups.destroy', $g) }}"
                                    data-name="{{ $g->name }}"
                                    data-churches="{{ $g->churches_count }}">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-muted-foreground">
                            @if (request()->filled('q'))
                                No group churches match "{{ request('q') }}".
                            @else
                                No group churches yet.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($groups, 'hasPages') && $groups->hasPages())
        <div class="mt-4">{{ $groups->withQueryString()->links() }}</div>
    @endif
</div>

{{-- New group modal --}}
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
                        <div class="pw-wrap">
                            <input type="password" name="admin_password" required minlength="8" autocomplete="new-password" class="field-input">
                            <button type="button" class="pw-toggle" data-toggle-password aria-label="Show password" title="Show password">
                                <svg class="pw-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg class="pw-eye-off" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
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

{{-- Edit group modal --}}
<div id="edit-group" class="gc-modal">
    <div class="card gc-modal-box">
        <h2 class="font-display text-lg text-primary">Edit Group Church</h2>
        <form id="edit-group-form" method="POST" action="" class="mt-4">
            @csrf
            @method('PUT')
            <div>
                <label class="field-label">Group church name</label>
                <input name="group_name" id="edit-group-name" required class="field-input">
            </div>
            <div class="gc-divider">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Group Admin Login</p>
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="field-label">Full name</label>
                        <input name="admin_full_name" id="edit-admin-name" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="admin_email" id="edit-admin-email" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">New password</label>
                        <div class="pw-wrap">
                            <input type="password" name="admin_password" id="edit-admin-password" minlength="8" autocomplete="new-password" class="field-input" placeholder="Leave blank to keep current password">
                            <button type="button" class="pw-toggle" data-toggle-password aria-label="Show password" title="Show password">
                                <svg class="pw-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg class="pw-eye-off" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" data-dismiss-modal="edit-group" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div id="delete-group" class="gc-modal">
    <div class="card gc-modal-box sm">
        <h2 class="font-display text-lg text-primary" id="delete-group-title">Delete group church?</h2>

        <div id="delete-group-blocked" style="display:none">
            <p class="mt-2 text-sm text-muted-foreground">
                <span id="delete-group-blocked-name" class="font-semibold text-primary"></span>
                still has <span id="delete-group-count"></span> church(es), so it can't be deleted yet.
                Delete the churches or move them to another group first.
            </p>
            <div class="mt-6 flex justify-end">
                <button type="button" data-dismiss-modal="delete-group" class="btn-outline">Close</button>
            </div>
        </div>

        <div id="delete-group-confirm">
            <p class="mt-2 text-sm text-muted-foreground">
                You are about to delete <span id="delete-group-name" class="font-semibold text-primary"></span>.
                This will also remove its group admin login. This action cannot be undone.
            </p>
            <form id="delete-group-form" method="POST" action="" class="mt-6 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" data-dismiss-modal="delete-group" class="btn-outline">Cancel</button>
                <button type="submit" class="gc-btn-danger">Yes, delete</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modals = ['edit-group', 'delete-group'];

    // Password show/hide
    const pwToggles = document.querySelectorAll('[data-toggle-password]');
    const setPasswordVisible = (btn, visible) => {
        const input = btn.closest('.pw-wrap').querySelector('input');
        input.type = visible ? 'text' : 'password';
        btn.querySelector('.pw-eye').style.display = visible ? 'none' : '';
        btn.querySelector('.pw-eye-off').style.display = visible ? '' : 'none';
        const label = visible ? 'Hide password' : 'Show password';
        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
    };
    const resetPasswords = () => pwToggles.forEach((btn) => setPasswordVisible(btn, false));

    pwToggles.forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.pw-wrap').querySelector('input');
            setPasswordVisible(btn, input.type === 'password');
        });
    });
    // Hide the password again when the new-group modal is cancelled
    document.querySelectorAll('[data-close-modal="new-group"]').forEach((btn) => {
        btn.addEventListener('click', resetPasswords);
    });

    const open = (id) => document.getElementById(id).classList.add('is-open');
    const close = (id) => {
        document.getElementById(id).classList.remove('is-open');
        resetPasswords();
    };

    // Edit
    document.querySelectorAll('[data-edit-group]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-group-form').action = btn.dataset.action;
            document.getElementById('edit-group-name').value = btn.dataset.name ?? '';
            document.getElementById('edit-admin-name').value = btn.dataset.adminName ?? '';
            document.getElementById('edit-admin-email').value = btn.dataset.adminEmail ?? '';
            const pw = document.getElementById('edit-admin-password');
            pw.value = '';
            // A group with no admin yet must get a password so one can be created.
            const hasAdmin = (btn.dataset.adminEmail ?? '') !== '';
            pw.required = !hasAdmin;
            pw.placeholder = hasAdmin ? 'Leave blank to keep current password' : 'Set a password for the new admin';
            resetPasswords();
            open('edit-group');
        });
    });

    // Delete
    document.querySelectorAll('[data-delete-group]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const count = parseInt(btn.dataset.churches || '0', 10);
            const blocked = count > 0;
            document.getElementById('delete-group-blocked').style.display = blocked ? '' : 'none';
            document.getElementById('delete-group-confirm').style.display = blocked ? 'none' : '';
            document.getElementById('delete-group-title').textContent = blocked ? 'Can’t delete yet' : 'Delete group church?';
            document.getElementById('delete-group-blocked-name').textContent = btn.dataset.name;
            document.getElementById('delete-group-count').textContent = count;
            document.getElementById('delete-group-name').textContent = btn.dataset.name;
            document.getElementById('delete-group-form').action = btn.dataset.action;
            open('delete-group');
        });
    });

    // Close: cancel buttons, backdrop click, Escape
    document.querySelectorAll('[data-dismiss-modal]').forEach((btn) => {
        btn.addEventListener('click', () => close(btn.dataset.dismissModal));
    });
    modals.forEach((id) => {
        document.getElementById(id).addEventListener('click', (e) => {
            if (e.target.id === id) close(id);
        });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') modals.forEach(close);
    });
})();
</script>
@endsection