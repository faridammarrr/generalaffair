<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\FuelBudget;
use App\Models\PettyCash;
use App\Models\PettyCashBudget;
use App\Models\RequestItem;
use App\Models\StampMovement;
use App\Models\WorkActivity;
use App\Models\fuel_usage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\IndonesiaHolidayService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, IndonesiaHolidayService $holidayService): View
    {
        $requestItems = RequestItem::oldest('created_at')->get();
        $bills = Bill::orderByRaw("status = 'Paid'")
            ->orderBy('created_at')
            ->get();
        $pettyCashes = PettyCash::oldest('created_at')->get();
        $fuelUsages = fuel_usage::latest('date')->get();
        $stampMovements = StampMovement::all();
        $activities = WorkActivity::orderBy('date')->orderByDesc('created_at')->get();

        $requestUnpaidAmount = $requestItems->sum('amount') - $requestItems->where('status', 'Paid')->sum('amount');
        $unpaidBills = $bills->whereIn('status', ['Unpaid', 'Submit', 'Draft']);
        $activeTasks = $activities->whereIn('status', ['Proses', 'Pending']);
        $completedTasks = $activities->where('status', 'Selesai');

        $recentRequestItems = $requestItems->take(5);
        $recentBills = $bills->take(5);
        $recentPettyCashes = $pettyCashes->take(5);
        $recentFuelUsages = $fuelUsages->take(5);
        $recentStampMovements = $stampMovements->take(5);

        $currentMonth = now()->month;
        $currentYear = now()->year;
        $holidays = $holidayService->forYear($currentYear);

        $pettyCashBudget = PettyCashBudget::where('month', now()->monthName)
            ->where('year', $currentYear)
            ->first();
        $pettyCashMonthlySpent = PettyCash::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $fuelBudget = FuelBudget::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();
        $fuelMonthlySpent = fuel_usage::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $monthExpression = match (DB::connection()->getDriverName()) {
            'mysql' => 'MONTH(date)',
            'pgsql' => 'EXTRACT(MONTH FROM date)',
            default => "CAST(strftime('%m', date) AS INTEGER)",
        };
        $monthlyExpenseRows = PettyCash::query()
            ->whereYear('date', $currentYear)
            ->selectRaw("{$monthExpression} as month, SUM(amount) as total")
            ->groupByRaw($monthExpression)
            ->pluck('total', 'month');
        $monthlyExpenses = collect(range(1, 12))->mapWithKeys(fn (int $month) => [$month => (int) ($monthlyExpenseRows[$month] ?? 0)]);

        $expenseCategories = collect(['ATK', 'Konsumsi', 'Transport', 'Maintenance', 'Utilities', 'Lainnya'])
            ->mapWithKeys(fn (string $category) => [$category => 0]);
        PettyCash::query()
            ->whereYear('date', $currentYear)
            ->selectRaw("COALESCE(category, 'Lainnya') as category, SUM(amount) as total")
            ->groupBy('category')
            ->get()
            ->each(function ($item) use ($expenseCategories): void {
                $category = in_array($item->category, ['ATK', 'Konsumsi', 'Transport', 'Maintenance', 'Utilities'], true)
                    ? $item->category
                    : 'Lainnya';
                $expenseCategories[$category] += (int) $item->total;
            });

        $requestStatusRows = RequestItem::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $requestStatuses = collect(['Draft', 'Submitted', 'Waiting Approval', 'Approved', 'Rejected', 'Completed'])
            ->mapWithKeys(fn (string $status) => [$status => (int) ($requestStatusRows[$status] ?? 0)]);

        $openMaintenance = $activities->filter(fn (WorkActivity $activity) => str_contains(strtolower((string) $activity->category), 'maintenance') && $activity->status !== 'Selesai')->count();
        $pendingRequests = $requestItems->whereIn('status', ['Submitted', 'Waiting Approval'])->count();

        $calendarStart = now()->startOfMonth();
        $calendarDays = [];
        $firstDayOfMonth = $calendarStart->dayOfWeekIso;
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
                'tasks' => $activities->filter(fn (WorkActivity $task) => $task->date && $task->date->format('Y-m-d') === $date->format('Y-m-d'))->values(),
            ];
        }

        $selectedDate = $request->input('task_date', today()->format('Y-m-d'));
        $selectedTasks = $activities
            ->filter(fn (WorkActivity $task) => $task->date && $task->date->format('Y-m-d') === $selectedDate)
            ->values();

        $editingActivity = $request->filled('edit_task') ? WorkActivity::find($request->input('edit_task')) : null;

        $stats = [
            'total_requests' => $requestItems->count(),
            'request_unpaid_amount' => $requestUnpaidAmount,
            'unpaid_bills' => $unpaidBills->count(),
            'overdue_bills' => $unpaidBills->filter(fn (Bill $bill) => $bill->due_date && $bill->status !== 'Draft' && $bill->due_date->lt(today()))->count(),
            'petty_cash_total' => $pettyCashes->sum('amount'),
            'petty_cash_budget' => $pettyCashBudget?->amount ?? 0,
            'petty_cash_monthly_spent' => $pettyCashMonthlySpent,
            'petty_cash_remaining' => max(0, ($pettyCashBudget?->amount ?? 0) - $pettyCashMonthlySpent),
            'fuel_spent' => $fuelUsages->sum('amount'),
            'fuel_monthly_spent' => $fuelMonthlySpent,
            'fuel_budget' => $fuelBudget?->amount ?? 0,
            'fuel_remaining' => max(0, ($fuelBudget?->amount ?? 0) - $fuelMonthlySpent),
            'operational_expense_monthly' => $pettyCashMonthlySpent,
            'pending_requests' => $pendingRequests,
            'open_maintenance' => $openMaintenance,
            'total_asset' => 0,
            'low_stock_items' => 0,
            'requests_this_month' => $requestItems->filter(fn (RequestItem $item) => $item->request_date?->month === $currentMonth && $item->request_date?->year === $currentYear)->count(),
            'completed_requests_this_month' => $requestItems->filter(fn (RequestItem $item) => $item->status === 'Completed' && $item->updated_at?->month === $currentMonth && $item->updated_at?->year === $currentYear)->count(),
            'pending_activities' => $activities->whereIn('status', ['Proses', 'Pending'])->count(),
            'maintenance_requests' => $activities->filter(fn (WorkActivity $activity) => str_contains(strtolower((string) $activity->category), 'maintenance'))->count(),
            'vehicle_requests' => 0,
            'stamp_remaining' => $stampMovements->sum(fn (StampMovement $movement) => $movement->signed_quantity),
            'active_tasks' => $activeTasks->count(),
            'completed_tasks' => $completedTasks->count(),
            'task_total' => $activities->count(),
        ];

        return view('dashboard.index', compact(
            'stats',
            'calendarDays',
            'calendarStart',
            'selectedDate',
            'selectedTasks',
            'activities',
            'editingActivity',
            'recentRequestItems',
            'recentBills',
            'recentPettyCashes',
            'recentFuelUsages',
            'recentStampMovements',
            'monthlyExpenses',
            'expenseCategories',
            'requestStatuses',
            'currentYear'
        ));
    }
}
