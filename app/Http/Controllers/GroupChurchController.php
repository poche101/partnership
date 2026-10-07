<?php

namespace App\Http\Controllers;

use App\Models\GroupChurch;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GroupChurchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $groups = GroupChurch::withCount('churches')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
                $query->where('name', 'like', $like);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // One admin per group (the oldest), keyed by group id, for the edit modal.
        $admins = User::where('role', 'group_admin')
            ->whereIn('group_church_id', $groups->pluck('id'))
            ->orderBy('id')
            ->get()
            ->unique('group_church_id')
            ->keyBy('group_church_id');

        return view('groups.index', [
            'groups' => $groups,
            'admins' => $admins,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'admin_full_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($data) {
            $group = GroupChurch::create(['name' => $data['group_name']]);

            $admin = User::create([
                'name' => $data['admin_full_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role' => 'group_admin',
                'group_church_id' => $group->id,
            ]);

            AuditLogger::log(Auth::user(), 'admin.created', 'group_admin', $admin->id, [
                'group_id' => $group->id, 'group_name' => $group->name, 'email' => $admin->email,
            ]);
        });

        return back()->with('success', 'Group church created.');
    }

    public function update(Request $request, GroupChurch $group)
    {
        abort_unless(Auth::user()->isZoneAdmin(), 403);

        $admin = User::where('group_church_id', $group->id)
            ->where('role', 'group_admin')
            ->orderBy('id')
            ->first();

        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'admin_full_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin?->id)],
            // Optional when an admin already exists (blank = keep current password).
            'admin_password' => [$admin ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($group, $admin, $data) {
            $group->update(['name' => $data['group_name']]);

            if ($admin) {
                $admin->name = $data['admin_full_name'];
                $admin->email = $data['admin_email'];

                if (!empty($data['admin_password'])) {
                    $admin->password = Hash::make($data['admin_password']);
                }

                $admin->save();
            } else {
                $admin = User::create([
                    'name' => $data['admin_full_name'],
                    'email' => $data['admin_email'],
                    'password' => Hash::make($data['admin_password']),
                    'role' => 'group_admin',
                    'group_church_id' => $group->id,
                ]);
            }

            AuditLogger::log(Auth::user(), 'group.updated', 'group_church', $group->id, [
                'group_name' => $group->name,
                'admin_email' => $admin->email,
                'password_changed' => !empty($data['admin_password']),
            ]);
        });

        return redirect()->route('groups.index')->with('success', 'Group church updated.');
    }

    public function destroy(GroupChurch $group)
    {
        abort_unless(Auth::user()->isZoneAdmin(), 403);

        if ($group->churches()->exists()) {
            return redirect()->route('groups.index')
                ->withErrors(['group' => "{$group->name} still has churches. Delete or move them to another group first."]);
        }

        $name = $group->name;
        $id = $group->id;

        DB::transaction(function () use ($group) {
            // Remove the group's admin login(s) so no orphaned accounts remain.
            User::where('group_church_id', $group->id)->where('role', 'group_admin')->delete();
            $group->delete();
        });

        AuditLogger::log(Auth::user(), 'group.deleted', 'group_church', $id, ['group_name' => $name]);

        return redirect()->route('groups.index')->with('success', "{$name} deleted.");
    }
}