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
        $name = $partner->fullName();
        if ($partner->spouse_name) {
            $name .= ' & '.$partner->spouse_name;
        }

        $pdf = Pdf::loadView('statements.pdf', [
            'statement' => $this->statement,
            'partner' => $partner,
            'name' => $name,
        ])->setPaper('a4');

        return $this->subject('Your Partnership Giving Statement')
            ->view('statements.mail')
            ->with(['name' => $name, 'statement' => $this->statement])
            ->attachData($pdf->output(), 'giving-statement.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}