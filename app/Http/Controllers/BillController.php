<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\IndonesiaHolidayService;

class BillController extends Controller
{
    public function index(IndonesiaHolidayService $holidayService): View
    {
        $bills = Bill::orderByRaw("status = 'Paid'")
            ->orderBy('created_at')
            ->get();

        $unpaid = $bills->whereIn('status', ['Unpaid', 'Submit', 'Draft']);

        $stats = [
            'unpaid_count' => $unpaid->count(),
            'unpaid_amount' => $unpaid->sum('amount'),
            'paid_count' => $bills->where('status', 'Paid')->count(),
            'overdue_count' => $unpaid->filter(fn (Bill $bill) => $bill->status !== 'Draft' && $bill->due_date && $bill->due_date->lt(today()))->count(),
            'remaining_return' => $bills->sum(fn (Bill $bill) => $bill->type === 'Cash Advance Settlement'
                ? max(0, $bill->amount - ($bill->settlement_amount ?? 0))
                : 0),
        ];

        $upcomingBills = $unpaid
            ->filter(fn (Bill $bill) => $bill->due_date && $bill->due_date->between(today(), today()->copy()->addDays(7)))
            ->sortBy('due_date');

        $overdueBills = $unpaid
            ->filter(fn (Bill $bill) => $bill->due_date && $bill->due_date->lt(today()))
            ->sortByDesc('due_date');

        $chartData = [
            [
                'label' => 'Belum Dibayar',
                'value' => $unpaid->count(),
                'class' => 'bg-amber-500',
            ],
            [
                'label' => 'Sudah Dibayar',
                'value' => $bills->where('status', 'Paid')->count(),
                'class' => 'bg-emerald-500',
            ],
        ];

        $calendarStart = now()->startOfMonth();
        $holidays = $holidayService->forYear($calendarStart->year);
        $calendarDays = [];
        $firstDayOfMonth = $calendarStart->copy()->dayOfWeekIso;
        $daysInMonth = $calendarStart->daysInMonth;

        for ($i = 1; $i < $firstDayOfMonth; $i++) {
            $calendarDays[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $calendarStart->copy()->day($day);
            $calendarDays[] = [
                'date' => $date,
                'holiday' => $holidays->get($date->format('Y-m-d')),
                'is_weekend' => $date->isWeekend(),
                'bills' => $bills->filter(fn (Bill $bill) => $bill->due_date && $bill->due_date->format('Y-m-d') === $date->format('Y-m-d')),
            ];
        }

        $calendarMonthLabel = $calendarStart->translatedFormat('F Y');
        $selectedDate = request('date');
        $selectedBills = $selectedDate
            ? $bills->filter(fn (Bill $bill) => $bill->due_date && $bill->due_date->format('Y-m-d') === $selectedDate)->values()
            : collect();

        return view('bills.index', compact('bills', 'stats', 'upcomingBills', 'overdueBills', 'chartData', 'calendarDays', 'calendarMonthLabel', 'selectedDate', 'selectedBills'));
    }

    public function create(): View
    {
        return view('bills.form', [
            'bill' => new Bill(['status' => 'Unpaid']),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Bill::create($this->withPaidDate($this->validatedData($request)));

        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil ditambahkan.');
    }

    public function edit(Bill $bill): View
    {
        return view('bills.form', [
            'bill' => $bill,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Bill $bill): RedirectResponse
    {
        $bill->update($this->withPaidDate($this->validatedData($request), $bill));

        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil dihapus.');
    }

    public function paid(Bill $bill): RedirectResponse
    {
        $bill->update([
            'status' => 'Paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('bills.index')->with('success', 'Tagihan sudah ditandai paid.');
    }

    public function submit(Bill $bill): RedirectResponse
    {
        $bill->update([
            'status' => 'Submit',
        ]);

        return redirect()->route('bills.index')->with('success', 'Tagihan sudah ditandai submit.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:500'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:Unpaid,Paid,Draft,Submit'],
            'settlement_amount' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function withPaidDate(array $data, ?Bill $bill = null): array
    {
        $data['paid_at'] = $data['status'] === 'Paid'
            ? ($bill?->paid_at ?? now())
            : null;

        return $data;
    }
}
