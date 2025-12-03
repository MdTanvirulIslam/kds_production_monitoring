<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkerController extends Controller
{
    /**
     * Display all workers
     */
    public function index(Request $request)
    {
        $query = Worker::with('currentAssignment.table');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by skill level
        if ($request->has('skill_level') && $request->skill_level) {
            $query->where('skill_level', $request->skill_level);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('worker_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $workers = $query->orderBy('name')->paginate(20);

        return view('workers.index', compact('workers'));
    }

    /**
     * Show create form (Admin only)
     */
    public function create()
    {
        return view('workers.create');
    }

    /**
     * Store new worker (Admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|unique:workers,worker_id|max:20',
            'name' => 'required|max:100',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'skill_level' => 'required|in:beginner,intermediate,expert',
            'photo' => 'nullable|image|max:2048',
            'notes' => 'nullable',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('workers', 'public');
        }

        $worker = Worker::create($validated);

        return redirect()->route('workers.show', $worker)
            ->with('success', 'Worker created successfully!');
    }

    /**
     * Show single worker
     */
    public function show(Worker $worker)
    {
        $worker->load(['currentAssignment.table', 'productionLogs' => function ($query) {
            $query->today()->latest()->take(10);
        }]);

        // Get today's production
        $todayProduction = $worker->getTodayProduction();

        // Get weekly production
        $weeklyProduction = $worker->productionLogs()
            ->whereBetween('production_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('garments_count');

        // Get production history (last 7 days)
        $productionHistory = $worker->productionLogs()
            ->selectRaw('production_date, SUM(garments_count) as total')
            ->whereBetween('production_date', [now()->subDays(7), now()])
            ->groupBy('production_date')
            ->orderBy('production_date')
            ->get();

        return view('workers.show', compact('worker', 'todayProduction', 'weeklyProduction', 'productionHistory'));
    }

    /**
     * Show edit form (Admin only)
     */
    public function edit(Worker $worker)
    {
        return view('workers.edit', compact('worker'));
    }

    /**
     * Update worker (Admin only)
     */
    public function update(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'worker_id' => 'required|max:20|unique:workers,worker_id,' . $worker->id,
            'name' => 'required|max:100',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'required|date',
            'skill_level' => 'required|in:beginner,intermediate,expert',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'notes' => 'nullable',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($worker->photo) {
                Storage::disk('public')->delete($worker->photo);
            }
            $validated['photo'] = $request->file('photo')->store('workers', 'public');
        }

        $worker->update($validated);

        return redirect()->route('workers.show', $worker)
            ->with('success', 'Worker updated successfully!');
    }

    /**
     * Delete worker (Admin only)
     */
    public function destroy(Worker $worker)
    {
        // Delete photo if exists
        if ($worker->photo) {
            Storage::disk('public')->delete($worker->photo);
        }

        $worker->delete();

        return redirect()->route('workers.index')
            ->with('success', 'Worker deleted successfully!');
    }
}
