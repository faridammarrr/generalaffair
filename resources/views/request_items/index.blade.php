@extends('layouts.app')

@section('title', 'Request General Affair')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Request Center</p>
            <h1>Kelola request operasional dengan jelas.</h1>
            <p class="lead">Pantau item yang masih draft, sudah diajukan, dan sudah dibayar dari satu halaman yang ringkas.</p>
        </div>

        <a class="button" href="{{ route('request-items.create') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Request
        </a>
    </section>

    <section class="stats" aria-label="Ringkasan request">
        <div class="stat">
            <p class="stat-label">Total Request</p>
            <p class="stat-value">{{ $stats['total_requests'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Unpaid Amount</p>
            <p class="stat-value">Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Submitted</p>
            <p class="stat-value">{{ $stats['submitted'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Paid</p>
            <p class="stat-value">{{ $stats['paid'] }}</p>
        </div>
    </section>

    <section class="stats" aria-label="Pantauan operasional">
      
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Grafik Request</h2>
                <span class="muted">Ringkasan status request berdasarkan progress pembayaran</span>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card blue">
                <div class="chart-kicker">Progress Request</div>
                <div class="chart-value">{{ $stats['total_requests'] }} total request</div>
                <div class="chart-meta">Total nominal: Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</div>
            </div>

            <div class="chart-legend">
                <div class="chart-legend-head">
                    <strong>Komposisi Status</strong>
                    <span class="muted">{{ $items->count() }} data</span>
                </div>
                <div class="progress-track">
                    @foreach ($requestChartData as $item)
                        <div class="h-full {{ $item['class'] }}" style="width: {{ $items->isEmpty() ? 0 : round(($item['value'] / max($items->count(), 1)) * 100) }}%;"></div>
                    @endforeach
                </div>
                <div class="legend-list">
                    @foreach ($requestChartData as $item)
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
                <h2 class="panel-title">Kalender Pembayaran</h2>
                <span class="muted">Tanggal jatuh tempo request yang perlu diperhatikan.</span>
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
                    <a href="{{ $day ? route('request-items.index', ['date' => $dayDate]) : '#' }}" class="calendar-day {{ $selectedDate === $dayDate ? 'selected-blue' : '' }} {{ $day && ($day['holiday'] || $day['is_weekend']) ? 'holiday' : '' }}" title="{{ $day ? ($day['holiday'] ?? ($day['is_weekend'] ? 'Akhir pekan - libur' : '')) : '' }}">
                        @if ($day)
                            <div class="calendar-date">{{ $day['date']->day }} @if($day['holiday'] || $day['is_weekend'])<span class="holiday-mark">Libur</span>@endif</div>
                            @if($day['holiday'] || $day['is_weekend'])<div class="calendar-holiday">{{ Str::limit($day['holiday'] ?: 'Akhir pekan', 24) }}</div>@endif
                            @if ($day['items']->isEmpty())
                                <div class="calendar-empty">Tidak ada</div>
                            @else
                                @foreach ($day['items'] as $item)
                                    <div class="calendar-pill blue">
                                        {{ Str::limit($item->name, 28) }}
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
                <div class="detail-box blue">
                    <div class="detail-title">Detail untuk {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</div>
                    @if ($selectedItems->isEmpty())
                        <div class="muted">Tidak ada request yang jatuh tempo pada tanggal ini.</div>
                    @else
                        <div class="detail-list">
                            @foreach ($selectedItems as $item)
                                <div class="detail-item blue">
                                    <div class="font-bold">{{ $item->name }}</div>
                                    <div class="muted mt-1 text-sm">{{ $item->requestor_name ?: '-' }} / {{ $item->division ?: '-' }}</div>
                                    <div class="muted mt-0.5 text-sm">Rp {{ number_format($item->amount, 0, ',', '.') }} / {{ $item->status }}</div>
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
                <h2 class="panel-title">Daftar Request</h2>
                <span class="muted">{{ $items->count() }} item tercatat</span>
            </div>
        </div>

        @if ($items->isEmpty())
            <div class="empty">
                <strong>Belum ada request.</strong>
                Buat request pertama untuk mulai mencatat kebutuhan GA.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Requestor</th>
                            <th>Nominal</th>
                            <th>Type</th>
                            <th>Budget</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if ($item->letter_number)
                                        <div class="muted">{{ $item->letter_number }}</div>
                                    @endif
                                    @if ($item->request_date)
                                        <div class="muted">Dibuat {{ $item->request_date->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $item->requestor_name ?: '-' }}</strong>
                                    @if ($item->division)
                                        <div class="muted">{{ $item->division }}</div>
                                    @endif
                                </td>
                                <td class="amount">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                <td>{{ $item->type }}</td>
                                <td>
                                    <strong>{{ $item->budget_id ?: '-' }}</strong>
                                    @if ($item->budget_name)
                                        <div class="muted">{{ $item->budget_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $item->receiver_name ?: '-' }}</strong>
                                    @if ($item->payment_due_date)
                                        <div class="muted">Due {{ $item->payment_due_date->format('d M Y') }}</div>
                                    @endif
                                    @if ($item->payment_method)
                                        <div class="muted">{{ $item->payment_method }}</div>
                                    @endif
                                    @if ($item->bank_account_number)
                                        <div class="muted">{{ $item->bank_account_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ strtolower($item->status) }}">{{ $item->status }}</span>
                                </td>
                                <td class="muted">
                                    @if ($item->status === 'Paid' && $item->paid_at)
                                        Paid {{ $item->paid_at->format('d M Y') }}
                                    @elseif ($item->status === 'Submitted' && $item->submitted_at)
                                        Submitted {{ $item->submitted_at->format('d M Y') }}
                                    @else
                                        Draft
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        @if ($item->status === 'Draft')
                                            <form class="inline-form" method="POST" action="{{ route('request-items.submit', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="icon-button primary" type="submit" title="Submit Request" aria-label="Submit Request">
                                                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                                </button>
                                            </form>
                                        @elseif ($item->status === 'Submitted')
                                            <form class="inline-form" method="POST" action="{{ route('request-items.paid', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="icon-button success" type="submit" title="Mark Paid" aria-label="Mark Paid">
                                                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <a class="icon-button secondary" href="{{ route('request-items.edit', $item) }}" title="Edit request" aria-label="Edit request">
                                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>

                                        <form class="inline-form" method="POST" action="{{ route('request-items.destroy', $item) }}" data-confirm="Apakah Anda yakin ingin menghapus request '{{ $item->name }}'?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button danger" type="submit" title="Hapus request" aria-label="Hapus request">
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
