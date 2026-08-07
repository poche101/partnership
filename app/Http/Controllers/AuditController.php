<?php

namespace App\Http\Controllers;

use App\Exports\AuditLogsExport;
use App\Models\AuditLog;
use App\Support\Arms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        return view('audit.index', [
            'logs' => $this->filteredQuery($request)->paginate(25)->withQueryString(),
        ]);
    }

    /**
     * Exports the exact same rows index() would show for these filters —
     * built from the same filteredQuery(), so the two can't drift apart.
     */
    public function export(Request $request)
    {
        return Excel::download(
            new AuditLogsExport($this->filteredQuery($request)),
            'audit-logs-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Shared by index() and export(). Same visibility scope, entity
     * filter, and search logic as before — date_from/date_to are new:
     * the view already collects them but nothing applied them yet.
     */
    private function filteredQuery(Request $request): Builder
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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        return $query;
    }
}