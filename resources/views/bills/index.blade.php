@extends('layouts.app')

@section('title', 'Reminder Bills')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Reminder Bills</p>
            <h1>Pantau reminder tagihan yang harus dibayar.</h1>
            <p class="lead">Simpan daftar tagihan vendor, nominal, due date, dan status pembayarannya dalam satu halaman.</p>
        </div>

        <a class="button" href="{{ route('bills.create') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Reminder
        </a>
    </section>

    <section class="stats" aria-label="Ringkasan Reminder Bills">
        <div class="stat">
            <p class="stat-label">Unpaid</p>
            <p class="stat-value">{{ $stats['unpaid_count'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Nominal Pending</p>
            <p class="stat-value">Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Overdue</p>
            <p class="stat-value">{{ $stats['overdue_count'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Paid</p>
            <p class="stat-value">{{ $stats['paid_count'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Settlement</p>
            <p class="stat-value">Rp {{ number_format($stats['remaining_return'], 0, ',', '.') }}</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Grafik Reminder Bills</h2>
                <span class="muted">Visual ringkasan status reminder Anda</span>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card amber">
                <div class="chart-kicker">Status Reminder</div>
                <div class="chart-value">{{ $stats['unpaid_count'] }} belum dibayar</div>
                <div class="chart-meta">Total reminder: {{ $bills->count() }}</div>
            </div>

            <div class="chart-legend">
                <div class="chart-legend-head">
                    <strong>Komposisi Status</strong>
                    <span class="muted">{{ $bills->count() }} data</span>
                </div>
                <div class="progress-track">
                    @foreach ($chartData as $item)
                        <div class="h-full {{ $item['class'] }}" style="width: {{ $bills->isEmpty() ? 0 : round(($item['value'] / max($bills->count(), 1)) * 100) }}%;"></div>
                    @endforeach
                </div>
                <div class="legend-list">
                    @foreach ($chartData as $item)
                        <div class="legend-item">
                            <span class="legend-dot {{ $item['class'] }}"></span>
                            <span>{{ $item['label'] }}: {{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Reminder Pembayaran</h2>
                <span class="muted">Reminder yang akan jatuh tempo dalam 7 hari dan yang sudah terlambat</span>
            </div>
        </div>

        @if ($upcomingBills->isNotEmpty() || $overdueBills->isNotEmpty())
            <div class="alert-stack">
                @if ($overdueBills->isNotEmpty())
                    <div class="alert danger">
                        <strong>Sudah terlambat</strong>
                        <ul>
                            @foreach ($overdueBills as $bill)
                                <li>{{ $bill->name }} - jatuh tempo {{ $bill->due_date->format('d M Y') }} (Rp {{ number_format($bill->amount, 0, ',', '.') }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($upcomingBills->isNotEmpty())
                    <div class="alert warning">
                        <strong>Akan jatuh tempo</strong>
                        <ul>
                            @foreach ($upcomingBills as $bill)
                                <li>{{ $bill->name }} - {{ $bill->due_date->format('d M Y') }} (Rp {{ number_format($bill->amount, 0, ',', '.') }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @else
            <div class="empty">
                <strong>Tidak ada reminder untuk minggu ini.</strong>
                Semua reminder yang belum dibayar masih aman untuk saat ini.
            </div>
        @endif
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Kalender Reminder Bills</h2>
                <span class="muted">Lihat reminder yang jatuh tempo per tanggal.</span>
            </div>
        </div>

        <div class="calendar">
            <div class="calendar-title">{{ ucfirst($calendarMonthLabel) }}</div>
            <div class="calendar-grid">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                    <div class="calendar-weekday">{{ $dayName }}</div>
                @endforeach

                @foreach ($calendarDays as $day)
                    @php($dayDate = $day ? $day['date']->format('Y-m-d') : null)
                    <a href="{{ $day ? route('bills.index', ['date' => $dayDate]) : '#' }}" class="calendar-day {{ $selectedDate === $dayDate ? 'selected-amber' : '' }} {{ $day && ($day['holiday'] || $day['is_weekend']) ? 'holiday' : '' }}" title="{{ $day ? ($day['holiday'] ?? ($day['is_weekend'] ? 'Akhir pekan - libur' : '')) : '' }}">
                        @if ($day)
                            <div class="calendar-date">{{ $day['date']->day }} @if($day['holiday'] || $day['is_weekend'])<span class="holiday-mark">Libur</span>@endif</div>
                            @if($day['holiday'] || $day['is_weekend'])<div class="calendar-holiday">{{ Str::limit($day['holiday'] ?: 'Akhir pekan', 24) }}</div>@endif
                            @if ($day['bills']->isEmpty())
                                <div class="calendar-empty">Tidak ada</div>
                            @else
                                @foreach ($day['bills'] as $bill)
                                    <div class="calendar-pill amber">
                                        {{ Str::limit($bill->name, 28) }}
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="h-full"></div>
                        @endif
                    </a>
                @endforeach
            </div>

            @if ($selectedDate)
                <div class="detail-box amber">
                    <div class="detail-title">Detail untuk {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</div>
                    @if ($selectedBills->isEmpty())
                        <div class="muted">Tidak ada reminder yang jatuh tempo pada tanggal ini.</div>
                    @else
                        <div class="detail-list">
                            @foreach ($selectedBills as $bill)
                                <div class="detail-item amber">
                                    <div class="font-bold">{{ $bill->name }}</div>
                                    <div class="muted mt-1 text-sm">{{ $bill->vendor ?: '-' }}</div>
                                    <div class="muted mt-0.5 text-sm">Rp {{ number_format($bill->amount, 0, ',', '.') }} / {{ $bill->status }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Daftar Reminder Bills</h2>
                <span class="muted">{{ $bills->count() }} reminder tercatat</span>
            </div>
        </div>

        @if ($bills->isEmpty())
            <div class="empty">
                <strong>Belum ada reminder.</strong>
                Tambahkan reminder vendor atau operasional yang perlu dipantau.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reminder</th>
                            <th>Vendor</th>
                            <th>Nominal</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bills as $bill)
                            <tr>
                                <td>
                                    <strong>{{ $bill->name }}</strong>
                                    @if ($bill->notes)
                                        <div class="muted">{{ $bill->notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $bill->vendor ?: '-' }}</td>
                                <td class="amount">
                                    Rp {{ number_format($bill->amount, 0, ',', '.') }}
                                    @if ($bill->type === 'Cash Advance Settlement' && !empty($bill->settlement_amount))
                                        <div class="muted">Settlement: Rp {{ number_format($bill->settlement_amount, 0, ',', '.') }}</div>
                                        <div class="muted">Sisa kembalian: Rp {{ number_format(max(0, $bill->amount - $bill->settlement_amount), 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="muted">
                                    @if ($bill->due_date)
                                        {{ $bill->due_date->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $bill->status === 'Paid' ? 'paid' : 'submitted' }}">{{ $bill->status }}</span>
                                </td>
                                <td>
                                    <div class="actions">
                                        @if ($bill->status === 'Unpaid')
                                            <form class="inline-form" method="POST" action="{{ route('bills.paid', $bill) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="icon-button success" type="submit" title="Mark Paid" aria-label="Mark Paid">
                                                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                         @if ($bill->status === 'Submit')
                                            <form class="inline-form" method="POST" action="{{ route('bills.paid', $bill) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="icon-button success" type="submit" title="Mark Paid" aria-label="Mark Paid">
                                                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                         @if ($bill->status === 'Draft')
                                            <form class="inline-form" method="POST" action="{{ route('bills.submit', $bill) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="icon-button primary" type="submit" title="Submit Bills" aria-label="Submit Bills">
                                                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>                                                </button>
                                            </form>
                                        @endif

                                        <a class="icon-button secondary" href="{{ route('bills.edit', $bill) }}" title="Edit reminder" aria-label="Edit reminder">
                                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>

                                        <form class="inline-form" method="POST" action="{{ route('bills.destroy', $bill) }}" data-confirm="Apakah Anda yakin ingin menghapus reminder '{{ $bill->name }}'?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button danger" type="submit" title="Hapus reminder" aria-label="Hapus reminder">
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
