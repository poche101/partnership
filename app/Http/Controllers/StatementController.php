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
            'partners' => Partner::orderBy('first_name')->get(['id', 'title', 'first_name', 'last_name', 'spouse_title', 'spouse_first_name', 'spouse_last_name']),
            'statements' => GivingStatement::with('partner')->latest('created_at')->limit(50)->get(),
        ]);
    }

    public function store(Request $request, GivingStatementWriter $writer)
    {
        $data = $request->validate([
            'partner_id' => ['required', 'exists:partners,id'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'message_template' => ['nullable', 'string'],
        ]);

        $partner = Partner::with('church.groupChurch')->findOrFail($data['partner_id']);
        $result = $writer->write(
            $partner,
            $data['period_start'] ?? null,
            $data['period_end'] ?? null,
            $data['message_template'] ?? null
        );

        $content = $result['content'];

        if (! empty($data['message_template']) && ($result['content_source'] ?? null) !== 'template') {
            $content = $this->resolveTemplate($data['message_template'], $partner, $data, $result['total']);
        }

        $statement = GivingStatement::create([
            'partner_id' => $partner->id,
            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'total_espees' => $result['total'],
            'content' => $content,
            'generated_by' => Auth::id(),
            'created_at' => now(),
        ]);

        AuditLogger::log(Auth::user(), 'statement.generated', 'giving_statement', $statement->id, [
            'partner_id' => $partner->id, 'total' => $result['total'],
        ]);

        $partnerName = trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name);
        $spouseName = filled($partner->spouse_first_name)
            ? trim(($partner->spouse_title ?? '').' '.$partner->spouse_first_name.' '.($partner->spouse_last_name ?: $partner->last_name))
            : null;

        return back()->with([
            'statement_id' => $statement->id,
            'statement_preview' => $statement->content,
            'statement_partner_name' => $partnerName,
            'statement_spouse_name' => $spouseName,
            'success' => 'Statement generated.',
        ]);
    }

    public function send(GivingStatement $statement)
    {
        $partner = $statement->partner;

        // Send to whichever of partner/spouse have an email on file —
        // a couple sharing one giving record should both receive the
        // statement when both addresses are available.
        $recipients = array_values(array_filter([
            $partner->email ?? null,
            $partner->spouse_email ?? null,
        ]));

        if (empty($recipients)) {
            return back()->with('error', 'This partner has no email address on file for either the partner or spouse.');
        }

        Mail::to($recipients)->send(new GivingStatementMail($statement));

        AuditLogger::log(Auth::user(), 'statement.emailed', 'giving_statement', $statement->id, [
            'partner_id' => $partner->id,
            'emails' => $recipients,
        ]);

        return back()->with('success', 'Statement sent to '.implode(' and ', $recipients).'.');
    }

    /**
     * Resolve {partner_name}, {spouse_suffix}, {period}, and {total} tokens
     * in a user-submitted message template using server-computed values.
     */
    private function resolveTemplate(string $template, Partner $partner, array $data, float $total): string
    {
        $partnerName = trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name);

        $spouseSuffix = '';
        if (filled($partner->spouse_first_name)) {
            $spouseName = trim(
                ($partner->spouse_title ?? '').' '.
                $partner->spouse_first_name.' '.
                ($partner->spouse_last_name ?: $partner->last_name)
            );
            $spouseSuffix = ' and '.$spouseName;
        }

        $start = $data['period_start'] ?? null;
        $end = $data['period_end'] ?? null;
        $period = ($start || $end)
            ? sprintf(
                '%s – %s',
                $start ? \Carbon\Carbon::parse($start)->format('M j, Y') : '…',
                $end ? \Carbon\Carbon::parse($end)->format('M j, Y') : '…'
            )
            : 'the selected period';

        return strtr($template, [
            '{partner_name}' => $partnerName,
            '{spouse_suffix}' => $spouseSuffix,
            '{period}' => $period,
            '{total}' => number_format($total, 2),
        ]);
    }
}