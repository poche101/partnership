
<?php $__env->startSection('title', 'Bulk Upload'); ?>
<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-6xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Bulk Upload</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        Upload an Excel (.xlsx) or CSV file with partner and giving columns. Rows are previewed before import —
        nothing is saved until you confirm.
    </p>

    <div class="card mt-6 p-6">
        <?php if($churches->count()): ?>
            <div class="mb-4">
                <label class="field-label">Church</label>

                <div class="combobox relative max-w-sm" id="church-combobox">
                    <button
                        type="button"
                        id="church-trigger"
                        class="field-input flex w-full items-center justify-between gap-2 text-left"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
                        <span id="church-trigger-label" class="truncate">
                            <?php echo e($churches->first()->name ?? 'Select church…'); ?>

                        </span>
                        <svg viewBox="0 0 20 20" fill="none" class="combobox-chevron">
                            <path d="M5.5 7.5L10 12l4.5-4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div id="church-panel" class="combobox-panel hidden">
                        <input
                            type="text"
                            id="church-search"
                            class="combobox-search"
                            placeholder="Search churches…"
                            autocomplete="off"
                        >
                        <ul id="church-options" class="combobox-options" role="listbox">
                            <?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li role="option" class="combobox-option" data-id="<?php echo e($c->id); ?>" data-name="<?php echo e($c->name); ?>">
                                    <?php echo e($c->name); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <p id="church-no-results" class="combobox-empty hidden">No churches match your search.</p>
                    </div>
                </div>

                <input type="hidden" id="church-select" value="<?php echo e($churches->first()->id ?? ''); ?>">
                <p class="mt-1 text-xs text-muted-foreground">Used for rows that don't specify their own Church Name column.</p>
            </div>
        <?php endif; ?>

        <input type="file" id="file-input" accept=".xlsx,.xls,.csv" class="block w-full text-sm">
        <p class="mt-2 text-xs text-muted-foreground">
            Expected columns (case-insensitive): title, first_name, last_name, delegate_category, kingschat_username,
            phone, email, church_name, church_category, group_name, spouse_title, spouse_first_name, spouse_last_name,
            spouse_delegate_category, spouse_kingschat, spouse_phone, spouse_email, then one column per partnership
            arm key (e.g. rhapsody, healing_school, loveworld_programs...).
        </p>

        <div id="preview-wrap" class="mt-6 hidden">
            <h2 class="font-display text-lg text-primary">Preview (<span id="preview-count"></span> rows)</h2>

            <div class="registry mt-2">
                <div class="registry-scroll" style="max-height: 20rem; overflow-y: auto;">
                    <table class="registry-table">
                        <thead>
                            <tr class="registry-group-row">
                                <th colspan="5" class="registry-group-header registry-group-partner">Partner Details</th>
                                <th colspan="5" class="registry-group-header registry-group-spouse">Spouse Details</th>
                                <th rowspan="2" class="registry-group-header registry-group-giving">Church</th>
                                <th rowspan="2" class="registry-group-header registry-group-giving">Giving Total</th>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>KingsChat</th>
                                <th class="registry-divider">Name</th>
                                <th>Category</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>KingsChat</th>
                            </tr>
                        </thead>
                        <tbody id="preview-body"></tbody>
                    </table>
                </div>
            </div>

            <button id="confirm-import" class="btn-primary mt-4">Confirm &amp; Import</button>
        </div>

        <div id="result" class="mt-4 hidden rounded-md border border-accent/40 bg-accent/10 px-4 py-3 text-sm"></div>
    </div>
</div>

