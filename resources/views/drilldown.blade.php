@extends('layouts.app')
@section('title', $heading)
@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
    <nav class="text-sm text-muted-foreground">
        @foreach ($crumbs as $c)
            <a href="{{ $c['url'] }}" class="underline">{{ $c['label'] }}</a> <span>›</span>
        @endforeach
        <span>{{ $heading }}</span>
    </nav>
    <h1 class="font-display mt-2 text-xl sm:text-2xl text-primary">{{ $heading }}</h1>
    <p class="mt-1 text-sm text-muted-foreground">{{ $subtitle }}</p>

    <div class="mt-5 space-y-2">
        @forelse ($items as $item)
            <a href="{{ $item['url'] }}" class="dd-row">
                <span class="dd-name">{{ $item['name'] }}</span>
                <span class="dd-meta">{{ $item['meta'] }}</span>
                @if ($item['total'] !== null)
                    <span class="dd-total font-mono">{{ number_format($item['total'], 2) }} <small>ESPEES</small></span>
                @endif
                <span class="dd-arrow">›</span>
            </a>
        @empty
            <div class="registry"><p class="registry-empty">Nothing to show yet.</p></div>
        @endforelse
    </div>
</div>
<style>
    .dd-row { display:flex; align-items:center; gap:.75rem; padding:.95rem 1.1rem; border:1px solid var(--border,#E5E1D8);
        border-radius:8px; background:var(--card,#fff); text-decoration:none; transition:background-color .12s ease,border-color .12s ease; }
    .dd-row:hover { background:var(--muted,#FAFAF7); border-color:var(--primary,#3B5A73); }
    .dd-name { flex:1; min-width:0; font-weight:600; color:var(--primary,#3B5A73); }
    .dd-meta { font-size:.75rem; color:var(--muted-foreground,#7A756B); }
    .dd-total { font-size:.9rem; white-space:nowrap; }
    .dd-total small { font-size:.65rem; color:var(--muted-foreground,#7A756B); }
    .dd-arrow { color:var(--muted-foreground,#7A756B); font-size:1.2rem; }
</style>
@endsection