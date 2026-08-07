@extends('layouts.app')
@section('title', 'Pastor Details')
@section('content')
<div class="mx-auto max-w-lg px-6 py-8">
    <h1 class="font-display text-2xl text-primary">Pastor Details</h1>
    <p class="mt-1 text-sm text-muted-foreground">{{ $church->name }}</p>

    @if (session('success'))
        <div class="mt-4 rounded-md border border-border bg-muted/40 px-4 py-3 text-sm text-primary">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mt-6 p-6">
        <form method="POST" action="{{ route('church.pastor.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="field-label">Full name</label>
                <input name="pastor_name" required class="field-input" value="{{ old('pastor_name', $church->pastor_name) }}">
                @error('pastor_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">Email</label>
                <input type="email" name="pastor_email" class="field-input" value="{{ old('pastor_email', $church->pastor_email) }}">
                @error('pastor_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">Phone</label>
                <input type="tel" name="pastor_phone" class="field-input" value="{{ old('pastor_phone', $church->pastor_phone) }}">
                @error('pastor_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">KingsChat username</label>
                <input name="pastor_kingschat" class="field-input" placeholder="@username" value="{{ old('pastor_kingschat', $church->pastor_kingschat) }}">
                @error('pastor_kingschat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
