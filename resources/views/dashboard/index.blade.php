@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
    @php
        $money = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $taskTotal = max(1, $stats['task_total']);
        $completionRate = min(100, round(($stats['completed_tasks'] / $taskTotal) * 100));
        $budgetUse = $stats['fuel_budget'] > 0 ? min(100, round(($stats['fuel_spent'] / $stats['fuel_budget']) * 100)) : 0;
        $nextTasks = $activities->whereIn('status', ['Proses', 'Pending'])->take(3);
        $maxMonthlyExpense = max(1, $monthlyExpenses->max());
        $maxCategoryExpense = max(1, $expenseCategories->max());
        $maxRequestStatus = max(1, $requestStatuses->max());
    @endphp

    <section class="dashboard-board">
        <div class="dashboard-grid">
            <div class="dash-card hero-card xl:col-span-3">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">General Affair</p>
                        <h1>Dashboard Operasional</h1>
                        <p class="lead">Ringkasan request, tagihan, petty cash, fuel usage, meterai, dan pekerjaan harian.</p>
                    </div>
                    <a class="button secondary compact" href="{{ route('dashboard', ['task_date' => $selectedDate]) }}">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 0 1-15.5 6.2"/><path d="M3 12A9 9 0 0 1 18.5 5.8"/><path d="M18 2v4h4"/><path d="M6 22v-4H2"/></svg>
                        Segarkan
                    </a>
                </div>

                <div class="overview-strip">
                    <div>
                        <span class="soft-icon rose">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <strong>Purchase Request</strong>
                        <small>Total PR: {{ $stats['total_requests'] }} data</small>
                    </div>
                    <div>
                        <span class="soft-icon violet">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11h3l9-6v14l-9-6H3z"/><path d="M21 9v6"/></svg>
                        </span>
                        <strong>Reminder</strong>
                        <small>{{ $stats['unpaid_bills'] }} belum bayar</small>
                    </div>
                    <div>
                        <span class="soft-icon mint">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                                <circle cx="7" cy="17" r="2" />
                                <circle cx="17" cy="17" r="2" />
                            </svg>
                        </span>
                        <strong>Fuel Usage</strong>
                        <small>{{ $budgetUse }}% bensin terpakai</small>
                    </div>
                    <div>
                        <span class="soft-icon lilac">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                        </span>
                        <strong>Schedule</strong>
                        <small>{{ $stats['active_tasks'] }} aktif</small>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="panel-title">Financial Summary</h2>
                        <span class="text-xs font-black text-slate-400">Live</span>
                    </div>
                    <div class="finance-grid">
                        <div class="finance-card mint lg:row-span-2">
                            <span>Sisa Budget Petty Cash</span>
                            <strong>{{ $money($stats['petty_cash_remaining']) }}</strong>
                            <small>Budget bulan ini {{ $money($stats['petty_cash_budget']) }}, terpakai {{ $money($stats['petty_cash_monthly_spent']) }}.</small>
                        </div>
                        <div class="finance-card green">
                            <span>Sisa Budget Bensin</span>
                            <strong>{{ $money($stats['fuel_remaining']) }}</strong>
                        </div>
                        <div class="finance-card amber">
                            <span>Bensin Terpakai Bulan Ini</span>
                            <strong>{{ $money($stats['fuel_monthly_spent']) }}</strong>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border-t border-slate-100 pt-6" aria-labelledby="analytics-title">
                    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow">Analytics &amp; Reports</p>
                            <h2 id="analytics-title" class="panel-title">Operational Overview {{ $currentYear }}</h2>
                            <p class="muted mt-1">Ringkasan aktual dari petty cash, purchase request, dan aktivitas kerja.</p>
                        </div>
                        <a class="button secondary compact" href="{{ route('reports.index') }}">
                            Lihat Reports
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-5">
                        @foreach ([
                            ['Total Operational Expense', $money($stats['operational_expense_monthly']), 'Bulan berjalan', 'bg-rose-50 text-rose-700'],
                            ['Petty Cash Terpakai', $money($stats['petty_cash_monthly_spent']), 'Bulan berjalan', 'bg-amber-50 text-amber-700'],
                            ['Sisa Budget Petty Cash', $money($stats['petty_cash_remaining']), 'Bulan berjalan', 'bg-emerald-50 text-emerald-700'],
                            ['Total Budget Bensin', $money($stats['fuel_budget']), 'Bulan berjalan', 'bg-sky-50 text-sky-700'],
                            ['Total Bensin Terpakai', $money($stats['fuel_monthly_spent']), 'Bulan berjalan', 'bg-violet-50 text-violet-700'],
                            ['PR Pending / Waiting Approval', $stats['pending_requests'], 'Status aktif', 'bg-orange-50 text-orange-700'],
                            ['Maintenance Request Open', $stats['open_maintenance'], 'Status aktif', 'bg-red-50 text-red-700'],
                            ['Total Asset', $stats['total_asset'], 'Belum tersedia', 'bg-slate-100 text-slate-700'],
                            ['Low Stock Item', $stats['low_stock_items'], 'Belum tersedia', 'bg-slate-100 text-slate-700'],
                        ] as [$label, $value, $meta, $color])
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wide {{ $color }}">{{ $meta }}</span>
                                <p class="mt-3 text-xs font-black uppercase tracking-wide text-slate-400">{{ $label }}</p>
                                <strong class="mt-1 block text-lg font-black text-slate-900">{{ $value }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        <div class="chart-legend">
                            <div class="chart-legend-head">
                                <strong>Monthly Operational Expense</strong>
                                <span class="muted">{{ $currentYear }}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-12 items-end gap-2" style="height: 170px;">
                                @foreach ($monthlyExpenses as $month => $amount)
                                    <div class="flex h-full flex-col items-center justify-end gap-2">
                                        <div class="w-full rounded-t-md bg-[#7770c6] transition-all hover:bg-[#5e58ad]" style="height: {{ max(4, round(($amount / $maxMonthlyExpense) * 100)) }}%;" title="{{ $money($amount) }}"></div>
                                        <span class="text-[10px] font-black text-slate-400">{{ \Carbon\Carbon::create()->month($month)->format('M') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="chart-legend">
                            <div class="chart-legend-head">
                                <strong>Expense by Category</strong>
                                <span class="muted">Petty Cash {{ $currentYear }}</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ($expenseCategories as $category => $amount)
                                    <div>
                                        <div class="mb-1 flex justify-between gap-3 text-xs font-black"><span>{{ $category }}</span><span class="text-slate-400">{{ $money($amount) }}</span></div>
                                        <div class="progress-track"><div class="h-full bg-[#9aa6d8]" style="width: {{ round(($amount / $maxCategoryExpense) * 100) }}%;"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        <div class="chart-legend">
                            <div class="chart-legend-head"><strong>PR Analytics</strong><span class="muted">{{ $stats['total_requests'] }} PR</span></div>
                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($requestStatuses as $status => $total)
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <div class="text-xs font-bold text-slate-500">{{ $status }}</div>
                                        <div class="mt-1 text-xl font-black text-slate-900">{{ $total }}</div>
                                        <div class="progress-track mt-2"><div class="h-full bg-[#f0a6a6]" style="width: {{ round(($total / $maxRequestStatus) * 100) }}%;"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="chart-legend">
                            <div class="chart-legend-head"><strong>Operational Activity</strong><span class="muted">Bulan berjalan</span></div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                @foreach ([
                                    ['Request masuk', $stats['requests_this_month']],
                                    ['Request selesai', $stats['completed_requests_this_month']],
                                    ['Masih pending', $stats['pending_activities']],
                                    ['Maintenance', $stats['maintenance_requests']],
                                    ['Vehicle request', $stats['vehicle_requests']],
                                ] as [$label, $total])
                                    <div class="rounded-xl border border-slate-100 bg-white p-3 shadow-sm">
                                        <div class="text-xs font-bold text-slate-500">{{ $label }}</div>
                                        <div class="mt-1 text-xl font-black text-slate-900">{{ $total }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="dash-card calendar-mini">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="panel-title">{{ $calendarStart->translatedFormat('M Y') }}</h2>
                    <span class="text-xs font-black text-slate-400">{{ $stats['task_total'] }} tugas</span>
                </div>
                <div class="mini-calendar-grid">
                    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                        <b>{{ $dayName }}</b>
                    @endforeach
                    @foreach ($calendarDays as $day)
                        @php($dayDate = $day ? $day['date']->format('Y-m-d') : null)
                        <a href="{{ $day ? route('dashboard', ['task_date' => $dayDate]) : '#' }}" class="{{ $selectedDate === $dayDate ? 'active' : '' }} {{ $day && $day['date']->isToday() ? 'today' : '' }} {{ $day && $day['tasks']->isNotEmpty() ? 'has-task' : '' }} {{ $day && ($day['holiday'] || $day['is_weekend']) ? 'holiday' : '' }}" title="{{ $day ? ($day['holiday'] ?? ($day['is_weekend'] ? 'Akhir pekan - libur' : '')) : '' }}">
                            {{ $day ? $day['date']->day : '' }}
                        </a>
                    @endforeach
                </div>
            </div>


            <a class="dash-card metric-card purple" href="{{ route('activities.index') }}">
                <div class="flex items-start justify-between">
                    <span>Task</span>
                    <span class="round-action light">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
                    </span>
                </div>
                <strong>{{ $completionRate }}%</strong>
                <small>{{ $stats['completed_tasks'] }} dari {{ $stats['task_total'] }} tugas selesai.</small>
            </a>
             <a class="dash-card metric-card purple" href="{{ route('stamp-movements.index') }}">
                <div class="flex items-start justify-between">
                    <span>Stamp</span>
                    <span class="round-action light">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
                    </span>
                </div>
                <strong>{{ number_format($stats['stamp_remaining'], 0, ',', '.') }}</strong>
                <small>Sisa meterai tersedia</small>
            </a>

            <div class="dash-card score-card">
                <div class="profile-line">
                    <div class="avatar small">GA</div>
                    <div><strong>Request Items</strong><span>Outstanding</span></div>
                </div>
                <div class="score">{{ $stats['total_requests'] }}</div>
                <p>Belum Dibayar: {{ $money($stats['request_unpaid_amount']) }}</p>
            </div>

            <div class="dash-card score-card">
                <div class="profile-line">
                    <div class="avatar small coral">GA</div>
                    <div><strong>Reminder Bills</strong><span>{{ $stats['overdue_bills'] }} overdue</span></div>
                </div>
                <div class="score">{{ $stats['unpaid_bills'] }}</div>
                <p>Tagihan belum dibayar atau masih draft.</p>
            </div>
        </div>
    </section>

    <section class="panel task-panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Kalender Tugas</h2>
                <span class="muted">Tambah, edit, dan tandai tugas selesai langsung dari dashboard.</span>
            </div>
        </div>

        <div class="panel-body">
            <div class="grid gap-4 lg:grid-cols-[360px_minmax(0,1fr)]">
                <div class="form-panel">
                    <h3 class="panel-title">{{ $editingActivity ? 'Edit Tugas' : 'Tambah Tugas' }}</h3>
                    <form method="POST" action="{{ $editingActivity ? route('activities.update', $editingActivity) : route('activities.store') }}">
                        @csrf
                        @if ($editingActivity)
                            @method('PUT')
                        @endif

                        <div class="form-grid">
                            <div class="field">
                                <label for="date">Tanggal</label>
                                <input id="date" name="date" type="date" value="{{ old('date', $editingActivity?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                            </div>

                            <div class="field">
                                <label for="category">Kategori</label>
                                <select id="category" name="category" required>
                                    <option value="">Pilih kategori</option>
                                    <option value="GA" {{ old('category', $editingActivity?->category) === 'GA' ? 'selected' : '' }}>GA</option>
                                    <option value="IT Support" {{ old('category', $editingActivity?->category) === 'IT Support' ? 'selected' : '' }}>IT Support</option>
                                </select>
                            </div>

                            <div class="field full">
                                <label for="title">Judul Tugas</label>
                                <input id="title" name="title" type="text" value="{{ old('title', $editingActivity?->title) }}" placeholder="Contoh: Cek printer" required>
                            </div>

                            <div class="field full">
                                <label for="description">Deskripsi</label>
                                <textarea id="description" name="description" placeholder="Catatan tambahan">{{ old('description', $editingActivity?->description) }}</textarea>
                            </div>

                            <div class="field">
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    <option value="Selesai" {{ old('status', $editingActivity?->status) === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="Proses" {{ old('status', $editingActivity?->status) === 'Proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="Pending" {{ old('status', $editingActivity?->status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            @if ($editingActivity)
                                <a class="button secondary" href="{{ route('dashboard', ['task_date' => $selectedDate]) }}">Batal</a>
                            @endif
                            <button class="button" type="submit">{{ $editingActivity ? 'Perbarui Tugas' : 'Simpan Tugas' }}</button>
                        </div>
                    </form>
                </div>

                <div>
                    <div class="calendar">
                        <div class="calendar-title">{{ $calendarStart->translatedFormat('F Y') }}</div>
                        <div class="calendar-grid">
                            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                                <div class="calendar-weekday">{{ $dayName }}</div>
                            @endforeach

                            @foreach ($calendarDays as $day)
                                @php($dayDate = $day ? $day['date']->format('Y-m-d') : null)
                                <a href="{{ $day ? route('dashboard', ['task_date' => $dayDate]) : '#' }}" class="calendar-day {{ $selectedDate && $selectedDate === $dayDate ? 'selected-blue' : '' }} {{ $day && ($day['holiday'] || $day['is_weekend']) ? 'holiday' : '' }}" title="{{ $day ? ($day['holiday'] ?? ($day['is_weekend'] ? 'Akhir pekan - libur' : '')) : '' }}">
                                    @if ($day)
                                        <div class="calendar-date">{{ $day['date']->day }} @if($day['holiday'] || $day['is_weekend'])<span class="holiday-mark">Libur</span>@endif</div>
                                        @if($day['holiday'] || $day['is_weekend'])<div class="calendar-holiday">{{ Str::limit($day['holiday'] ?: 'Akhir pekan', 24) }}</div>@endif
                                        @if ($day['tasks']->isEmpty())
                                            <div class="calendar-empty">Kosong</div>
                                        @else
                                            @foreach ($day['tasks'] as $task)
                                                <div class="calendar-pill {{ $task->status === 'Selesai' ? 'amber' : 'blue' }}">
                                                    {{ Str::limit($task->title, 26) }}
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if ($selectedDate)
                        <div class="detail-box blue">
                            <div class="detail-title">Tugas untuk {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</div>

                            @if ($selectedTasks->isEmpty())
                                <div class="muted">Tidak ada tugas di tanggal ini.</div>
                            @else
                                <div class="detail-list">
                                    @foreach ($selectedTasks as $task)
                                        <div class="detail-item blue">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <strong>{{ $task->title }}</strong>
                                                    <div class="muted text-sm">{{ $task->category }} - {{ $task->status }}</div>
                                                </div>
                                                <div class="flex gap-2">
                                                    <a class="button secondary compact" href="{{ route('dashboard', ['task_date' => $selectedDate, 'edit_task' => $task->id]) }}">Edit</a>
                                                    <form class="inline-form" action="{{ route('activities.complete', $task) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="button success compact" type="submit">Selesai</button>
                                                    </form>
                                                </div>
                                            </div>
                                            @if ($task->description)
                                                <p class="muted mt-2">{{ $task->description }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Ringkasan Data Terbaru</h2>
                <span class="muted">Entri terakhir dari request, bill, petty cash, fuel, dan aktivitas.</span>
            </div>
        </div>

        <div class="panel-body stack">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="panel p-4">
                    <h3 class="panel-title">Request Terakhir</h3>
                    @if($recentRequestItems->isEmpty())
                        <p class="muted">Belum ada request.</p>
                    @else
                        <ul class="mt-3 space-y-2">
                            @foreach($recentRequestItems as $item)
                                <li class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <strong>{{ $item->name }}</strong>
                                    <div class="muted text-sm">{{ $item->status }} - {{ $money($item->amount) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="panel p-4">
                    <h3 class="panel-title">Tagihan Terakhir</h3>
                    @if($recentBills->isEmpty())
                        <p class="muted">Belum ada tagihan.</p>
                    @else
                        <ul class="mt-3 space-y-2">
                            @foreach($recentBills as $item)
                                <li class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <strong>{{ $item->name }}</strong>
                                    <div class="muted text-sm">{{ $item->status }} - {{ $money($item->amount) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="panel p-4">
                    <h3 class="panel-title">Petty Cash Terakhir</h3>
                    @if($recentPettyCashes->isEmpty())
                        <p class="muted">Belum ada petty cash.</p>
                    @else
                        <ul class="mt-3 space-y-2">
                            @foreach($recentPettyCashes as $item)
                                <li class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <strong>{{ $item->description }}</strong>
                                    <div class="muted text-sm">{{ $money($item->amount) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="panel p-4">
                    <h3 class="panel-title">Fuel Usage Terakhir</h3>
                    @if($recentFuelUsages->isEmpty())
                        <p class="muted">Belum ada data bensin.</p>
                    @else
                        <ul class="mt-3 space-y-2">
                            @foreach($recentFuelUsages as $item)
                                <li class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <strong>{{ $item->driver_name ?: 'Bensin' }}</strong>
                                    <div class="muted text-sm">{{ $money($item->amount) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="panel p-4">
                <h3 class="panel-title">Aktivitas Terbaru</h3>
                @if($activities->isEmpty())
                    <p class="muted">Belum ada aktivitas.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($activities->take(5) as $task)
                            <li class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <strong>{{ $task->title }}</strong>
                                <div class="muted text-sm">{{ $task->date?->format('d M Y') ?? '-' }} - {{ $task->status }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>
@endsection
