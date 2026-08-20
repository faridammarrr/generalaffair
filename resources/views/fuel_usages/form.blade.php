@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Pembelian Bensin' : 'Tambah Pembelian Bensin')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Monitoring Bensin</p>
            <h1>{{ $mode === 'edit' ? 'Edit Pembelian Bensin' : 'Tambah Pembelian Bensin' }}</h1>
            <p class="lead">Catat pembelian bensin yang dilakukan supir agar sisa budget bulanan bisa dipantau.</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Form Pembelian Bensin</h2>
                <span class="muted">Isi data berikut agar pembelian bisa tercatat dengan mudah</span>
            </div>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('fuel-usages.update', $item) : route('fuel-usages.store') }}" class="stack p-5">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="field">
                    <label for="person">Nama person / supir</label>
                    <input id="person" name="person" value="{{ old('person', $item->person ?? $item->driver_name) }}" placeholder="Contoh: Budi / Pak Arif" required>
                    @error('person')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="date">Tanggal pembelian</label>
                    <input id="date" name="date" type="date" value="{{ old('date', $item->date ? $item->date->format('Y-m-d') : '') }}" required>
                    @error('date')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="field">
                    <label for="amount">Nominal pembelian</label>
                    <input id="amount" name="amount" type="number" min="1" value="{{ old('amount', $item->amount) }}" placeholder="Contoh: 200000" required>
                    @error('amount')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="budget_month">Bulan anggaran</label>
                    <input id="budget_month" name="budget_month" type="number" min="1" max="12" value="{{ old('budget_month', $item->budget_month ?? now()->month) }}" placeholder="1-12" required>
                    @error('budget_month')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="field">
                <label for="budget_year">Tahun anggaran</label>
                <input id="budget_year" name="budget_year" type="number" min="2000" value="{{ old('budget_year', $item->budget_year ?? now()->year) }}" placeholder="2026" required>
                @error('budget_year')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="description">Keterangan</label>
                <textarea id="description" name="description" rows="4" placeholder="Contoh: Bensin untuk perjalanan kantor hari ini" required>{{ old('description', $item->description ?? $item->notes) }}</textarea>
                <small class="mt-2 block text-xs font-medium text-slate-500">Isi singkat saja, misalnya tujuan atau keperluan pembelian.</small>
                @error('description')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a class="button secondary" href="{{ route('fuel-usages.index') }}">Batal</a>
                <button class="button" type="submit">Simpan data</button>
            </div>
        </form>
    </section>
@endsection
