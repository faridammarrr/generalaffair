<?php

namespace App\Http\Controllers;

use App\Models\WorkActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkActivityController extends Controller
{
    public function index(): View
    {
        $activities = WorkActivity::latest('date')->latest('created_at')->get()->groupBy(function ($activity) {
            return $activity->date->format('Y-m-d');
        });
        $editingActivity = null;

        return view('activities.index', compact('activities', 'editingActivity'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        WorkActivity::create($data);

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit(WorkActivity $activity): View
    {
        $activities = WorkActivity::latest('date')->latest('created_at')->get()->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        });
        $editingActivity = $activity;

        return view('activities.index', compact('activities', 'editingActivity'));
    }

    public function update(Request $request, WorkActivity $activity): RedirectResponse
    {
        $activity->update($this->validatedData($request));

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(WorkActivity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function complete(WorkActivity $activity): RedirectResponse
    {
        $activity->update(['status' => 'Selesai']);

        return redirect()->route('activities.index')->with('success', 'Kegiatan telah ditandai selesai.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
        ]);
    }
}