<style>
    /* Church combobox */
    .combobox { }
    .combobox-chevron {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--muted-foreground, #7A756B);
        transition: transform 0.12s ease;
    }
    #church-trigger[aria-expanded="true"] .combobox-chevron {
        transform: rotate(180deg);
    }
    .combobox-panel {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 30;
        background: var(--card, #fff);
        border: 1px solid var(--border, #E5E1D8);
        border-radius: 8px;
        box-shadow: 0 8px 24px -8px rgba(0,0,0,0.18), 0 2px 6px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .combobox-search {
        width: 100%;
        border: none;
        border-bottom: 1px solid var(--border, #E5E1D8);
        padding: 0.6rem 0.85rem;
        font-size: 0.875rem;
        outline: none;
        background: var(--card, #fff);
        color: var(--foreground, #1F1B16);
    }
    .combobox-search:focus {
        background: var(--muted, #FAFAF7);
    }
    .combobox-options {
        list-style: none;
        margin: 0;
        padding: 0.25rem 0;
        max-height: 14rem;
        overflow-y: auto;
    }
    .combobox-option {
        padding: 0.55rem 0.85rem;
        font-size: 0.875rem;
        color: var(--foreground, #1F1B16);
        cursor: pointer;
    }
    .combobox-option:hover,
    .combobox-option.is-active {
        background: var(--muted, #FAFAF7);
        color: var(--primary, #3B5A73);
    }
    .combobox-empty {
        padding: 0.75rem 0.85rem;
        font-size: 0.8rem;
        color: var(--muted-foreground, #B3AEA1);
        text-align: center;
    }

    .registry {
        border: 1px solid var(--border, #E5E1D8);
        border-radius: 8px;
        overflow: hidden;
        background: var(--card, #fff);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .registry-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .registry-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .registry-scroll::-webkit-scrollbar-thumb {
        background: var(--border, #E5E1D8);
        border-radius: 999px;
    }

    .registry-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .registry-table thead th {
        text-align: left;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--muted-foreground, #7A756B);
        padding: 0.7rem 1.1rem;
        border-bottom: 2px solid var(--border, #E5E1D8);
        background: var(--muted, #FAFAF7);
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .registry-group-header {
        text-align: center;
        font-size: 0.72rem;
        padding: 0.6rem 1.1rem;
        border-bottom: 1px solid var(--border, #E5E1D8);
    }
    .registry-group-partner {
        background: var(--muted, #F3F2ED);
        color: var(--primary, #3B5A73);
    }
    .registry-group-spouse {
        background: #EFE9E0;
        color: #7A5C3E;
    }
    .registry-group-giving {
        background: var(--muted, #F3F2ED);
        color: var(--primary, #3B5A73);
    }

    .registry-table tbody tr {
        border-bottom: 1px solid var(--border, #EEEBE3);
        transition: background-color 0.12s ease;
    }
    .registry-table tbody tr:last-child { border-bottom: none; }
    .registry-table tbody tr:hover { background: var(--muted, #FAFAF7); }
    .registry-table td {
        padding: 0.85rem 1.1rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .registry-divider {
        border-left: 2px solid var(--border, #E5E1D8);
    }

    .registry-name {
        font-weight: 500;
        color: var(--foreground, #1F1B16);
        line-height: 1.3;
    }
    .registry-muted { color: var(--muted-foreground, #B3AEA1); }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
const ARM_KEYS = <?php echo json_encode(\App\Models\PartnershipEntry::ARM_KEYS, 15, 512) ?>;
const PARTNER_FIELDS = [
    'title','first_name','last_name','delegate_category','kingschat_username','phone','email',
    'church_name','church_category','group_name',
    'spouse_title','spouse_first_name','spouse_last_name','spouse_delegate_category','spouse_kingschat','spouse_phone','spouse_email',
];

let parsedRows = [];

function normalizeKey(k) {
    return String(k).trim().toLowerCase().replace(/\s+/g, '_');
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// Church combobox: a button showing the current selection opens a panel
// containing the search box and the option list together. Typing filters
// the list in place; clicking an option sets the hidden #church-select
// input (still what confirm-import and renderPreview read from) and
// updates the trigger label, then closes the panel.
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('church-combobox');
    const trigger = document.getElementById('church-trigger');
    const triggerLabel = document.getElementById('church-trigger-label');
    const panel = document.getElementById('church-panel');
    const search = document.getElementById('church-search');
    const optionsList = document.getElementById('church-options');
    const noResults = document.getElementById('church-no-results');
    const hiddenInput = document.getElementById('church-select');

    if (!root || !trigger || !panel || !search || !optionsList || !hiddenInput) return;

    const options = Array.from(optionsList.querySelectorAll('.combobox-option'));

    function openPanel() {
        panel.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');
        search.value = '';
        options.forEach((o) => { o.hidden = false; });
        noResults.classList.add('hidden');
        search.focus();
    }

    function closePanel() {
        panel.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', () => {
        const isOpen = !panel.classList.contains('hidden');
        if (isOpen) {
            closePanel();
        } else {
            openPanel();
        }
    });

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) closePanel();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePanel();
    });

    search.addEventListener('input', () => {
        const term = search.value.trim().toLowerCase();
        let anyVisible = false;

        options.forEach((o) => {
            const matches = !term || o.dataset.name.toLowerCase().includes(term);
            o.hidden = !matches;
            if (matches) anyVisible = true;
        });

        noResults.classList.toggle('hidden', anyVisible);
    });

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            hiddenInput.value = opt.dataset.id;
            triggerLabel.textContent = opt.dataset.name;
            closePanel();
        });
    });
});

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
    const churchTriggerLabel = document.getElementById('church-trigger-label');
    const fallbackChurchName = churchTriggerLabel ? churchTriggerLabel.textContent.trim() : '';

    document.getElementById('preview-wrap').classList.remove('hidden');
    document.getElementById('preview-count').textContent = parsedRows.length;

    const body = document.getElementById('preview-body');
    body.innerHTML = parsedRows.slice(0, 50).map((r) => {
        const total = ARM_KEYS.reduce((s, k) => s + (r.giving[k] || 0), 0);

        const partnerName = [r.partner.title, r.partner.first_name, r.partner.last_name].filter(Boolean).join(' ');
        const hasSpouse = !!r.partner.spouse_first_name;
        const spouseName = hasSpouse
            ? [r.partner.spouse_title, r.partner.spouse_first_name, r.partner.spouse_last_name].filter(Boolean).join(' ')
            : '';

        const church = r.partner.church_name || fallbackChurchName || '—';

        const muted = (v) => v ? escapeHtml(v) : '<span class="registry-muted">—</span>';

        return `<tr>
            <td><div class="registry-name">${escapeHtml(partnerName)}</div></td>
            <td>${muted(r.partner.delegate_category)}</td>
            <td>${muted(r.partner.phone)}</td>
            <td>${muted(r.partner.email)}</td>
            <td>${muted(r.partner.kingschat_username)}</td>
            <td class="registry-divider">${hasSpouse ? `<div class="registry-name">${escapeHtml(spouseName)}</div>` : '<span class="registry-muted">—</span>'}</td>
            <td>${hasSpouse ? muted(r.partner.spouse_delegate_category) : '<span class="registry-muted">—</span>'}</td>
            <td>${hasSpouse ? muted(r.partner.spouse_phone) : '<span class="registry-muted">—</span>'}</td>
            <td>${hasSpouse ? muted(r.partner.spouse_email) : '<span class="registry-muted">—</span>'}</td>
            <td>${hasSpouse ? muted(r.partner.spouse_kingschat) : '<span class="registry-muted">—</span>'}</td>
            <td>${escapeHtml(church)}</td>
            <td class="font-mono">${total.toFixed(2)}</td>
        </tr>`;
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kings\partnership\partnership\resources\views/upload/index.blade.php ENDPATH**/ ?>