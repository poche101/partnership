<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\Arms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = AuditLog::latest('created_at');

        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->query('entity_type'));
        }
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->query('entity_id'));
        }

        if ($request->filled('q')) {
            $search = $request->query('q');

            // If the search term matches an arm's label (e.g. "Building Fund"),
            // also search for its underlying key (e.g. "partnership_a"), since
            // that's what's actually stored in the `changes` JSON.
            $matchingArmKeys = collect(Arms::enabled())
                ->filter(fn ($arm) => str_contains(strtolower($arm['label']), strtolower($search)))
                ->pluck('key');

            $detailsColumn = match (DB::connection()->getDriverName()) {
                'pgsql' => 'details::text',
                default => 'details',
            };

            $query->where(function ($sub) use ($search, $matchingArmKeys, $detailsColumn) {
                $sub->where('actor_email', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhereRaw("{$detailsColumn} LIKE ?", ["%{$search}%"]);

                foreach ($matchingArmKeys as $armKey) {
                    $sub->orWhereRaw("{$detailsColumn} LIKE ?", ["%{$armKey}%"]);
                }
            });
        }

        return view('audit.index', [
            'logs' => $query->paginate(25)->withQueryString(),
        ]);
    }
}