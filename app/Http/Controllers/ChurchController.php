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

    public function index(Request $request)
    {
        $user = Auth::user();
        $q = trim((string) $request->query('q', ''));

        $query = Church::with('groupChurch')->latest();

        if ($user->isGroupAdmin()) {
            $query->where('group_church_id', $user->group_church_id);
        }

        if ($q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhere('pastor_name', 'like', $like)
                    ->orWhere('pastor_email', 'like', $like)
                    ->orWhere('pastor_phone', 'like', $like)
                    ->orWhere('pastor_kingschat', 'like', $like)
                    ->orWhereHas('groupChurch', fn ($g) => $g->where('name', 'like', $like));
            });
        }

        $groups = $user->isZoneAdmin() ? GroupChurch::orderBy('name')->get() : collect();

        return view('churches.index', [
            'churches' => $query->paginate(25)->withQueryString(),
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

    public function update(Request $request, Church $church)
    {
        $user = Auth::user();
        $this->authorizeManage($user, $church);

        $data = $request->validate([
            'church_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', Rule::in(self::CATEGORIES)],
            'group_id' => [$user->isZoneAdmin() ? 'required' : 'nullable', 'exists:group_churches,id'],
            'pastor_name' => ['nullable', 'string', 'max:255'],
            'pastor_email' => ['nullable', 'email', 'max:255'],
            'pastor_phone' => ['nullable', 'string', 'max:50'],
            'pastor_kingschat' => ['nullable', 'string', 'max:100'],
        ]);

        // Only zone admins may move a church to another group.
        $groupId = $user->isZoneAdmin() ? $data['group_id'] : $church->group_church_id;
        $oldGroupId = $church->group_church_id;

        DB::transaction(function () use ($church, $data, $groupId, $oldGroupId) {
            $church->update([
                'name' => $data['church_name'],
                'category' => $data['category'] ?? null,
                'group_church_id' => $groupId,
                'pastor_name' => $data['pastor_name'] ?? null,
                'pastor_email' => $data['pastor_email'] ?? null,
                'pastor_phone' => $data['pastor_phone'] ?? null,
                'pastor_kingschat' => $data['pastor_kingschat'] ?? null,
            ]);

            // Keep the church admin(s) in the same group as the church.
            if ((int) $oldGroupId !== (int) $groupId) {
                User::where('church_id', $church->id)->update(['group_church_id' => $groupId]);
            }

            AuditLogger::log(Auth::user(), 'church.updated', 'church', $church->id, [
                'church_name' => $church->name,
            ]);
        });

        return redirect()->route('churches.index')->with('success', 'Church updated.');
    }

    public function destroy(Church $church)
    {
        $user = Auth::user();
        $this->authorizeManage($user, $church);

        $name = $church->name;
        $id = $church->id;

        DB::transaction(function () use ($church) {
            // Remove the church's admin login(s) so no orphaned accounts remain.
            User::where('church_id', $church->id)->where('role', 'church_admin')->delete();
            $church->delete();
        });

        AuditLogger::log($user, 'church.deleted', 'church', $id, ['church_name' => $name]);

        return redirect()->route('churches.index')->with('success', "{$name} deleted.");
    }

    /**
     * Zone admins can manage any church; group admins only churches in their own group.
     */
    private function authorizeManage($user, Church $church): void
    {
        abort_unless($user->isZoneAdmin() || $user->isGroupAdmin(), 403);

        if ($user->isGroupAdmin()) {
            abort_unless((int) $church->group_church_id === (int) $user->group_church_id, 403);
        }
    }
}