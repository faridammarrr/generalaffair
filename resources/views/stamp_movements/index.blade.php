@extends('layouts.app')

@section('title', 'Stok Meterai')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Meterai</p>
            <h1>Pantau sisa meterai.</h1>
            <p class="lead">Catat meterai masuk dan keluar, lalu stok sisa akan dihitung otomatis dari riwayat mutasi.</p>
        </div>

        <a class="button" href="{{ route('stamp-movements.create') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Catat Mutasi
        </a>
    </section>

    <section class="stats" aria-label="Ringkasan meterai">
        <div class="stat">
            <p class="stat-label">Sisa Meterai</p>
            <p class="stat-value">{{ number_format($stats['remaining'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Total Masuk</p>
            <p class="stat-value">{{ number_format($stats['total_in'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Total Keluar</p>
            <p class="stat-value">{{ number_format($stats['total_out'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Pinjam</p>
            <p class="stat-value">{{ number_format($stats['borrowed'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Transaksi</p>
            <p class="stat-value">{{ $stats['transactions'] }}</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Riwayat Mutasi Meterai</h2>
                <span class="muted">{{ $movements->count() }} mutasi tercatat</span>
            </div>
        </div>

        @if ($movements->isEmpty())
            <div class="empty">
                <strong>Belum ada mutasi meterai.</strong>
                Catat stok awal sebagai meterai masuk untuk mulai memantau sisa.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $movement)
                            <tr>
                                <td class="muted">{{ $movement->moved_at->format('d M Y') }}</td>
                                <td><strong>{{ $movement->description }}</strong></td>
                                <td>{{ $movement->person_name ?: '-' }}</td>
                                <td>{{ $movement->division ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $movement->direction === 'in' ? 'paid' : ($movement->direction === 'borrow' ? 'submitted' : 'draft') }}">
                                        {{ $movement->direction === 'in' ? 'Masuk' : ($movement->direction === 'borrow' ? 'Pinjam' : 'Keluar') }}
                                    </span>
                                </td>
                                <td class="amount">{{ $movement->direction === 'in' ? '+' : '-' }}{{ number_format($movement->quantity, 0, ',', '.') }}</td>
                                <td>
                                    <div class="actions">
                                        @if ($movement->direction === 'borrow')
                                            <form class="inline-form" method="POST" action="{{ route('stamp-movements.return', $movement) }}">
                                                @csrf
                                                <button class="icon-button primary" type="submit" title="Kembalikan meterai" aria-label="Kembalikan meterai">
                                                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15.3-6.3M21 12a9 9 0 0 1-15.3 6.3"/><path d="M8 12h13"/><path d="M15 9l3 3-3 3"/></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <a class="icon-button secondary" href="{{ route('stamp-movements.edit', $movement) }}" title="Edit mutasi" aria-label="Edit mutasi">
                                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>

                                        <form class="inline-form" method="POST" action="{{ route('stamp-movements.destroy', $movement) }}" data-confirm="Apakah Anda yakin ingin menghapus mutasi meterai ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button danger" type="submit" title="Hapus mutasi" aria-label="Hapus mutasi">
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
