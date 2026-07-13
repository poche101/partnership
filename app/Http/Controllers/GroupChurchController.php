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
    public function index()
    {
        $groups = GroupChurch::withCount('churches')->latest()->get();

        return view('groups.index', ['groups' => $groups]);
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
}
