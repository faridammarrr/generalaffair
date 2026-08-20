<?php

namespace App\Http\Controllers;

use App\Models\StampMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StampMovementController extends Controller
{
    public function index(): View
    {
        $movements = StampMovement::latest('moved_at')->latest()->get();
        $remaining = $movements->sum(fn (StampMovement $movement) => $movement->signed_quantity);

        $stats = [
            'remaining' => $remaining,
            'total_in' => $movements->where('direction', 'in')->sum('quantity'),
            'total_out' => $movements->where('direction', 'out')->sum('quantity'),
            'borrowed' => $movements->where('direction', 'borrow')->sum('quantity'),
            'transactions' => $movements->count(),
        ];

        return view('stamp_movements.index', compact('movements', 'stats'));
    }

    public function create(): View
    {
        return view('stamp_movements.form', [
            'movement' => new StampMovement([
                'direction' => 'out',
                'moved_at' => now(),
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        StampMovement::create($this->validatedData($request));

        return redirect()->route('stamp-movements.index')->with('success', 'Mutasi meterai berhasil dicatat.');
    }

    public function edit(StampMovement $stampMovement): View
    {
        return view('stamp_movements.form', [
            'movement' => $stampMovement,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, StampMovement $stampMovement): RedirectResponse
    {
        $stampMovement->update($this->validatedData($request));

        return redirect()->route('stamp-movements.index')->with('success', 'Mutasi meterai berhasil diperbarui.');
    }

    public function destroy(StampMovement $stampMovement): RedirectResponse
    {
        $stampMovement->delete();

        return redirect()->route('stamp-movements.index')->with('success', 'Mutasi meterai berhasil dihapus.');
    }

    public function returnBorrow(StampMovement $stampMovement): RedirectResponse
    {
        if ($stampMovement->direction !== 'borrow') {
            return redirect()->route('stamp-movements.index')->with('error', 'Hanya mutasi pinjam yang bisa dikembalikan.');
        }

        $stampMovement->update([
            'description' => 'Pengembalian meterai: ' . $stampMovement->description,
            'direction' => 'in',
            'moved_at' => now()->toDateString(),
        ]);

        return redirect()->route('stamp-movements.index')->with('success', 'Meterai pinjaman berhasil dikembalikan dan stok bertambah kembali.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'person_name' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'direction' => ['required', 'in:in,out,borrow'],
            'moved_at' => ['required', 'date'],
        ]);
    }
}
