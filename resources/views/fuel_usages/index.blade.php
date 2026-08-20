@extends('layouts.app')

@section('title', 'Fuel Usage Monitoring')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Fuel Usage</p>
            <h1>Budget bensin bulanan dan riwayat pembelian supir.</h1>
            <p class="lead">Finance bisa menetapkan budget per bulan, lalu supir mencatat pembelian bensin yang mengurangi sisa budget.</p>
        </div>

        <a class="button" href="{{ route('fuel-usages.create') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Pembelian
        </a>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Set Budget Bulanan</h2>
                <span class="muted">Tentukan anggaran untuk bulan tertentu</span>
            </div>
            <a class="button secondary" href="{{ route('fuel-usages.budgets.create') }}">Tambah budget</a>
        </div>
    </section>

    <section class="stats" aria-label="Ringkasan budget bensin">
        <div class="stat">
            <p class="stat-label">Budget Bulan Ini</p>
            <p class="stat-value">Rp {{ number_format($stats['budget_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Total Pembelian</p>
            <p class="stat-value">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Sisa Budget</p>
            <p class="stat-value">Rp {{ number_format($stats['remaining'], 0, ',', '.') }}</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Riwayat Pembelian Bensin</h2>
                <span class="muted">{{ $items->count() }} transaksi tercatat</span>
            </div>
        </div>

        <form method="GET" action="{{ route('fuel-usages.index') }}" class="stack px-5 pt-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div class="field">
                    <label for="month">Bulan</label>
                    <input id="month" name="month" type="number" min="1" max="12" value="{{ $request->input('month', now()->month) }}">
                </div>
                <div class="field">
                    <label for="year">Tahun</label>
                    <input id="year" name="year" type="number" min="2000" value="{{ $request->input('year', now()->year) }}">
                </div>
                <div class="field">
                    <label for="start_date">Dari tanggal</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $request->input('start_date') }}">
                </div>
                <div class="field">
                    <label for="end_date">Sampai tanggal</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $request->input('end_date') }}">
                </div>
                <div class="actions">
                    <button class="button secondary" type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('fuel-usages.index') }}">Reset</a>
                </div>
            </div>
        </form>

        @if ($items->isEmpty())
            <div class="empty">
                <strong>Belum ada pembelian bensin.</strong>
                Catat pembelian pertama untuk bulan ini.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Supir</th>
                            <th>Nominal</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td class="muted">
                                    {{ $item->date ? $item->date->format('d M Y') : '-' }}
                                </td>
                                <td>{{ $item->driver_name ?: '-' }}</td>
                                <td class="amount">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                <td>{{ $item->description ?? $item->notes ?: '-' }}</td>
                                <td>
                                    <div class="actions">
                                        <a class="icon-button secondary" href="{{ route('fuel-usages.edit', $item) }}" title="Edit catatan" aria-label="Edit catatan">
                                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>

                                        <form class="inline-form" method="POST" action="{{ route('fuel-usages.destroy', $item) }}" data-confirm="Apakah Anda yakin ingin menghapus pembelian bensin ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button danger" type="submit" title="Hapus catatan" aria-label="Hapus catatan">
                                                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
