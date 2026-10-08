@extends('layouts.app')
@section('title', 'Bulk Upload')
@section('content')
@php
    /*
     * Group churches shown in the dropdown, in this order.
     * (Numbering removed; the double space in "Ikoyi  Group 1/2" is tidied.)
     */
    $groupList = [
        'Lekki Group',
        'Obalende Group',
        'Lekki Free Trade Zone Group',
        'Chevron Group',
        'Lagos Island Group',
        'Victoria Island Group',
        'Ajah Group',
        'Owode-Badore Group',
        'Ajiwe Group',
        'Mobil Road Group',
        'Ogombo Group',
        'Alasia Group',
        'Tedo Group',
        'Kajola Group',
        'Onishon Group',
        'Eputu Group',
        'Epe Group',
        'Brazil Group',
        'Youth Church Group',
        'Teens Church Group',
        'Lekki Phase 1 Sub Group',
        'Eleko Sub Group',
        'Ikoyi Group 1',
        'Ikoyi Group 2',
        'Victoria Island Sub-Group',
        'Abijo Sub-Group',
    ];

    // Match each listed name to the group church saved in the app, ignoring case,
    // extra spaces and hyphens. The two Ikoyi groups are saved as "Ikoyi Sub-Group 1/2".
    $norm = fn ($s) => mb_strtolower(trim(preg_replace('/[\s\-]+/', ' ', (string) $s)));
    $aliases = ['ikoyi group 1' => 'ikoyi sub group 1', 'ikoyi group 2' => 'ikoyi sub group 2'];
    $dbGroups = \App\Models\GroupChurch::get(['id', 'name'])->keyBy(fn ($g) => $norm($g->name));

    // Zone admins see every group; group/church admins only the groups they can reach.
    $allowedGroupIds = auth()->user()->isZoneAdmin()
        ? null
        : $churches->pluck('group_church_id')->filter()->unique()->values()->all();

    $groupOptions = collect($groupList)
        ->map(function ($name) use ($norm, $aliases, $dbGroups) {
            $key = $norm($name);
            $g = $dbGroups->get($key) ?? $dbGroups->get($aliases[$key] ?? '');
            return ['name' => $name, 'id' => $g?->id];
        })
        ->filter(fn ($o) => $allowedGroupIds === null || ($o['id'] && in_array($o['id'], $allowedGroupIds)))
        ->values();

    $singleGroup = $groupOptions->count() === 1 ? $groupOptions->first() : null;
