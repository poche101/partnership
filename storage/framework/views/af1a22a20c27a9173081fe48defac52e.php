
<?php $__env->startSection('title', 'Giving Statements'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-5xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Giving Statements</h1>
    <p class="mt-1 text-sm text-muted-foreground">Generate a partnership giving statement for a partner over an optional date range.</p>

    <div class="card mt-6 p-6">
        <form method="POST" action="<?php echo e(route('statements.store')); ?>" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <?php echo csrf_field(); ?>
            <div class="sm:col-span-2">
                <label class="field-label">Partner</label>
                <select name="partner_id" required class="field-input">
                    <option value="">Select partner…</option>
                    <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>"><?php echo e(trim($p->title.' '.$p->first_name.' '.$p->last_name)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <button type="submit" class="btn-primary btn-icon">
                    <svg viewBox="0 0 20 20" fill="none" class="btn-svg"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
                    Generate Statement
                </button>
            </div>
        </form>
    </div>

    <?php if(session('statement_preview')): ?>
        <div class="mt-8">
            <div id="statement-doc" class="statement-doc">
                <div class="statement-frame">
                    <div class="statement-header">
                        <img src="/images/zone5-logo.png" alt="Zone 5" class="statement-logo">
                        <div class="statement-org">
                            <p class="statement-org-name">Zone 5</p>
                            <p class="statement-org-sub">Partnership &amp; Giving Records</p>
                        </div>
                        <div class="statement-doc-id">
                            <p class="statement-eyebrow">Statement</p>
                            <p class="statement-date"><?php echo e(now()->format('M j, Y')); ?></p>
                        </div>
                    </div>

                    <div class="statement-rule"></div>

                    <h2 class="statement-title">Partnership Giving Statement</h2>

                    <div class="statement-body">
                        <pre class="statement-content"><?php echo e(session('statement_preview')); ?></pre>
                    </div>

                    <div class="statement-footer">
                        <div class="statement-seal" aria-hidden="true">
                            <svg viewBox="0 0 120 120" width="88" height="88">
                                <circle cx="60" cy="60" r="56" fill="none" stroke="var(--doc-brass)" stroke-width="1.5"/>
                                <circle cx="60" cy="60" r="48" fill="none" stroke="var(--doc-brass)" stroke-width="1"/>
                                <path id="sealArcTop" d="M 15,60 A 45,45 0 0 1 105,60" fill="none"/>
                                <path id="sealArcBottom" d="M 105,60 A 45,45 0 0 1 15,60" fill="none"/>
                                <text font-size="8.5" letter-spacing="2" fill="var(--doc-brass)">
                                    <textPath href="#sealArcTop" startOffset="50%" text-anchor="middle">OFFICIAL RECORD</textPath>
                                </text>
                                <text font-size="8.5" letter-spacing="2" fill="var(--doc-brass)">
                                    <textPath href="#sealArcBottom" startOffset="50%" text-anchor="middle">ZONE FIVE</textPath>
                                </text>
                                <path d="M45,62 L55,72 L76,48" fill="none" stroke="var(--doc-brass)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="statement-note">This statement reflects partnership giving recorded in the Zone 5 system as of the date above.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <button id="download-pdf" class="btn-outline btn-icon">
                    <svg viewBox="0 0 20 20" fill="none" class="btn-svg"><path d="M10 3v10m0 0l-4-4m4 4l4-4M4 16h12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Download as PDF
                </button>

                <?php if(session('statement_id')): ?>
                    <form method="POST" action="<?php echo e(route('statements.send', session('statement_id'))); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary btn-icon" onclick="this.disabled=true; this.querySelector('.btn-label').textContent='Sending…'; this.closest('form').submit();">
                            <svg viewBox="0 0 20 20" fill="none" class="btn-svg"><path d="M3 5l7 5 7-5M3 5h14v10H3V5z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/></svg>
                            <span class="btn-label">Email to Partner</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-shell card mt-8 overflow-x-auto">
        <table>
            <thead><tr><th>Partner</th><th>Period</th><th>Total</th><th>Generated</th><th></th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $statements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-medium"><?php echo e($s->partner?->fullName()); ?></td>
                        <td><?php echo e($s->period_start?->format('M j, Y') ?? '—'); ?> – <?php echo e($s->period_end?->format('M j, Y') ?? '—'); ?></td>
                        <td class="font-mono"><?php echo e(number_format($s->total_espees, 2)); ?></td>
                        <td><?php echo e($s->created_at->format('M j, Y g:ia')); ?></td>
                        <td>
                            <form method="POST" action="<?php echo e(route('statements.send', $s->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-link-icon" title="Resend by email">
                                    <svg viewBox="0 0 20 20" fill="none" class="btn-svg-sm"><path d="M3 5l7 5 7-5M3 5h14v10H3V5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                    Resend
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="py-6 text-center text-muted-foreground">No statements generated yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .statement-doc {
        --doc-paper: #FAF8F2;
        --doc-paper-shade: #F1ECDD;
        --doc-ink: #16324F;
        --doc-ink-soft: #4A5D70;
        --doc-brass: #A9812F;
        --doc-hairline: #D9D2C0;
    }
    .statement-frame {
        background: var(--doc-paper);
        border: 1px solid var(--doc-hairline);
        box-shadow: 0 1px 3px rgba(22,50,79,0.06), 0 12px 32px -20px rgba(22,50,79,0.25);
        border-radius: 4px;
        padding: 2.25rem 2.5rem;
        position: relative;
    }
    .statement-frame::before {
        content: "";
        position: absolute;
        inset: 8px;
        border: 1px solid var(--doc-hairline);
        border-radius: 2px;
        pointer-events: none;
    }
    .statement-header {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .statement-logo {
        height: 40px;
        width: auto;
        object-fit: contain;
    }
    .statement-org { flex: 1; }
    .statement-org-name {
        font-family: inherit;
        font-weight: 600;
        color: var(--doc-ink);
        letter-spacing: 0.02em;
    }
    .statement-org-sub {
        font-size: 0.7rem;
        color: var(--doc-ink-soft);
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .statement-doc-id { text-align: right; }
    .statement-eyebrow {
        font-size: 0.65rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--doc-brass);
        font-weight: 600;
    }
    .statement-date {
        font-size: 0.8rem;
        color: var(--doc-ink-soft);
        font-variant-numeric: tabular-nums;
    }
    .statement-rule {
        margin: 1.25rem 0 1.5rem;
        height: 1px;
        background: linear-gradient(to right, var(--doc-brass), var(--doc-hairline) 40%, transparent);
    }
    .statement-title {
        font-family: inherit;
        font-size: 1.35rem;
        color: var(--doc-ink);
        letter-spacing: 0.01em;
    }
    .statement-body { margin-top: 1.25rem; }
    .statement-content {
        white-space: pre-wrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.8rem;
        line-height: 1.6;
        color: var(--doc-ink);
        background: var(--doc-paper-shade);
        border: 1px solid var(--doc-hairline);
        border-radius: 3px;
        padding: 1.25rem 1.5rem;
    }
    .statement-footer {
        margin-top: 1.75rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-top: 1px dashed var(--doc-hairline);
        padding-top: 1.25rem;
    }
    .statement-seal { flex-shrink: 0; opacity: 0.9; }
    .statement-note {
        font-size: 0.7rem;
        color: var(--doc-ink-soft);
        line-height: 1.5;
    }

    /* Button styling */
    .btn-icon {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .btn-svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
    .btn-svg-sm {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
    }
    .btn-link-icon {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.75rem;
        color: var(--doc-ink-soft, #4A5D70);
        text-decoration: underline;
        text-underline-offset: 2px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }
    .btn-link-icon:hover {
        color: var(--doc-ink, #16324F);
    }
    button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('download-pdf')?.addEventListener('click', async () => {
    const button = document.getElementById('download-pdf');
    const source = document.getElementById('statement-doc');
    const originalLabel = button.innerHTML;

    button.disabled = true;
    button.textContent = 'Preparing PDF…';

    try {
        const canvas = await html2canvas(source, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#FAF8F2',
        });

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'pt', 'a4');

        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        const margin = 20;
        const usableWidth = pageWidth - margin * 2;

        const imgWidth = usableWidth;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        const imgData = canvas.toDataURL('image/png');

        if (imgHeight <= pageHeight - margin * 2) {
            doc.addImage(imgData, 'PNG', margin, margin, imgWidth, imgHeight);
        } else {
            const pageCanvasHeight = ((pageHeight - margin * 2) * canvas.width) / imgWidth;
            let renderedHeight = 0;
            let firstPage = true;

            while (renderedHeight < canvas.height) {
                const sliceHeight = Math.min(pageCanvasHeight, canvas.height - renderedHeight);

                const pageCanvas = document.createElement('canvas');
                pageCanvas.width = canvas.width;
                pageCanvas.height = sliceHeight;

                const ctx = pageCanvas.getContext('2d');
                ctx.drawImage(canvas, 0, renderedHeight, canvas.width, sliceHeight, 0, 0, canvas.width, sliceHeight);

                const sliceData = pageCanvas.toDataURL('image/png');
                const sliceImgHeight = (sliceHeight * imgWidth) / canvas.width;

                if (!firstPage) doc.addPage();
                doc.addImage(sliceData, 'PNG', margin, margin, imgWidth, sliceImgHeight);

                renderedHeight += sliceHeight;
                firstPage = false;
            }
        }

        doc.save('giving-statement.pdf');
    } catch (err) {
        console.error('PDF generation failed:', err);
        alert('Could not generate PDF. Please try again.');
    } finally {
        button.disabled = false;
        button.innerHTML = originalLabel;
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/statements/index.blade.php ENDPATH**/ ?>