@extends('layouts.app')
@section('title', 'Giving Statements')
@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Giving Statements</h1>
    <p class="mt-1 text-sm text-muted-foreground">Generate a partnership giving statement for a partner over an optional date range.</p>

    <div class="card mt-6 p-6">
        <form method="POST" action="{{ route('statements.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            @csrf
            <div class="sm:col-span-2">
                <label class="field-label">Partner</label>
                <select name="partner_id" required class="field-input">
                    <option value="">Select partner…</option>
                    @foreach ($partners as $p)
                        <option value="{{ $p->id }}">{{ trim($p->title.' '.$p->first_name.' '.$p->last_name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">From</label>
                <input type="date" name="period_start" class="field-input">
            </div>
            <div>
                <label class="field-label">To</label>
                <input type="date" name="period_end" class="field-input">
            </div>
            <div class="sm:col-span-4">
                <button type="submit" class="btn-primary">Generate Statement</button>
            </div>
        </form>

        @if (session('statement_preview'))
            <div class="mt-6 rounded-md border border-border bg-muted/40 p-4">
                <pre id="statement-content" class="whitespace-pre-wrap font-mono text-xs text-foreground">{{ session('statement_preview') }}</pre>
                <button id="download-pdf" class="btn-outline mt-4">Download as PDF</button>
            </div>
        @endif
    </div>

    <div class="table-shell card mt-6 overflow-x-auto">
        <table>
            <thead><tr><th>Partner</th><th>Period</th><th>Total</th><th>Generated</th></tr></thead>
            <tbody>
                @forelse ($statements as $s)
                    <tr>
                        <td class="font-medium">{{ $s->partner?->fullName() }}</td>
                        <td>{{ $s->period_start?->format('M j, Y') ?? '—' }} – {{ $s->period_end?->format('M j, Y') ?? '—' }}</td>
                        <td class="font-mono">{{ number_format($s->total_espees, 2) }}</td>
                        <td>{{ $s->created_at->format('M j, Y g:ia') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-muted-foreground">No statements generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if (session('statement_preview'))
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('download-pdf')?.addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const text = document.getElementById('statement-content').textContent;
    const lines = doc.splitTextToSize(text, 180);
    doc.setFontSize(11);
    doc.text(lines, 15, 20);
    doc.save('giving-statement.pdf');
});
</script>
@endif
@endsection
