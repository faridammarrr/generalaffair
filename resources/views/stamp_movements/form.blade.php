@extends('layouts.app')

@section('title', ($mode ?? 'create') === 'edit' ? 'Edit Mutasi Meterai' : 'Catat Mutasi Meterai')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">{{ ($mode ?? 'create') === 'edit' ? 'Edit Mutasi Meterai' : 'Mutasi Meterai' }}</p>
            <h1>{{ ($mode ?? 'create') === 'edit' ? 'Perbarui mutasi meterai.' : 'Catat meterai masuk atau keluar.' }}</h1>
            <p class="lead">Gunakan meterai masuk untuk stok awal atau pembelian baru, dan meterai keluar saat dipakai untuk dokumen.</p>
        </div>

        <a class="button secondary" href="{{ route('stamp-movements.index') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </section>

    <form class="form-panel" method="POST" action="{{ ($mode ?? 'create') === 'edit' ? route('stamp-movements.update', $movement) : route('stamp-movements.store') }}">
        @csrf
        @if (($mode ?? 'create') === 'edit')
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field full">
                <label for="description">Keterangan</label>
                <input id="description" name="description" value="{{ old('description', $movement->description) }}" placeholder="Contoh: Stok awal meterai Juli" required>
                @error('description')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="person_name">Nama Pemakai</label>
                <input id="person_name" name="person_name" value="{{ old('person_name', $movement->person_name) }}" placeholder="Contoh: Budi Santoso">
                @error('person_name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="division">Divisi</label>
                <input id="division" name="division" value="{{ old('division', $movement->division) }}" placeholder="Contoh: Finance">
                @error('division')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="direction">Jenis Mutasi</label>
                <select id="direction" name="direction" required>
                    <option value="in" @selected(old('direction', $movement->direction) === 'in')>Masuk</option>
                    <option value="out" @selected(old('direction', $movement->direction) === 'out')>Keluar</option>
                    <option value="borrow" @selected(old('direction', $movement->direction) === 'borrow')>Pinjam</option>
                </select>
                @error('direction')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="quantity">Jumlah</label>
                <input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', $movement->quantity) }}" placeholder="10" required>
                @error('quantity')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="moved_at">Tanggal</label>
                <input id="moved_at" name="moved_at" type="date" value="{{ old('moved_at', optional($movement->moved_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                @error('moved_at')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('stamp-movements.index') }}">Batal</a>
            <button class="button" type="submit">
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                Simpan
            </button>
        </div>
    </form>
@endsection
