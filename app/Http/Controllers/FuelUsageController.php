<?php

namespace App\Http\Controllers;

use App\Models\FuelBudget;
use App\Models\fuel_usage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelUsageController extends Controller
{
    public function index(Request $request): View
    {
        $query = fuel_usage::query();

        if ($request->filled('month')) {
            $query->where('budget_month', $request->input('month'));
        }

        if ($request->filled('year')) {
            $query->where('budget_year', $request->input('year'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        $items = $query->latest('date')->latest('created_at')->get();
        $budgetMonth = $request->input('month') ?: now()->month;
        $budgetYear = $request->input('year') ?: now()->year;
        $budget = FuelBudget::where('month', $budgetMonth)->where('year', $budgetYear)->first();
        $budgetAmount = $budget?->amount ?? 0;
        $totalSpent = $items->sum('amount');
        $remaining = $budgetAmount - $totalSpent;

        $stats = [
            'budget_amount' => $budgetAmount,
            'total_spent' => $totalSpent,
            'remaining' => $remaining,
            'count' => $items->count(),
        ];

        return view('fuel_usages.index', compact('items', 'stats', 'request'));
    }

    public function create(): View
    {
        return view('fuel_usages.form', [
            'item' => new fuel_usage(),
            'mode' => 'create',
        ]);
    }

    public function createBudget(): View
    {
        $budgetMonth = now()->month;
        $budgetYear = now()->year;

        $budget = FuelBudget::where('month', $budgetMonth)->where('year', $budgetYear)->first();

        return view('fuel_usages.budget_form', [
            'budget' => $budget ?? new FuelBudget(),
            'month' => $budgetMonth,
            'year' => $budgetYear,
        ]);
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000'],
            'amount' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $budget = FuelBudget::where('month', $data['month'])->where('year', $data['year'])->first();

        if ($budget) {
            $budget->update($data);
        } else {
            FuelBudget::create($data);
        }

        return redirect()->route('fuel-usages.index', ['month' => $data['month'], 'year' => $data['year']])
            ->with('success', 'Budget bensin berhasil disimpan.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($this->hasColumn('driver_name')) {
            $data['driver_name'] = $data['driver_name'] ?? null;
        } else {
            unset($data['driver_name']);
        }

        if ($this->hasColumn('person')) {
            $data['person'] = $data['person'] ?? $data['driver_name'] ?? null;
        } else {
            unset($data['person']);
        }

        if ($this->hasColumn('date')) {
            $data['date'] = $data['date'] ?? now()->toDateString();
            $date = \Carbon\Carbon::parse($data['date']);
            $data['budget_month'] = $date->month;
            $data['budget_year'] = $date->year;
        } else {
            unset($data['date']);
        }

        if ($this->hasColumn('budget_month')) {
            $data['budget_month'] = $data['budget_month'] ?? $data['budget_month'] ?? now()->month;
        } else {
            unset($data['budget_month']);
        }

        if ($this->hasColumn('budget_year')) {
            $data['budget_year'] = $data['budget_year'] ?? $data['budget_year'] ?? now()->year;
        } else {
            unset($data['budget_year']);
        }

        if ($this->hasColumn('description')) {
            $data['description'] = $data['description'] ?? $data['notes'] ?? null;
            unset($data['notes']);
        } elseif ($this->hasColumn('notes')) {
            $data['notes'] = $data['notes'] ?? null;
        } else {
            unset($data['description'], $data['notes']);
        }

        fuel_usage::create($data);

        return redirect()->route('fuel-usages.index')->with('success', 'Pembelian bensin berhasil dicatat.');
    }

    public function edit(fuel_usage $fuel_usage): View
    {
        return view('fuel_usages.form', [
            'item' => $fuel_usage,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, fuel_usage $fuel_usage): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($this->hasColumn('driver_name')) {
            $data['driver_name'] = $data['driver_name'] ?? null;
        } else {
            unset($data['driver_name']);
        }

        if ($this->hasColumn('person')) {
            $data['person'] = $data['person'] ?? $data['driver_name'] ?? $fuel_usage->person ?? $fuel_usage->driver_name ?? null;
        } else {
            unset($data['person']);
        }

        if ($this->hasColumn('date')) {
            $data['date'] = $data['date'] ?? now()->toDateString();
            $date = \Carbon\Carbon::parse($data['date']);
            $data['budget_month'] = $date->month;
            $data['budget_year'] = $date->year;
        } else {
            unset($data['date']);
        }

        if ($this->hasColumn('budget_month')) {
            $data['budget_month'] = $data['budget_month'] ?? $fuel_usage->budget_month ?? now()->month;
        } else {
            unset($data['budget_month']);
        }

        if ($this->hasColumn('budget_year')) {
            $data['budget_year'] = $data['budget_year'] ?? $fuel_usage->budget_year ?? now()->year;
        } else {
            unset($data['budget_year']);
        }

        if ($this->hasColumn('description')) {
            $data['description'] = $data['description'] ?? $data['notes'] ?? $fuel_usage->description ?? $fuel_usage->notes ?? null;
            unset($data['notes']);
        } elseif ($this->hasColumn('notes')) {
            $data['notes'] = $data['notes'] ?? $fuel_usage->notes ?? null;
        } else {
            unset($data['description'], $data['notes']);
        }

        $fuel_usage->update($data);

        return redirect()->route('fuel-usages.index')->with('success', 'Pembelian bensin berhasil diperbarui.');
    }

    public function destroy(fuel_usage $fuel_usage): RedirectResponse
    {
        $fuel_usage->delete();

        return redirect()->route('fuel-usages.index')->with('success', 'Pembelian bensin berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $rules = [
            'driver_name' => ['nullable', 'string', 'max:255'],
            'person' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:1'],
            'budget_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'budget_year' => ['nullable', 'integer', 'min:2000'],
            'description' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->hasColumn('person')) {
            $rules['person'] = ['required', 'string', 'max:255'];
        }

        if ($this->hasColumn('description')) {
            $rules['description'] = ['required', 'string', 'max:500'];
        }

        return $request->validate($rules);
    }

    private function hasColumn(string $column): bool
    {
        return app('db')->connection()->getSchemaBuilder()->hasColumn('fuel_usages', $column);
    }

}
