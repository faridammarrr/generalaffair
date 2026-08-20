<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'General Affair')</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="app-shell">
        <aside class="sidebar">
            <div class="brand-wrap">
                <a href="{{ route('dashboard') }}" class="brand">
                    <div class="brand-mark">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
                    </div>
                    <div class="brand-copy">
                        <span class="brand-title">Dashboard</span>
                    </div>
                </a>
            </div>

            <nav class="nav" aria-label="Navigasi utama">
                <a class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8v-10h-8v10Zm0-18v6h8V3h-8Z"/></svg>
                    Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('request-items.*') ? 'active' : '' }}" href="{{ route('request-items.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6h15l-2 8H8L6 3H3"/><path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/><path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                    Request
                </a>
                <a class="nav-link {{ request()->routeIs('stamp-movements.*') ? 'active' : '' }}" href="{{ route('stamp-movements.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10v18H7z"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h3"/></svg>
                    Meterai
                </a>
                <a class="nav-link {{ request()->routeIs('bills.*') ? 'active' : '' }}" href="{{ route('bills.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2Z"/><path d="M9 8h6"/><path d="M9 12h6"/></svg>
                    Reminder Bills
                </a>
                <a class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5"/><path d="M8 17V9"/><path d="M12 19V7"/><path d="M16 15v-5"/><path d="M20 19V4"/></svg>
                    Tracking
                </a>
                <a class="nav-link {{ request()->routeIs('petty-cashes.*') ? 'active' : '' }}" href="{{ route('petty-cashes.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h18v13H3z"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M12 12h.01"/></svg>
                    Petty Cash
                </a>
                <a class="nav-link {{ request()->routeIs('petty-cashes.budgets*') ? 'active' : '' }}" href="{{ route('petty-cashes.budgets') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16"/><path d="M7 16V8"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg>
                    Budget
                </a>
                <a class="nav-link {{ request()->routeIs('fuel-usages.*') ? 'active' : '' }}" href="{{ route('fuel-usages.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h13l2 4v5H4z"/><path d="M8 8V5h6v3"/><path d="M8 16h.01"/><path d="M15 16h.01"/></svg>
                    Fuel Usage
                </a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5"/><path d="M8 17V9"/><path d="M12 19V7"/><path d="M16 15v-5"/><path d="M20 19V4"/></svg>
                    Reports
                </a>
            </nav>
        </aside>

        <div class="workspace">
            <header class="workspace-topbar">
                <div class="workspace-search">
                    <div class="top-avatar">F</div>
                    <span>General Affair Dashboard</span>
                </div>
            </header>

            @if (session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif

            @yield('content')

            <footer class="mt-6 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <div class="flex items-center justify-between gap-4 max-sm:flex-col max-sm:items-start">
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-full bg-violet-100 text-xs font-black text-violet-700">GA</div>
                        <div>
                            <div class="text-sm font-black text-slate-800">General Affair &amp; IT Support</div>
                            <div class="text-xs font-semibold text-slate-500">Farid Ammar</div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </main>

    <dialog id="custom-confirm-modal" class="fixed inset-0 z-50 m-auto w-[calc(100%-2.5rem)] max-w-md p-0 rounded-2xl border border-slate-200 bg-white shadow-2xl backdrop:bg-slate-950/60 backdrop:backdrop-blur-sm">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600">
                    <svg class="h-6 w-6 fill-none stroke-current stroke-2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h3 id="confirm-modal-title" class="text-lg font-black text-slate-950">Konfirmasi Hapus</h3>
                    <p id="confirm-modal-message" class="mt-1 text-sm text-slate-600">Apakah Anda yakin ingin menghapus data ini?</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-4">
                <button id="confirm-modal-cancel" type="button" class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Batal
                </button>
                <button id="confirm-modal-accept" type="button" class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-md transition hover:bg-red-700">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </dialog>
</body>
</html>
