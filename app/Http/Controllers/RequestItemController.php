<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\RequestItem;
use App\Models\StampMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\IndonesiaHolidayService;

class RequestItemController extends Controller
{
    public function index(IndonesiaHolidayService $holidayService): View
    {
        $items = RequestItem::oldest('created_at')->get();
        $totalAmount = $items->sum('amount');
        $totalPaid = $items->where('status', 'Paid')->sum('amount');

        $stats = [
            'total_requests' => $items->count(),
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'submitted' => $items->where('status', 'Submitted')->count(),
            'paid' => $items->where('status', 'Paid')->count(),
            'total_outstanding' => $totalAmount - $totalPaid
        ];

        $stampMovements = StampMovement::all();
        $unpaidBills = Bill::where('status', 'Unpaid')->get();

        $overview = [
            'remaining_stamps' => $stampMovements->sum(fn (StampMovement $movement) => $movement->signed_quantity),
            'unpaid_bills' => $unpaidBills->count(),
            'unpaid_bill_amount' => $unpaidBills->sum('amount'),
        ];

        $requestChartData = [
            [
                'label' => 'Draft',
                'value' => $items->where('status', 'Draft')->count(),
                'class' => 'bg-slate-500',
            ],
            [
                'label' => 'Submitted',
                'value' => $items->where('status', 'Submitted')->count(),
                'class' => 'bg-amber-500',
            ],
            [
                'label' => 'Paid',
                'value' => $items->where('status', 'Paid')->count(),
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
                'items' => $items->filter(fn (RequestItem $item) => $item->payment_due_date && $item->payment_due_date->format('Y-m-d') === $date->format('Y-m-d')),
            ];
        }

        $calendarMonthLabel = $calendarStart->translatedFormat('F Y');
        $selectedDate = request('date');
        $selectedItems = $selectedDate
            ? $items->filter(fn (RequestItem $item) => $item->payment_due_date && $item->payment_due_date->format('Y-m-d') === $selectedDate)->values()
            : collect();

        return view('request_items.index', compact('items', 'stats', 'overview', 'requestChartData', 'calendarDays', 'calendarMonthLabel', 'selectedDate', 'selectedItems'));
    }

    public function create(): View
    {
        return view('request_items.form', [
            'item' => new RequestItem(['status' => 'Draft']),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        RequestItem::create($this->withStatusDates($this->validatedData($request)));

        return redirect()->route('request-items.index')->with('success', 'Request berhasil ditambahkan.');
    }

    public function edit(RequestItem $requestItem): View
    {
        return view('request_items.form', [
            'item' => $requestItem,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, RequestItem $requestItem): RedirectResponse
    {
        $requestItem->update($this->withStatusDates($this->validatedData($request), $requestItem));

        return redirect()->route('request-items.index')->with('success', 'Request berhasil diperbarui.');
    }

    public function destroy(RequestItem $requestItem): RedirectResponse
    {
        $requestItem->delete();

        return redirect()->route('request-items.index')->with('success', 'Request berhasil dihapus.');
    }

    public function submit(RequestItem $requestItem): RedirectResponse
    {
        $item = $requestItem;
        $item->status = 'Submitted';
        $item->submitted_at = now();
        $item->save();

        return redirect()->route('request-items.index')->with('success', 'Request berhasil disubmit.');
    }

    public function paid(RequestItem $requestItem): RedirectResponse
    {
        $item = $requestItem;
        $item->status = 'Paid';
        $item->submitted_at ??= now();
        $item->paid_at = now();
        $item->save();

        return redirect()->route('request-items.index')->with('success', 'Request sudah ditandai paid.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:500'],
            'request_date' => ['nullable', 'date'],
            'letter_number' => ['nullable', 'string', 'max:255'],
            'requestor_name' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'budget_id' => ['nullable', 'string', 'max:255'],
            'budget_name' => ['nullable', 'string', 'max:255'],
            'receiver_name' => ['nullable', 'string', 'max:255'],
            'payment_due_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:Draft,Submitted,Paid'],
        ]);
    }

    private function withStatusDates(array $data, ?RequestItem $item = null): array
    {
        if ($data['status'] === 'Draft') {
            $data['submitted_at'] = null;
            $data['paid_at'] = null;
        }

        if ($data['status'] === 'Submitted') {
            $data['submitted_at'] = $item?->submitted_at ?? now();
            $data['paid_at'] = null;
        }

        if ($data['status'] === 'Paid') {
            $data['submitted_at'] = $item?->submitted_at ?? now();
            $data['paid_at'] = $item?->paid_at ?? now();
        }

        return $data;
    }
}
