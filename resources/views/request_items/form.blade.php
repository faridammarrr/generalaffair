@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Request' : 'Tambah Request')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">{{ $mode === 'edit' ? 'Edit Request' : 'Request Baru' }}</p>
            <h1>{{ $mode === 'edit' ? 'Perbarui detail request.' : 'Catat kebutuhan baru.' }}</h1>
            <p class="lead">Isi data request, budget, penerima, dan detail pembayaran agar proses submit sampai paid mudah dilacak.</p>
        </div>

        <a class="button secondary" href="{{ route('request-items.index') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </section>

    <form class="form-panel" method="POST" action="{{ $mode === 'edit' ? route('request-items.update', $item) : route('request-items.store') }}">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field full">
                <label for="name">Nama Request</label>
                <input id="name" name="name" value="{{ old('name', $item->name) }}" placeholder="Contoh: Pembelian ATK bulanan" required>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="request_date">Tanggal Dibuat</label>
                <input id="request_date" name="request_date" type="date" value="{{ old('request_date', optional($item->request_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                @error('request_date')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="letter_number">Nomor Surat</label>
                <input id="letter_number" name="letter_number" value="{{ old('letter_number', $item->letter_number) }}" placeholder="Contoh: GA/PR/VII/001">
                @error('letter_number')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="requestor_name">Nama Requestor</label>
                <input id="requestor_name" name="requestor_name" value="{{ old('requestor_name', $item->requestor_name) }}" placeholder="Contoh: Farid Ammar">
                @error('requestor_name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="division">Divisi</label>
                <input id="division" name="division" value="{{ old('division', $item->division) }}" placeholder="Contoh: General Affair">
                @error('division')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="amount">Nominal</label>
                <input id="amount" name="amount" type="number" min="1" value="{{ old('amount', $item->amount) }}" placeholder="250000" required>
                @error('amount')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="type">Type</label>
                <select id="type" name="type" required>
                    @php
                        $types = ['Cash Advance', 'Direct Payment', 'Reimbursement', 'Petty Cash'];
                        $currentType = old('type', $item->type);

                        if ($currentType && ! in_array($currentType, $types, true)) {
                            $types[] = $currentType;
                        }
                    @endphp
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected($currentType === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="budget_id">Budget ID</label>
                <input id="budget_id" name="budget_id" value="{{ old('budget_id', $item->budget_id) }}" placeholder="Contoh: GA-2026-001">
                @error('budget_id')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="budget_name">Budget Name</label>
                <input id="budget_name" name="budget_name" value="{{ old('budget_name', $item->budget_name) }}" placeholder="Contoh: Office Supplies">
                @error('budget_name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="receiver_name">Receiver Name</label>
                <input id="receiver_name" name="receiver_name" value="{{ old('receiver_name', $item->receiver_name) }}" placeholder="Contoh: PT Vendor Utama">
                @error('receiver_name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="payment_due_date">Payment Due Date</label>
                <input id="payment_due_date" name="payment_due_date" type="date" value="{{ old('payment_due_date', optional($item->payment_due_date)->format('Y-m-d')) }}">
                @error('payment_due_date')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method">
                    @php
                        $paymentMethods = ['Bank Transfer', 'Transfer CC BNI', 'Cash', 'Virtual Account', 'Cheque', 'Other'];
                        $currentPaymentMethod = old('payment_method', $item->payment_method);

                        if ($currentPaymentMethod && ! in_array($currentPaymentMethod, $paymentMethods, true)) {
                            $paymentMethods[] = $currentPaymentMethod;
                        }
                    @endphp
                    <option value="">Pilih payment method</option>
                    @foreach ($paymentMethods as $paymentMethod)
                        <option value="{{ $paymentMethod }}" @selected($currentPaymentMethod === $paymentMethod)>{{ $paymentMethod }}</option>
                    @endforeach
                </select>
                @error('payment_method')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="bank_account_number">Bank Account Number</label>
                <input id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $item->bank_account_number) }}" placeholder="Contoh: 1234567890">
                @error('bank_account_number')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['Draft', 'Submitted', 'Paid'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $item->status ?? 'Draft') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('request-items.index') }}">Batal</a>
            <button class="button" type="submit">
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                Simpan
            </button>
        </div>
    </form>
@endsection
