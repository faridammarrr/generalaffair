@extends('layouts.app')

@section('title', 'Tracking Kegiatan')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Tracking Kegiatan</p>
            <h1>Tracking Kegiatan GA & IT Support</h1>
            <p class="lead">Catat aktivitas harian GA dan IT Support secara langsung dari website, lalu lihat riwayatnya dengan rapi.</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">{{ $editingActivity ? 'Edit Kegiatan' : 'Tambah Kegiatan' }}</h2>
                <span class="muted">{{ $editingActivity ? 'Perbarui data kegiatan yang sudah tersimpan.' : 'Input kegiatan harian yang sudah selesai atau sedang dikerjakan.' }}</span>
            </div>
        </div>

        <div class="panel-body">
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
                            <option value="">-- Pilih kategori --</option>
                            <option value="GA" {{ old('category', $editingActivity?->category) === 'GA' ? 'selected' : '' }}>GA</option>
                            <option value="IT Support" {{ old('category', $editingActivity?->category) === 'IT Support' ? 'selected' : '' }}>IT Support</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="title">Judul Kegiatan</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $editingActivity?->title) }}" placeholder="Contoh: Cek kebersihan kantor" required>
                    </div>

                    <div class="field full">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" placeholder="Tuliskan detail kegiatan, hasil, atau catatan tambahan...">{{ old('description', $editingActivity?->description) }}</textarea>
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
                        <a class="button secondary" href="{{ route('activities.index') }}">Batal</a>
                    @endif
                    <button class="button" type="submit">{{ $editingActivity ? 'Perbarui Kegiatan' : 'Simpan Kegiatan' }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Riwayat Kegiatan</h2>
                <span class="muted">Daftar input kegiatan GA dan IT Support yang sudah dicatat.</span>
            </div>
        </div>

        @if ($activities->isEmpty())
            <div class="empty">
                <strong>Belum ada kegiatan yang dicatat.</strong>
                Tambahkan kegiatan pertama Anda di form di atas.
            </div>
        @else
            <div class="activity-groups">
                @foreach ($activities as $dateKey => $dayActivities)
                    <div class="date-group">
                        <div class="date-group-header">
                            {{ \Carbon\Carbon::parse($dateKey)->translatedFormat('d M Y') }}
                        </div>
                        <div class="activity-list">
                            @foreach ($dayActivities as $activity)
                                <div class="activity-card">
                                    <div class="activity-card-head">
                                        <div>
                                            <strong>{{ $activity->title }}</strong>
                                            <div class="muted text-sm">{{ $activity->category }}</div>
                                        </div>
                                        <span class="badge {{ strtolower($activity->status) }}">{{ $activity->status }}</span>
                                    </div>
                                    @if ($activity->description)
                                        <div class="activity-description">{{ $activity->description }}</div>
                                    @endif
                                    <div class="activity-actions">
                                        <a class="icon-button secondary" href="{{ route('activities.edit', $activity) }}" title="Edit kegiatan" aria-label="Edit kegiatan">
                                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>
                                        <form class="inline-form" method="POST" action="{{ route('activities.destroy', $activity) }}" data-confirm="Apakah Anda yakin ingin menghapus kegiatan '{{ $activity->title }}'?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button danger" type="submit" title="Hapus kegiatan" aria-label="Hapus kegiatan">
                                                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
