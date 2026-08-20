@extends('layouts.app')

@section('title', 'Petty Cash')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Petty Cash</p>
            <h1>Catat Belanja Petty Cash</h1>
            <p class="lead">Pantau pengeluaran kecil operasional, kategori, dan penanggung jawab dalam satu tempat.</p>
        </div>

        <a class="button" href="{{ route('petty-cashes.create') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Catatan
        </a>
    </section>

    <section class="stats" aria-label="Ringkasan petty cash">
        <div class="stat">
            <p class="stat-label">Total Pengeluaran</p>
            <p class="stat-value">Rp {{ number_format($stats['total_spending'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Jumlah Catatan</p>
            <p class="stat-value">{{ $stats['count'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Catatan Terakhir</p>
            <p class="stat-value">Rp {{ number_format($stats['latest_amount'], 0, ',', '.') }}</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Daftar Belanja Petty Cash</h2>
                <span class="muted">{{ $items->count() }} catatan tercatat</span>
            </div>
        </div>

        <form method="GET" action="{{ route('petty-cashes.index') }}" class="stack px-5 pt-5">
            <div class="filter-row">
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
                    <a class="button secondary" href="{{ route('petty-cashes.index') }}">Reset</a>
                </div>
            </div>
        </form>

        @if ($items->isEmpty())
            <div class="empty">
                <strong>Belum ada catatan petty cash.</strong>
                Tambahkan belanja kecil yang perlu dipantau.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th>Nominal</th>
                            <th>Person</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td class="muted">
                                    {{ $item->date ? $item->date->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    <strong>{{ $item->description }}</strong>
                                    @if ($item->notes)
                                        <div class="muted">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->category ?: '-' }}</td>
                                <td class="amount">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                <td>{{ $item->person_name ?: '-' }}</td>
                                <td>
                                    @if ($item->invoice_path)
                                       <div class="flex items-center gap-2">

                                        <!-- View Button -->
                                        <a href="{{ route('petty-cashes.invoice', $item) }}" 
                                        target="_blank"
                                        class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition"
                                        title="View"
                                        aria-label="View">

                                            <svg xmlns="http://www.w3.org/2000/svg" 
                                                class="icon-button secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 
                                                        4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <!-- Download Button -->
                                        <a href="{{ route('petty-cashes.invoice', $item) }}" 
                                        download
                                        class="icon-button p-2 rounded-lg text-green-600 hover:bg-green-50 transition"
                                        title="Download"
                                        aria-label="Download">

                                            <svg xmlns="http://www.w3.org/2000/svg" 
                                                class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M7 10l5 5 5-5M12 15V3"/>
                                            </svg>
                                        </a>

                                    </div>
                                    @else
                                        <div class="muted">-</div>
                                    @endif
                                    <div class="actions">
                                        <a class="icon-button secondary" href="{{ route('petty-cashes.edit', $item) }}" title="Edit catatan" aria-label="Edit catatan">
                                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>

                                        <form class="inline-form" method="POST" action="{{ route('petty-cashes.destroy', $item) }}" data-confirm="Apakah Anda yakin ingin menghapus catatan petty cash ini?">
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
