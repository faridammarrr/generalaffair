<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;

class PettyCashController extends Controller
{
    public function index(Request $request): View
    {
        $query = PettyCash::query();

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        $items = $query->oldest('created_at')->get();
        $stats = [
            'total_spending' => $items->sum('amount'),
            'count' => $items->count(),
            'latest_amount' => $items->last()?->amount ?? 0,
        ];

        return view('petty_cashes.index', compact('items', 'stats', 'request'));
    }

    public function create(): View
    {
        return view('petty_cashes.form', [
            'item' => new PettyCash(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['invoice_path'] = $this->storeInvoice($request);

        PettyCash::create($data);

        return redirect()->route('petty-cashes.index')->with('success', 'Belanja petty cash berhasil dicatat.');
    }

    public function edit(PettyCash $pettyCash): View
    {
        return view('petty_cashes.form', [
            'item' => $pettyCash,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, PettyCash $pettyCash): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('invoice')) {
            if ($pettyCash->invoice_path) {
                Storage::disk('public')->delete($pettyCash->invoice_path);
            }

            $data['invoice_path'] = $this->storeInvoice($request);
        } elseif ($request->boolean('remove_invoice') && $pettyCash->invoice_path) {
            Storage::disk('public')->delete($pettyCash->invoice_path);
            $data['invoice_path'] = null;
        }

        $pettyCash->update($data);

        return redirect()->route('petty-cashes.index')->with('success', 'Catatan petty cash berhasil diperbarui.');
    }

    public function destroy(PettyCash $pettyCash): RedirectResponse
    {
        $pettyCash->delete();

        return redirect()->route('petty-cashes.index')->with('success', 'Catatan petty cash berhasil dihapus.');
    }

    public function invoice(PettyCash $pettyCash): BinaryFileResponse
    {
        if (! $pettyCash->invoice_path || ! Storage::disk('public')->exists($pettyCash->invoice_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($pettyCash->invoice_path);
        $mimeType = mime_content_type($path) ?: 'application/octet-stream';
        $filename = basename($pettyCash->invoice_path);

        $response = response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);

        if (str_contains($mimeType, 'pdf')) {
            $response->headers->set('Content-Type', 'application/pdf');
        }

        return $response;
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'date' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'integer', 'min:1'],
            'category' => ['nullable', 'string', 'max:255'],
            'person_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'invoice' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'remove_invoice' => ['nullable', 'boolean'],
        ]);
    }

    private function storeInvoice(Request $request): ?string
    {
        if (! $request->hasFile('invoice')) {
            return null;
        }

        return $request->file('invoice')->store('petty-cash-invoices', 'public');
    }
}
