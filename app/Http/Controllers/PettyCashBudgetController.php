<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use App\Models\PettyCashBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PettyCashBudgetController extends Controller
{
    public function index(Request $request): View
    {
        $query = PettyCashBudget::query();

        if ($request->filled('start_date')) {
            $query->where('year', '>=', Carbon::parse($request->input('start_date'))->year)
                ->where('month', '>=', Carbon::parse($request->input('start_date'))->monthName);
        }

        if ($request->filled('end_date')) {
            $query->where('year', '<=', Carbon::parse($request->input('end_date'))->year)
                ->where('month', '<=', Carbon::parse($request->input('end_date'))->monthName);
        }

        $budgets = $query->oldest('created_at')->get();
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentBudget = PettyCashBudget::where('month', now()->monthName)->where('year', $currentYear)->first();

        $monthlySpending = PettyCash::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $remaining = $currentBudget ? $currentBudget->amount - $monthlySpending : null;

        return view('petty_cashes.budgets', compact('budgets', 'currentBudget', 'monthlySpending', 'remaining', 'request'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'budget_date' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $date = now()->parse($data['budget_date']);
        $date = $date->day(1);

        $budget = PettyCashBudget::where('month', $date->monthName)
            ->where('year', $date->year)
            ->first();

        if ($budget) {
            $budget->update([
                'amount' => $data['amount'],
                'notes' => $data['notes'],
            ]);
        } else {
            PettyCashBudget::create([
                'month' => $date->monthName,
                'year' => $date->year,
                'amount' => $data['amount'],
                'notes' => $data['notes'],
            ]);
        }

        return redirect()->route('petty-cashes.budgets')->with('success', 'Budget petty cash berhasil disimpan.');
    }
}
