<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\GroupChurch;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ChurchController extends Controller
{
    const CATEGORIES = ['Category A', 'Category B', 'Category C', 'Category D'];

    public function index()
    {
        $user = Auth::user();

        $query = Church::with('groupChurch')->latest();
        if ($user->isGroupAdmin()) {
            $query->where('group_church_id', $user->group_church_id);
        }

        $groups = $user->isZoneAdmin() ? GroupChurch::orderBy('name')->get() : collect();

        return view('churches.index', [
            'churches' => $query->get(),
            'groups' => $groups,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isZoneAdmin() || $user->isGroupAdmin(), 403);

        $data = $request->validate([
            'church_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', Rule::in(self::CATEGORIES)],
            'admin_full_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8'],
            'group_id' => ['nullable', 'exists:group_churches,id'],
        ]);

        $groupId = $user->isZoneAdmin() ? $data['group_id'] : $user->group_church_id;
        abort_if(! $groupId, 422, 'A group church is required.');

        DB::transaction(function () use ($data, $groupId) {
            $church = Church::create([
                'name' => $data['church_name'],
                'category' => $data['category'] ?? null,
                'group_church_id' => $groupId,
            ]);

            $admin = User::create([
                'name' => $data['admin_full_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role' => 'church_admin',
                'group_church_id' => $groupId,
                'church_id' => $church->id,
            ]);

            AuditLogger::log(Auth::user(), 'admin.created', 'church_admin', $admin->id, [
                'church_id' => $church->id, 'church_name' => $church->name, 'email' => $admin->email,
            ]);
        });

        return back()->with('success', 'Church created.');
    }
}
