@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Reminder Bills' : 'Tambah Reminder Bills')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">{{ $mode === 'edit' ? 'Edit Reminder' : 'Reminder Baru' }}</p>
            <h1>{{ $mode === 'edit' ? 'Perbarui detail reminder bills.' : 'Catat reminder bills yang perlu dibayar.' }}</h1>
            <p class="lead">Isi nama tagihan, vendor, nominal, dan due date agar pembayaran tidak kelewat.</p>
        </div>

        <a class="button secondary" href="{{ route('bills.index') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </section>

    <form class="form-panel" method="POST" action="{{ $mode === 'edit' ? route('bills.update', $bill) : route('bills.store') }}">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field full">
                <label for="name">Nama Tagihan</label>
                <input id="name" name="name" value="{{ old('name', $bill->name) }}" placeholder="Contoh: Internet kantor Juli" required>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="vendor">Vendor</label>
                <input id="vendor" name="vendor" value="{{ old('vendor', $bill->vendor) }}" placeholder="Contoh: Telkom">
                @error('vendor')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="type">Jenis Tagihan</label>
                <select id="type" name="type">
                    @foreach (['', 'Purchase Requisition', 'Cash Advance Settlement'] as $option)
                        <option value="{{ $option }}" @selected(old('type', $bill->type ?? '') === $option)>{{ $option ?: 'Pilih jenis' }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="amount">Nominal</label>
                <input id="amount" name="amount" type="number" min="1" value="{{ old('amount', $bill->amount) }}" placeholder="750000" required>
                @error('amount')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="due_date">Due Date</label>
                <input id="due_date" name="due_date" type="date" value="{{ old('due_date', optional($bill->due_date)->format('Y-m-d')) }}">
                @error('due_date')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field {{ in_array(old('type', $bill->type ?? ''), ['Purchase Requisition', 'Cash Advance Settlement'], true) ? '' : 'hidden' }}" id="settlement-field">
                <label for="settlement_amount">Settlement Dana Dipakai</label>
                <input id="settlement_amount" name="settlement_amount" type="number" min="0" value="{{ old('settlement_amount', $bill->settlement_amount ?? '') }}" placeholder="500000">
                @error('settlement_amount')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach ([
                    'Unpaid', 
                    'Paid',
                    'Draft',
                    'Submit'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $bill->status ?? 'Unpaid') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field full">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" placeholder="Nomor invoice, periode, atau detail pembayaran">{{ old('notes', $bill->notes) }}</textarea>
                @error('notes')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeSelect = document.getElementById('type');
                const settlementField = document.getElementById('settlement-field');

                const toggleSettlementField = function () {
                    const shouldShow = ['Purchase Requisition', 'Cash Advance Settlement'].includes(typeSelect.value);
                    settlementField.classList.toggle('hidden', !shouldShow);
                };

                if (typeSelect && settlementField) {
                    typeSelect.addEventListener('change', toggleSettlementField);
                    toggleSettlementField();
                }
            });
        </script>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('bills.index') }}">Batal</a>
            <button class="button" type="submit">
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                Simpan
            </button>
        </div>
    </form>
@endsection
