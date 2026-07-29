<?php

namespace App\Http\Controllers;

use App\Mail\GivingStatementMail;
use App\Models\GivingStatement;
use App\Models\Partner;
use App\Services\AuditLogger;
use App\Services\GivingStatementWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class StatementController extends Controller
{
    public function index()
    {
        return view('statements.index', [
            'partners' => Partner::orderBy('first_name')->get(['id', 'title', 'first_name', 'last_name']),
            'statements' => GivingStatement::with('partner')->latest('created_at')->limit(50)->get(),
        ]);
    }

    public function store(Request $request, GivingStatementWriter $writer)
    {
        $data = $request->validate([
            'partner_id' => ['required', 'exists:partners,id'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
        ]);

        $partner = Partner::with('church.groupChurch')->findOrFail($data['partner_id']);
        $result = $writer->write($partner, $data['period_start'] ?? null, $data['period_end'] ?? null);

        $statement = GivingStatement::create([
            'partner_id' => $partner->id,
            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'total_espees' => $result['total'],
            'content' => $result['content'],
            'generated_by' => Auth::id(),
            'created_at' => now(),
        ]);

        AuditLogger::log(Auth::user(), 'statement.generated', 'giving_statement', $statement->id, [
            'partner_id' => $partner->id, 'total' => $result['total'],
        ]);

        return back()->with('statement_preview', $statement->content)->with('success', 'Statement generated.');
    }

    public function send(GivingStatement $statement)
{
    $partner = $statement->partner;

    if (empty($partner->email)) {
        return back()->with('error', 'This partner has no email address on file.');
    }

    Mail::to($partner->email)->send(new GivingStatementMail($statement));

    AuditLogger::log(Auth::user(), 'statement.emailed', 'giving_statement', $statement->id, [
        'partner_id' => $partner->id,
        'email' => $partner->email,
    ]);

    return back()->with('success', 'Statement sent to '.$partner->email.'.');
}
}
