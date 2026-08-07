<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChurchPastorController extends Controller
{
    /**
     * Scoped via the logged-in user's own church_id — never a route
     * param — so one church_admin can never view or edit another
     * church's pastor details.
     */
    public function edit(Request $request)
    {
        $church = $request->user()->church;

        abort_unless($church, 404, 'No church is linked to this login yet.');

        return view('churches.edit-pastor', compact('church'));
    }

    public function update(Request $request)
    {
        $church = $request->user()->church;

        abort_unless($church, 404, 'No church is linked to this login yet.');

        $data = $request->validate([
            'pastor_name' => ['required', 'string', 'max:255'],
            'pastor_email' => ['nullable', 'email', 'max:255'],
            'pastor_phone' => ['nullable', 'string', 'max:30'],
            'pastor_kingschat' => ['nullable', 'string', 'max:100'],
        ]);

        $church->update($data);

        AuditLogger::log(Auth::user(), 'church.pastor_updated', 'church', $church->id, [
            'church_id' => $church->id,
            'church_name' => $church->name,
        ]);

        return back()->with('success', 'Pastor details updated.');
    }
}
