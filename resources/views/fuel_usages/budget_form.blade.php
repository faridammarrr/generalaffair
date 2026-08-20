@extends('layouts.app')

@section('title', 'Set Budget Bensin')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Fuel Usage</p>
            <h1>Set budget bulanan</h1>
            <p class="lead">Masukkan anggaran bensin untuk bulan ini agar sisa budget bisa dihitung otomatis.</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Budget bulan {{ $month }}/{{ $year }}</h2>
                <span class="muted">Data ini dipakai untuk menghitung sisa anggaran tiap pembelian</span>
            </div>
        </div>

        <form method="POST" action="{{ route('fuel-usages.budgets.store') }}" class="stack p-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="field">
                    <label for="month">Bulan</label>
                    <input id="month" name="month" type="number" min="1" max="12" value="{{ old('month', $month) }}" required>
                </div>

                <div class="field">
                    <label for="year">Tahun</label>
                    <input id="year" name="year" type="number" min="2000" value="{{ old('year', $year) }}" required>
                </div>
            </div>

            <div class="field">
                <label for="amount">Nominal budget</label>
                <input id="amount" name="amount" type="number" min="0" value="{{ old('amount', $budget->amount ?? 0) }}" placeholder="Contoh: 5000000" required>
            </div>

            <div class="field">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Opsional: anggaran untuk operasional bulan ini">{{ old('notes', $budget->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a class="button secondary" href="{{ route('fuel-usages.index') }}">Batal</a>
                <button class="button" type="submit">Simpan budget</button>
            </div>
        </form>
    </section>
@endsection
