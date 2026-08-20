<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use App\Models\RequestItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        [$filters, $expenseItems, $requestItems, $pettyCashItems] = $this->filteredData($request);

        return view('reports.index', compact('filters', 'expenseItems', 'requestItems', 'pettyCashItems'));
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['expense', 'request', 'petty-cash'], true), 404);

        [$filters, $expenseItems, $requestItems, $pettyCashItems] = $this->filteredData($request);
        $rows = match ($type) {
            'expense' => collect([['Tanggal', 'Nomor Transaksi', 'Kategori', 'Deskripsi', 'Nominal', 'PIC', 'Status']])
                ->merge($expenseItems->map(fn (PettyCash $item) => [$item->date?->format('Y-m-d'), 'PC-' . str_pad($item->id, 5, '0', STR_PAD_LEFT), $item->category ?: 'Lainnya', $item->description, $item->amount, $item->person_name ?: '-', 'Recorded'])),
            'request' => collect([['Nomor PR', 'Requester', 'Tanggal', 'Kategori', 'Deskripsi', 'Nominal', 'Status']])
                ->merge($requestItems->map(fn (RequestItem $item) => [$item->letter_number ?: 'PR-' . str_pad($item->id, 5, '0', STR_PAD_LEFT), $item->requestor_name ?: '-', $item->request_date?->format('Y-m-d'), $item->type, $item->name, $item->amount, $item->status])),
            default => collect([['Tanggal', 'Kategori', 'Deskripsi', 'Pengeluaran', 'Saldo']])
                ->merge($pettyCashItems->map(function (PettyCash $item) use ($pettyCashItems) {
                    static $spent = 0;
                    $spent += (int) $item->amount;
                    return [$item->date?->format('Y-m-d'), $item->category ?: 'Lainnya', $item->description, $item->amount, $pettyCashItems->sum('amount') - $spent];
                })),
        };

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, 'general-affair-' . $type . '-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredData(Request $request): array
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->integer('month') ?: null,
            'year' => $request->integer('year') ?: null,
            'category' => $request->input('category'),
            'status' => $request->input('status'),
        ];

        if ($filters['month'] && $filters['year']) {
            $start = now()->setDate($filters['year'], $filters['month'], 1)->startOfMonth()->toDateString();
            $end = now()->setDate($filters['year'], $filters['month'], 1)->endOfMonth()->toDateString();
            $filters['start_date'] = $start;
            $filters['end_date'] = $end;
        } elseif ($filters['year']) {
            $filters['start_date'] = now()->setYear($filters['year'])->startOfYear()->toDateString();
            $filters['end_date'] = now()->setYear($filters['year'])->endOfYear()->toDateString();
        }

        $applyDates = function (Builder $query, string $column) use ($filters): Builder {
            return $query
                ->when($filters['start_date'], fn (Builder $query, string $date) => $query->whereDate($column, '>=', $date))
                ->when($filters['end_date'], fn (Builder $query, string $date) => $query->whereDate($column, '<=', $date));
        };

        $expenseItems = $applyDates(PettyCash::query()->oldest('date'), 'date')
            ->when($filters['category'], fn (Builder $query, string $category) => $query->where('category', $category))
            ->get();
        $pettyCashItems = $expenseItems;
        $requestItems = $applyDates(RequestItem::query()->oldest('request_date'), 'request_date')
            ->when($filters['category'], fn (Builder $query, string $category) => $query->where('type', $category))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->get();

        return [$filters, $expenseItems, $requestItems, $pettyCashItems];
    }
}
