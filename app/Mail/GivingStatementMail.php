<?php

namespace App\Mail;

use App\Models\GivingStatement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class GivingStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GivingStatement $statement)
    {
    }

    public function build()
    {
        $partner = $this->statement->partner;
        
        $partnerLabel = trim(($partner->title ?? '').' '.$partner->first_name.' '.$partner->last_name);
        $spouseLabel = filled($partner->spouse_first_name)
            ? trim(($partner->spouse_title ?? '').' '.$partner->spouse_first_name.' '.($partner->spouse_last_name ?: $partner->last_name))
            : null;

        $name = $partnerLabel . ($spouseLabel ? ' & ' . $spouseLabel : '');

        $messageBody = $this->statement->content 
            ?? $this->statement->message 
            ?? $this->statement->message_template 
            ?? session('statement_preview');

        $pdf = Pdf::loadView('statements.pdf', [
            'statement' => $this->statement,
            'partner'   => $partner,
            'name'      => $name,
            'message'   => $messageBody,
        ])->setPaper('a4');

        return $this->subject('Your Partnership Giving Statement - Zone 5')
            ->view('statements.mail')
            ->with([
                'statement'   => $this->statement,
                'partner'     => $partner,
                'name'        => $name,
                'partnerName' => $partnerLabel,
                'spouseName'  => $spouseLabel,
                'messageBody' => $messageBody,
                'generatedAt' => $this->statement->created_at?->format('M j, Y') ?? now()->format('M j, Y'),
            ])
            ->attachData($pdf->output(), 'giving-statement.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}