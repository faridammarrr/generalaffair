@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Petty Cash' : 'Tambah Petty Cash')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Petty Cash</p>
            <h1>{{ $mode === 'edit' ? 'Edit Catatan Petty Cash' : 'Tambah Catatan Petty Cash' }}</h1>
            <p class="lead">Isi data belanja kecil dengan kategori dan penanggung jawab yang jelas.</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Form Catatan</h2>
                <span class="muted">Catat pengeluaran harian dengan cepat</span>
            </div>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('petty-cashes.update', $item) : route('petty-cashes.store') }}" class="stack p-5" enctype="multipart/form-data">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="field">
                <label for="date">Tanggal</label>
                <input id="date" name="date" type="date" value="{{ old('date', optional($item->date)->format('Y-m-d')) }}">
                @error('date')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="description">Deskripsi</label>
                <input id="description" name="description" value="{{ old('description', $item->description) }}" placeholder="Contoh: Belanja ATK kantor" required>
                @error('description')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="amount">Nominal</label>
                <input id="amount" name="amount" type="number" min="1" value="{{ old('amount', $item->amount) }}" placeholder="Contoh: 125000" required>
                @error('amount')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="category">Kategori</label>
                <select id="category" name="category">
                    @php
                        $categories = 
                        ['Operasional Kantor', 
                        'Pantry & Konsumsi', 
                        'Kebersihan & Houskeeping', 
                        'Tanaman & Lingkungan',
                        'Kesehatan',
                        'Transportasi & Perjalanan',
                        'Maintenance & Perbaikan',
                        'IT & Equipment',
                        'Lain-lain'];
                        $currentCategory = old('category', $item->category);

                        if ($currentCategory && ! in_array($currentCategory, $categories, true)) {
                            $categories[] = $currentCategory;
                        }
                    @endphp
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected($currentCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="person_name">Penanggung Jawab</label>
                <input id="person_name" name="person_name" value="{{ old('person_name', $item->person_name) }}" placeholder="Contoh: Budi">
                @error('person_name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Tambahkan keterangan jika perlu">{{ old('notes', $item->notes) }}</textarea>
                @error('notes')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="invoice">Bukti Invoice</label>
                <input id="invoice" name="invoice" type="file" accept=".jpg,.jpeg,.png,.pdf">
                @error('invoice')
                    <p class="error">{{ $message }}</p>
                @enderror
                @if ($mode === 'edit' && $item->invoice_path)
                    <div class="file-note">
                        File saat ini: {{ basename($item->invoice_path) }}
                        <label class="checkbox-label">
                            <input type="checkbox" name="remove_invoice" value="1">
                            Hapus file
                        </label>
                    </div>
                @endif
            </div>

            <div class="actions">
                <button class="button" type="submit">Simpan</button>
                <a class="button secondary" href="{{ route('petty-cashes.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
