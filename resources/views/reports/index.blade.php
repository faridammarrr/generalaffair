@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php($money = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.'))

    <section class="page-header">
        <div>
            <p class="eyebrow">Analytics &amp; Reports</p>
            <h1>Laporan operasional</h1>
            <p class="lead">Gunakan filter untuk meninjau expense, purchase request, dan petty cash dari data aktual.</p>
        </div>
        <div class="flex flex-wrap gap-2 max-sm:flex-col">
            <button class="button secondary compact" type="button" onclick="window.print()">Print</button>
            <button class="button secondary compact" type="button" onclick="window.print()">Export PDF</button>
            <a class="button compact" href="{{ route('reports.export', array_merge(['type' => 'expense'], request()->query())) }}">Export Excel</a>
        </div>
    </section>

    <section class="panel print:hidden">
        <div class="panel-header"><div><h2 class="panel-title">Filter Laporan</h2><span class="muted">Filter diterapkan ke seluruh laporan yang relevan.</span></div></div>
        <form class="panel-body" method="GET" action="{{ route('reports.index') }}">
            <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                <div class="field"><label for="start_date">Dari tanggal</label><input id="start_date" name="start_date" type="date" value="{{ $filters['start_date'] }}"></div>
                <div class="field"><label for="end_date">Sampai tanggal</label><input id="end_date" name="end_date" type="date" value="{{ $filters['end_date'] }}"></div>
                <div class="field"><label for="month">Bulan</label><select id="month" name="month"><option value="">Semua bulan</option>@foreach(range(1, 12) as $month)<option value="{{ $month }}" @selected($filters['month'] === $month)>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>@endforeach</select></div>
                <div class="field"><label for="year">Tahun</label><select id="year" name="year"><option value="">Semua tahun</option>@foreach(range(now()->year, now()->year - 4) as $year)<option value="{{ $year }}" @selected($filters['year'] === $year)>{{ $year }}</option>@endforeach</select></div>
                <div class="field"><label for="category">Kategori</label><select id="category" name="category"><option value="">Semua kategori</option>@foreach(['ATK', 'Konsumsi', 'Transport', 'Maintenance', 'Utilities', 'Lainnya', 'Petty Cash', 'Direct Payment'] as $category)<option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>@endforeach</select></div>
                <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Semua status</option>@foreach(['Draft', 'Submitted', 'Waiting Approval', 'Approved', 'Rejected', 'Completed', 'Paid'] as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>@endforeach</select></div>
            </div>
            <div class="form-actions"><a class="button secondary" href="{{ route('reports.index') }}">Reset</a><button class="button" type="submit">Terapkan Filter</button></div>
        </form>
    </section>

    <section class="panel report-section">
        <div class="panel-header"><div><h2 class="panel-title">A. Operational Expense Report</h2><span class="muted">{{ $expenseItems->count() }} transaksi petty cash</span></div><a class="button secondary compact print:hidden" href="{{ route('reports.export', array_merge(['type' => 'expense'], request()->query())) }}">Export Excel</a></div>
        <div class="overflow-x-auto"><table><thead><tr><th>Tanggal</th><th>Nomor transaksi</th><th>Kategori</th><th>Deskripsi</th><th>Nominal</th><th>PIC</th><th>Status</th></tr></thead><tbody>@forelse($expenseItems as $item)<tr><td>{{ $item->date?->format('d M Y') ?? '-' }}</td><td>PC-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $item->category ?: 'Lainnya' }}</td><td>{{ $item->description }}</td><td>{{ $money($item->amount) }}</td><td>{{ $item->person_name ?: '-' }}</td><td>Recorded</td></tr>@empty<tr><td colspan="7" class="muted">No Data</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="panel report-section">
        <div class="panel-header"><div><h2 class="panel-title">B. Purchase Request Report</h2><span class="muted">{{ $requestItems->count() }} purchase request</span></div><a class="button secondary compact print:hidden" href="{{ route('reports.export', array_merge(['type' => 'request'], request()->query())) }}">Export Excel</a></div>
        <div class="overflow-x-auto"><table><thead><tr><th>Nomor PR</th><th>Requester</th><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Nominal</th><th>Status</th></tr></thead><tbody>@forelse($requestItems as $item)<tr><td>{{ $item->letter_number ?: 'PR-' . str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $item->requestor_name ?: '-' }}</td><td>{{ $item->request_date?->format('d M Y') ?? '-' }}</td><td>{{ $item->type }}</td><td>{{ $item->name }}</td><td>{{ $money($item->amount) }}</td><td>{{ $item->status }}</td></tr>@empty<tr><td colspan="7" class="muted">No Data</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="panel report-section">
        <div class="panel-header"><div><h2 class="panel-title">C. Petty Cash Report</h2><span class="muted">{{ $pettyCashItems->count() }} transaksi petty cash</span></div><a class="button secondary compact print:hidden" href="{{ route('reports.export', array_merge(['type' => 'petty-cash'], request()->query())) }}">Export Excel</a></div>
        <div class="overflow-x-auto"><table><thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Pengeluaran</th><th>Saldo</th></tr></thead><tbody>@php($runningSpent = 0) @forelse($pettyCashItems as $item) @php($runningSpent += $item->amount)<tr><td>{{ $item->date?->format('d M Y') ?? '-' }}</td><td>{{ $item->category ?: 'Lainnya' }}</td><td>{{ $item->description }}</td><td>{{ $money($item->amount) }}</td><td>{{ $money($pettyCashItems->sum('amount') - $runningSpent) }}</td></tr>@empty<tr><td colspan="5" class="muted">No Data</td></tr>@endforelse</tbody></table></div>
    </section>
@endsection
