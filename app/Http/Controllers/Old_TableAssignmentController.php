<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Worker;
use App\Models\TableAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Old_TableAssignmentController extends Controller
{
    /**
     * Display all assignments
     */
    public function index(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $assignments = TableAssignment::with(['table', 'worker'])
            ->whereDate('assigned_date', $date)
            ->orderBy('table_id')
            ->paginate(40);

        // Get unassigned tables and workers
        $assignedTableIds = TableAssignment::whereDate('assigned_date', $date)
            ->where('status', 'active')
            ->pluck('table_id')
            ->toArray();

        $assignedWorkerIds = TableAssignment::whereDate('assigned_date', $date)
            ->where('status', 'active')
            ->pluck('worker_id')
            ->toArray();

        $unassignedTables = Table::whereNotIn('id', $assignedTableIds)
            ->where('is_active', true)
            ->count();

        $availableWorkers = Worker::whereNotIn('id', $assignedWorkerIds)
            ->where('is_active', true)
            ->count();

        $stats = [
            'total_tables' => Table::where('is_active', true)->count(),
            'assigned_tables' => count($assignedTableIds),
            'unassigned_tables' => $unassignedTables,
            'available_workers' => $availableWorkers,
        ];

        return view('assignments.index', compact('assignments', 'stats', 'date'));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        // Get assigned IDs for this date
        $assignedTableIds = TableAssignment::whereDate('assigned_date', $date)
            ->where('status', 'active')
            ->pluck('table_id')
            ->toArray();

        $assignedWorkerIds = TableAssignment::whereDate('assigned_date', $date)
            ->where('status', 'active')
            ->pluck('worker_id')
            ->toArray();

        // Get available tables and workers
        $tables = Table::whereNotIn('id', $assignedTableIds)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        $workers = Worker::whereNotIn('id', $assignedWorkerIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('assignments.create', compact('tables', 'workers', 'date'));
    }

    /**
     * Store new assignment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'worker_id' => 'required|exists:workers,id',
            'assigned_date' => 'required|date',
            'shift_start' => 'nullable',
            'shift_end' => 'nullable',
            'notes' => 'nullable',
        ]);

        // Check if table is already assigned for this date
        $existingAssignment = TableAssignment::where('table_id', $validated['table_id'])
            ->whereDate('assigned_date', $validated['assigned_date'])
            ->where('status', 'active')
            ->first();

        if ($existingAssignment) {
            return back()->withErrors(['table_id' => 'This table is already assigned for this date.'])->withInput();
        }

        // Check if worker is already assigned for this date
        $workerAssignment = TableAssignment::where('worker_id', $validated['worker_id'])
            ->whereDate('assigned_date', $validated['assigned_date'])
            ->where('status', 'active')
            ->first();

        if ($workerAssignment) {
            return back()->withErrors(['worker_id' => 'This worker is already assigned to another table for this date.'])->withInput();
        }

        TableAssignment::create($validated);

        return redirect()->route('assignments.index', ['date' => $validated['assigned_date']])
            ->with('success', 'Assignment created successfully!');
    }

    /**
     * Show single assignment
     */
    public function show(TableAssignment $assignment)
    {
        $assignment->load(['table', 'worker']);
        return view('assignments.show', compact('assignment'));
    }

    /**
     * Show edit form
     */
    public function edit(TableAssignment $assignment)
    {
        $tables = Table::where('is_active', true)->orderBy('table_number')->get();
        $workers = Worker::where('is_active', true)->orderBy('name')->get();
        return view('assignments.edit', compact('assignment', 'tables', 'workers'));
    }

    /**
     * Update assignment
     */
    public function update(Request $request, TableAssignment $assignment)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'worker_id' => 'required|exists:workers,id',
            'assigned_date' => 'required|date',
            'shift_start' => 'nullable',
            'shift_end' => 'nullable',
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable',
        ]);

        $assignment->update($validated);

        return redirect()->route('assignments.index', ['date' => $validated['assigned_date']])
            ->with('success', 'Assignment updated successfully!');
    }

    /**
     * Delete assignment
     */
    public function destroy(TableAssignment $assignment)
    {
        $date = $assignment->assigned_date->format('Y-m-d');
        $assignment->delete();

        return redirect()->route('assignments.index', ['date' => $date])
            ->with('success', 'Assignment deleted successfully!');
    }

    /**
     * Bulk assign workers to tables
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'assigned_date' => 'required|date',
            'assignments' => 'required|array',
            'assignments.*.table_id' => 'required|exists:tables,id',
            'assignments.*.worker_id' => 'required|exists:workers,id',
        ]);

        $created = 0;
        foreach ($validated['assignments'] as $assignment) {
            // Check if not already assigned
            $exists = TableAssignment::where('table_id', $assignment['table_id'])
                ->whereDate('assigned_date', $validated['assigned_date'])
                ->where('status', 'active')
                ->exists();

            if (!$exists) {
                TableAssignment::create([
                    'table_id' => $assignment['table_id'],
                    'worker_id' => $assignment['worker_id'],
                    'assigned_date' => $validated['assigned_date'],
                    'status' => 'active',
                ]);
                $created++;
            }
        }

        return redirect()->route('assignments.index', ['date' => $validated['assigned_date']])
            ->with('success', "{$created} assignments created successfully!");
    }

    /**
     * Copy assignments from one date to another
     */
    public function copyFromDate(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|different:from_date',
        ]);

        $sourceAssignments = TableAssignment::whereDate('assigned_date', $validated['from_date'])
            ->where('status', 'active')
            ->get();

        $created = 0;
        foreach ($sourceAssignments as $source) {
            // Check if not already assigned
            $exists = TableAssignment::where('table_id', $source->table_id)
                ->whereDate('assigned_date', $validated['to_date'])
                ->where('status', 'active')
                ->exists();

            if (!$exists) {
                TableAssignment::create([
                    'table_id' => $source->table_id,
                    'worker_id' => $source->worker_id,
                    'assigned_date' => $validated['to_date'],
                    'shift_start' => $source->shift_start,
                    'shift_end' => $source->shift_end,
                    'status' => 'active',
                ]);
                $created++;
            }
        }

        return redirect()->route('assignments.index', ['date' => $validated['to_date']])
            ->with('success', "{$created} assignments copied successfully!");
    }
}