@endphp
<div class="mx-auto max-w-6xl px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Bulk Upload</h1>
    <p class="mt-1 text-sm text-muted-foreground">
        Upload an Excel (.xlsx) or CSV file with partner and giving columns. Rows are previewed before import —
        nothing is saved until you confirm.
    </p>

    <div class="card mt-6 p-6">
        @if ($groupOptions->count())
            <div class="mb-4">
                <label class="field-label">Group church</label>

                <div class="combobox relative max-w-sm" id="group-combobox">
                    <button
                        type="button"
                        id="group-trigger"
                        class="field-input flex w-full items-center justify-between gap-2 text-left"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
                        <span id="group-trigger-label" class="truncate text-muted-foreground">Select group church…</span>
                        <svg viewBox="0 0 20 20" fill="none" class="combobox-chevron">
                            <path d="M5.5 7.5L10 12l4.5-4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div id="group-panel" class="combobox-panel hidden">
                        <input
                            type="text"
                            id="group-search"
                            class="combobox-search"
                            placeholder="Search group churches…"
                            autocomplete="off"
                        >
                        <ul id="group-options" class="combobox-options" role="listbox">
                            <li role="option" class="combobox-option" data-clear data-id="" data-name="">
                                <em>None — use each row's group_name</em>
                            </li>
                            @foreach ($groupOptions as $g)
                                <li role="option" class="combobox-option" data-id="{{ $g['id'] }}" data-name="{{ $g['name'] }}">
                                    {{ $g['name'] }}
                                </li>
                            @endforeach
                        </ul>
                        <p id="group-no-results" class="combobox-empty hidden">No group churches match your search.</p>
                    </div>
                </div>

                <input type="hidden" id="group-select" value="">
                <p class="mt-1 text-xs text-muted-foreground">
                    Each row's <code>church_name</code> is matched inside this group. Rows with their own
                    <code>group_name</code> use that instead.
                </p>
            </div>
        @endif

        <div class="mb-3 flex flex-wrap items-center gap-3">
            <button type="button" id="download-template" class="btn-template">
                <svg viewBox="0 0 20 20" fill="none" width="16" height="16" aria-hidden="true">
                    <path d="M10 3v9m0 0l-3.5-3.5M10 12l3.5-3.5M4 15.5h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Download Template (.xlsx)
            </button>
            <span class="text-xs text-muted-foreground">Fill it in, then upload it below.</span>
        </div>

        <input type="file" id="file-input" accept=".xlsx,.xls,.csv" class="block w-full text-sm">
        <p class="mt-2 text-xs text-muted-foreground">
            Expected columns (case-insensitive): title, first_name, last_name, delegate_category, kingschat_username,
            phone, email, church_name, church_category, group_name, spouse_title, spouse_first_name, spouse_last_name,
            spouse_delegate_category, spouse_kingschat, spouse_phone, spouse_email, then one column per partnership
            arm key (e.g. rhapsody, healing_school, loveworld_programs...).
        </p>

        <div id="preview-wrap" class="mt-6 hidden">
            <h2 class="font-display text-lg text-primary">Preview (<span id="preview-count"></span> rows)</h2>

            <div id="preview-warning" class="mt-2 hidden rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800"></div>

            <div class="registry mt-2">
                <div class="registry-scroll" style="max-height: 20rem; overflow-y: auto;">
                    <table class="registry-table">
                        <thead>
                            <tr class="registry-group-row">
                                <th colspan="5" class="registry-group-header registry-group-partner">Partner Details</th>
                                <th colspan="5" class="registry-group-header registry-group-spouse">Spouse Details</th>
                                <th rowspan="2" class="registry-group-header registry-group-giving">Church / Group</th>
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
    /* Template download button */
    .btn-template {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.9rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--primary, #3B5A73);
        background: var(--card, #fff);
        border: 1px solid var(--border, #E5E1D8);
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.12s ease, border-color 0.12s ease;
    }
    .btn-template:hover {
        background: var(--muted, #FAFAF7);
        border-color: var(--primary, #3B5A73);
    }

    /* Group church combobox */
    .combobox-chevron {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--muted-foreground, #7A756B);
        transition: transform 0.12s ease;
    }
    #group-trigger[aria-expanded="true"] .combobox-chevron {
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
    .registry-sub {
        font-size: 0.75rem;
        color: var(--muted-foreground, #8A8578);
        margin-top: 0.1rem;
    }
    .registry-muted { color: var(--muted-foreground, #B3AEA1); }
    .registry-missing { color: #B3261E; font-weight: 500; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
const ARM_KEYS = @json(\App\Models\PartnershipEntry::ARM_KEYS);
const PARTNER_FIELDS = [
    'title','first_name','last_name','delegate_category','kingschat_username','phone','email',
    'church_name','church_category','group_name',
    'spouse_title','spouse_first_name','spouse_last_name','spouse_delegate_category','spouse_kingschat','spouse_phone','spouse_email',
];
const SINGLE_GROUP = @json($singleGroup);

let parsedRows = [];
let selectedGroup = { id: '', name: '' };

// Template download: built in the browser from PARTNER_FIELDS + ARM_KEYS,
// so the columns always match what the importer expects.
// Sheet 1 ("Template") is headers only and is the sheet the importer reads.
// Sheet 2 ("Example") shows a filled-in row for reference and is ignored on import.
document.getElementById('download-template').addEventListener('click', () => {
    const headers = [...PARTNER_FIELDS, ...ARM_KEYS];

    const template = XLSX.utils.aoa_to_sheet([headers]);
    template['!cols'] = headers.map((h) => ({ wch: Math.max(h.length + 2, 14) }));

    const sample = {
        title: 'Pastor', first_name: 'John', last_name: 'Doe',
        delegate_category: 'Partner', kingschat_username: 'johndoe',
        phone: '08012345678', email: 'john@example.com',
        church_name: 'Example Church', church_category: '', group_name: '',
        spouse_title: 'Sis', spouse_first_name: 'Jane', spouse_last_name: 'Doe',
    };
    ARM_KEYS.forEach((k, i) => { sample[k] = i === 0 ? 5000 : ''; });

    const example = XLSX.utils.aoa_to_sheet([headers, headers.map((h) => sample[h] ?? '')]);
    example['!cols'] = template['!cols'];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, template, 'Template');
    XLSX.utils.book_append_sheet(wb, example, 'Example');
    XLSX.writeFile(wb, 'partnership-upload-template.xlsx');
});

function normalizeKey(k) {
    return String(k).trim().toLowerCase().replace(/\s+/g, '_');
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// Group church combobox: a button showing the current selection opens a panel
// containing the search box and the option list together. Typing filters
// the list in place; clicking an option sets the hidden #group-select
// input and updates the trigger label, then closes the panel.
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('group-combobox');
    const trigger = document.getElementById('group-trigger');
    const triggerLabel = document.getElementById('group-trigger-label');
    const panel = document.getElementById('group-panel');
    const search = document.getElementById('group-search');
    const optionsList = document.getElementById('group-options');
    const noResults = document.getElementById('group-no-results');
    const hiddenInput = document.getElementById('group-select');

    if (!root || !trigger || !panel || !search || !optionsList || !hiddenInput) return;

    const options = Array.from(optionsList.querySelectorAll('.combobox-option'));
    const placeholder = 'Select group church…';

    function setGroup(id, name) {
        selectedGroup = { id: id || '', name: name || '' };
        hiddenInput.value = selectedGroup.id;
        triggerLabel.textContent = name || placeholder;
        triggerLabel.classList.toggle('text-muted-foreground', !name);
        if (parsedRows.length) renderPreview();
    }

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
            const isClear = o.hasAttribute('data-clear');
            const matches = isClear || !term || o.dataset.name.toLowerCase().includes(term);
            o.hidden = !matches;
            if (matches && !isClear) anyVisible = true;
        });

        noResults.classList.toggle('hidden', anyVisible);
    });

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            setGroup(opt.dataset.id, opt.dataset.name);
            closePanel();
        });
    });

    // A group/church admin who only has one group doesn't need to choose.
    if (SINGLE_GROUP) setGroup(SINGLE_GROUP.id ?? '', SINGLE_GROUP.name);
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
    const fallbackGroupName = selectedGroup.name;

    document.getElementById('preview-wrap').classList.remove('hidden');
    document.getElementById('preview-count').textContent = parsedRows.length;

    // A group can't stand in for a church, so every row needs its own church_name.
    const missing = parsedRows.filter((r) => !r.partner.church_name).length;
    const warn = document.getElementById('preview-warning');
    warn.classList.toggle('hidden', missing === 0);
    warn.textContent = missing
        ? `${missing} row(s) have no church_name. Add a church_name to those rows before importing.`
        : '';
    document.getElementById('confirm-import').disabled = missing > 0;

    const muted = (v) => v ? escapeHtml(v) : '<span class="registry-muted">—</span>';

    const body = document.getElementById('preview-body');
    body.innerHTML = parsedRows.slice(0, 50).map((r) => {
        const total = ARM_KEYS.reduce((s, k) => s + (r.giving[k] || 0), 0);

        const partnerName = [r.partner.title, r.partner.first_name, r.partner.last_name].filter(Boolean).join(' ');
        const hasSpouse = !!r.partner.spouse_first_name;
        const spouseName = hasSpouse
            ? [r.partner.spouse_title, r.partner.spouse_first_name, r.partner.spouse_last_name].filter(Boolean).join(' ')
            : '';

        const churchName = r.partner.church_name || '';
        const groupName = r.partner.group_name || fallbackGroupName;

        const churchCell = churchName
            ? `<div class="registry-name">${escapeHtml(churchName)}</div>`
            : '<div class="registry-missing">No church</div>';
        const groupCell = groupName ? `<div class="registry-sub">${escapeHtml(groupName)}</div>` : '';

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
            <td>${churchCell}${groupCell}</td>
            <td class="font-mono">${total.toFixed(2)}</td>
        </tr>`;
    }).join('');
}

document.getElementById('confirm-import').addEventListener('click', async () => {
    const btn = document.getElementById('confirm-import');
    btn.disabled = true;
    btn.textContent = 'Importing…';

    try {
        const res = await fetch(@json(route('upload.import')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                group_church_id: selectedGroup.id || null,
                group_name: selectedGroup.name || null,
                church_id: null,
                rows: parsedRows,
            }),
        });
        const data = await res.json();
        const resultEl = document.getElementById('result');
        resultEl.classList.remove('hidden');
        if (res.ok) {
            resultEl.textContent = `Imported ${data.partners} partner(s) and ${data.entries} giving record(s).`;
            if (Array.isArray(data.skipped) && data.skipped.length) {
                resultEl.textContent += ` ${data.skipped.length} skipped: ` + data.skipped.slice(0, 10).join('; ');
            }
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
@endsection