<?php $__env->startSection('title', 'Bulk Upload'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-4xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Bulk Upload</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        Upload an Excel (.xlsx) or CSV file with partner and giving columns. Rows are previewed before import —
        nothing is saved until you confirm.
    </p>

    <div class="card mt-6 p-6">
        <?php if($churches->count()): ?>
            <div class="mb-4">
                <label class="field-label">Church</label>
                <select id="church-select" class="field-input max-w-sm">
                    <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        <?php endif; ?>

        <input type="file" id="file-input" accept=".xlsx,.xls,.csv" class="block w-full text-sm">
        <p class="mt-2 text-xs text-muted-foreground">
            Expected columns (case-insensitive): title, first_name, last_name, delegate_category, kingschat_username,
            phone, email, then one column per partnership arm key (e.g. rhapsody, healing_school, loveworld_programs...).
        </p>

        <div id="preview-wrap" class="mt-6 hidden">
            <h2 class="font-display text-lg text-primary">Preview (<span id="preview-count"></span> rows)</h2>
            <div class="table-shell card mt-2 max-h-80 overflow-auto">
                <table>
                    <thead><tr><th>First name</th><th>Last name</th><th>Email</th><th>Giving total</th></tr></thead>
                    <tbody id="preview-body"></tbody>
                </table>
            </div>
            <button id="confirm-import" class="btn-primary mt-4">Confirm &amp; Import</button>
        </div>

        <div id="result" class="mt-4 hidden rounded-md border border-accent/40 bg-accent/10 px-4 py-3 text-sm"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
const ARM_KEYS = <?php echo json_encode(\App\Models\PartnershipEntry::ARM_KEYS, 15, 512) ?>;
const PARTNER_FIELDS = ['title','first_name','last_name','delegate_category','kingschat_username','phone','email','group_name','church_category','spouse_title','spouse_first_name','spouse_delegate_category','spouse_kingschat','spouse_phone','spouse_email'];

let parsedRows = [];

function normalizeKey(k) {
    return String(k).trim().toLowerCase().replace(/\s+/g, '_');
}

document.getElementById('file-input').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (evt) => {
        const wb = XLSX.read(evt.target.result, { type: 'binary' });
        const sheet = wb.Sheets[wb.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });

        parsedRows = rows.map((r) => {
            const norm = {};
            Object.keys(r).forEach((k) => { norm[normalizeKey(k)] = r[k]; });

            const partner = {};
            PARTNER_FIELDS.forEach((f) => { if (norm[f] !== undefined && norm[f] !== '') partner[f] = String(norm[f]); });

            const giving = {};
            ARM_KEYS.forEach((k) => { giving[k] = parseFloat(norm[k]) || 0; });

            return { partner, giving };
        }).filter((r) => r.partner.first_name);

        renderPreview();
    };
    reader.readAsBinaryString(file);
});

function renderPreview() {
    document.getElementById('preview-wrap').classList.remove('hidden');
    document.getElementById('preview-count').textContent = parsedRows.length;
    const body = document.getElementById('preview-body');
    body.innerHTML = parsedRows.slice(0, 50).map((r) => {
        const total = ARM_KEYS.reduce((s, k) => s + (r.giving[k] || 0), 0);
        return `<tr><td>${r.partner.first_name || ''}</td><td>${r.partner.last_name || ''}</td><td>${r.partner.email || ''}</td><td class="font-mono">${total.toFixed(2)}</td></tr>`;
    }).join('');
}

document.getElementById('confirm-import').addEventListener('click', async () => {
    const churchSelect = document.getElementById('church-select');
    const btn = document.getElementById('confirm-import');
    btn.disabled = true;
    btn.textContent = 'Importing…';

    try {
        const res = await fetch(<?php echo json_encode(route('upload.import'), 15, 512) ?>, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                church_id: churchSelect ? churchSelect.value : null,
                rows: parsedRows,
            }),
        });
        const data = await res.json();
        const resultEl = document.getElementById('result');
        resultEl.classList.remove('hidden');
        if (res.ok) {
            resultEl.textContent = `Imported ${data.partners} partner(s) and ${data.entries} giving record(s).`;
        } else {
            resultEl.textContent = data.message || 'Import failed.';
        }
    } catch (err) {
        alert('Import failed: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Confirm & Import';
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\Downloads\partnership-tracker-laravel\pt-laravel\resources\views/upload/index.blade.php ENDPATH**/ ?>