@extends('layouts.app')

@section('title', 'Budget Petty Cash')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Budget Petty Cash</p>
            <h1>Kelola budget bulanan petty cash.</h1>
            <p class="lead">Atur batas anggaran per bulan dan lihat sisa saldo serta pengeluaran aktual.</p>
        </div>
    </section>

    <section class="stats" aria-label="Ringkasan budget">
        <div class="stat">
            <p class="stat-label">Budget Bulan Ini</p>
            <p class="stat-value">Rp {{ number_format($currentBudget?->amount ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Pengeluaran Bulan Ini</p>
            <p class="stat-value">Rp {{ number_format($monthlySpending, 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Sisa Budget</p>
            <p class="stat-value">Rp {{ number_format($remaining ?? 0, 0, ',', '.') }}</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Buat / Perbarui Budget</h2>
                <span class="muted">Budget akan dihitung per bulan</span>
            </div>
        </div>

        <form method="POST" action="{{ route('petty-cashes.budgets.store') }}" class="stack p-5">
            @csrf
            <div class="field">
                <label for="budget_date">Pilih bulan budget</label>
                <input id="budget_date" name="budget_date" type="date" value="{{ old('budget_date', now()->day(1)->format('Y-m-d')) }}" required>
                <div class="muted">Saat Anda memilih bulan, sistem akan memakai bulan itu sebagai batas budget bulanan.</div>
            </div>
            <div class="field">
                <label for="amount">Nominal Budget</label>
                <input id="amount" name="amount" type="number" min="1" value="{{ old('amount', $currentBudget?->amount ?? '') }}" required>
            </div>
            <div class="field">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="3">{{ old('notes', $currentBudget?->notes) }}</textarea>
            </div>
            <div class="actions">
                <button class="button" type="submit">Simpan Budget</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Riwayat Budget</h2>
                <span class="muted">Daftar budget bulanan yang pernah dibuat</span>
            </div>
        </div>

        <form method="GET" action="{{ route('petty-cashes.budgets') }}" class="stack px-5 pt-5">
            <div class="filter-row">
                <div class="field">
                    <label for="start_date">Dari bulan</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $request->input('start_date') }}">
                </div>
                <div class="field">
                    <label for="end_date">Sampai bulan</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $request->input('end_date') }}">
                </div>
                <div class="actions">
                    <button class="button secondary" type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('petty-cashes.budgets') }}">Reset</a>
                </div>
            </div>
        </form>

        @if ($budgets->isEmpty())
            <div class="empty">
                <strong>Belum ada budget petty cash.</strong>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Nominal</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budgets as $budget)
                            <tr>
                                <td>{{ $budget->month }}</td>
                                <td>{{ $budget->year }}</td>
                                <td class="amount">Rp {{ number_format($budget->amount, 0, ',', '.') }}</td>
                                <td>{{ $budget->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

