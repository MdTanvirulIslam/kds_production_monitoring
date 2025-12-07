<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Worker;
use App\Models\TableAssignment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TableAssignmentController extends Controller
{
    /**
     * Display a listing of assignments
     */
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today('Asia/Dhaka')->toDateString());

        $assignments = TableAssignment::with(['table', 'worker'])
            ->whereDate('assigned_date', $date)
            ->orderBy('table_id')
            ->paginate(40);

        // Stats
        $totalTables = Table::where('is_active', true)->count();
        $assignedTableIds = TableAssignment::whereDate('assigned_date', $date)->pluck('table_id')->toArray();

        $stats = [
            'total_tables' => $totalTables,
            'assigned_tables' => count($assignedTableIds),
            'unassigned_tables' => $totalTables - count($assignedTableIds),
        ];

        // Get shifts for filter
        $shifts = $this->getShifts();

        return view('assignments.index', compact('assignments', 'date', 'stats', 'shifts'));
    }

    /**
     * Show bulk assignment page
     */
    public function bulk(Request $request)
    {
        $date = $request->input('date', Carbon::today('Asia/Dhaka')->toDateString());

        // Get assigned IDs for this date
        $assignedTableIds = TableAssignment::whereDate('assigned_date', $date)->pluck('table_id')->toArray();
        $assignedWorkerIds = TableAssignment::whereDate('assigned_date', $date)->pluck('worker_id')->toArray();

        // All tables with current assignment status
        $tables = Table::where('is_active', true)
            ->with(['currentAssignment' => function($q) use ($date) {
                $q->whereDate('assigned_date', $date);
            }, 'currentAssignment.worker'])
            ->orderBy('table_number')
            ->get();

        // All workers with current assignment status
        $workers = Worker::where('is_active', true)
            ->with(['currentAssignment' => function($q) use ($date) {
                $q->whereDate('assigned_date', $date);
            }])
            ->orderBy('name')
            ->get();

        // Current assignments for display
        $assignments = TableAssignment::with(['table', 'worker'])
            ->whereDate('assigned_date', $date)
            ->orderBy('table_id')
            ->get();

        $unassignedTables = $tables->filter(fn($t) => !in_array($t->id, $assignedTableIds))->count();
        $availableWorkers = $workers->filter(fn($w) => !in_array($w->id, $assignedWorkerIds))->count();
        $assignedCount = count($assignedTableIds);

        // Get shifts
        $shifts = $this->getShifts();

        return view('assignments.bulk', compact(
            'tables',
            'workers',
            'assignments',
            'date',
            'unassignedTables',
            'availableWorkers',
            'assignedCount',
            'shifts'
        ));
    }

    /**
     * Get shifts from database or return defaults
     */
    private function getShifts()
    {
        // Try to get from database if Shift model exists
        if (class_exists('App\Models\Shift')) {
            $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
            if ($shifts->count() > 0) {
                return $shifts;
            }
        }

        // Return default shifts as collection
        return collect([
            (object)[
                'id' => 1,
                'name' => 'Morning Shift',
                'start_time' => '06:00',
                'end_time' => '14:00',
            ],
            (object)[
                'id' => 2,
                'name' => 'Day Shift',
                'start_time' => '14:00',
                'end_time' => '22:00',
            ],
            (object)[
                'id' => 3,
                'name' => 'Night Shift',
                'start_time' => '22:00',
                'end_time' => '06:00',
            ],
        ]);
    }

    /**
     * Store bulk assignments
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'tables' => 'required|array',
            'workers' => 'required|array',
            'date' => 'required|date',
        ]);

        $tables = $request->tables;
        $workers = $request->workers;
        $date = $request->date;
        $shiftId = $request->shift_id;
        $shiftStart = $request->shift_start ?? '08:00';
        $shiftEnd = $request->shift_end ?? '17:00';

        $assignedCount = 0;
        $minCount = min(count($tables), count($workers));

        for ($i = 0; $i < $minCount; $i++) {
            // Check if assignment already exists for this table + shift
            $existing = TableAssignment::where('table_id', $tables[$i])
                ->whereDate('assigned_date', $date)
                ->where('shift_start', $shiftStart)
                ->where('shift_end', $shiftEnd)
                ->first();

            if (!$existing) {
                TableAssignment::create([
                    'table_id' => $tables[$i],
                    'worker_id' => $workers[$i],
                    'assigned_date' => $date,
                    'assigned_by' => auth()->id(),
                    'shift_id' => $shiftId,
                    'shift_start' => $shiftStart,
                    'shift_end' => $shiftEnd,
                    'status' => 'active',
                ]);
                $assignedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$assignedCount} assignments created successfully!",
            'assigned_count' => $assignedCount,
        ]);
    }

    /**
     * Copy assignments from one date to another
     */
    public function copy(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|different:from_date',
        ]);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $shiftFilter = $request->shift_filter;

        // Get source assignments
        $query = TableAssignment::whereDate('assigned_date', $fromDate);

        // Filter by shift if specified
        if ($shiftFilter && $shiftFilter !== 'all') {
            $query->where('shift_id', $shiftFilter);
        }

        $sourceAssignments = $query->get();

        if ($sourceAssignments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No assignments found for {$fromDate}",
            ], 400);
        }

        $copiedCount = 0;

        foreach ($sourceAssignments as $source) {
            // Check if assignment already exists for target date + shift
            $existing = TableAssignment::where('table_id', $source->table_id)
                ->whereDate('assigned_date', $toDate)
                ->where('shift_start', $source->shift_start)
                ->where('shift_end', $source->shift_end)
                ->first();

            if (!$existing) {
                TableAssignment::create([
                    'table_id' => $source->table_id,
                    'worker_id' => $source->worker_id,
                    'assigned_date' => $toDate,
                    'assigned_by' => auth()->id(),
                    'shift_id' => $source->shift_id,
                    'shift_start' => $source->shift_start,
                    'shift_end' => $source->shift_end,
                    'status' => 'active',
                    'notes' => "Copied from {$fromDate}",
                ]);
                $copiedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$copiedCount} assignments copied from {$fromDate} to {$toDate}!",
            'copied_count' => $copiedCount,
        ]);
    }

    /**
     * Clear all assignments for a date
     */
    public function clearAll(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;
        $shiftId = $request->shift_id;

        $query = TableAssignment::whereDate('assigned_date', $date);

        // Filter by shift if specified
        if ($shiftId && $shiftId !== 'all') {
            $query->where('shift_id', $shiftId);
        }

        $deletedCount = $query->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} assignments cleared for {$date}!",
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $date = $request->input('date', Carbon::today('Asia/Dhaka')->toDateString());

        // Get unassigned tables
        $assignedTableIds = TableAssignment::whereDate('assigned_date', $date)->pluck('table_id');
        $tables = Table::whereNotIn('id', $assignedTableIds)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        // Get unassigned workers
        $assignedWorkerIds = TableAssignment::whereDate('assigned_date', $date)->pluck('worker_id');
        $workers = Worker::whereNotIn('id', $assignedWorkerIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $shifts = $this->getShifts();

        return view('assignments.create', compact('tables', 'workers', 'date', 'shifts'));
    }

    /**
     * Store single assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'worker_id' => 'required|exists:workers,id',
            'assigned_date' => 'required|date',
        ]);

        $shiftStart = $request->shift_start ?? '08:00';
        $shiftEnd = $request->shift_end ?? '17:00';

        // Check if already assigned for this shift
        $existing = TableAssignment::where('table_id', $request->table_id)
            ->whereDate('assigned_date', $request->assigned_date)
            ->where('shift_start', $shiftStart)
            ->where('shift_end', $shiftEnd)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This table is already assigned for this shift.',
            ], 400);
        }

        $assignment = TableAssignment::create([
            'table_id' => $request->table_id,
            'worker_id' => $request->worker_id,
            'assigned_date' => $request->assigned_date,
            'assigned_by' => auth()->id(),
            'shift_id' => $request->shift_id,
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully!',
                'assignment' => $assignment->load(['table', 'worker']),
            ]);
        }

        return redirect()->route('assignments.index', ['date' => $request->assigned_date])
            ->with('success', 'Assignment created successfully!');
    }

    /**
     * Show single assignment
     */
    public function show(TableAssignment $assignment)
    {
        return view('assignments.show', compact('assignment'));
    }

    /**
     * Show edit form
     */
    public function edit(TableAssignment $assignment)
    {
        $date = $assignment->assigned_date->format('Y-m-d');

        // Get available tables (current + unassigned)
        $assignedTableIds = TableAssignment::whereDate('assigned_date', $date)
            ->where('id', '!=', $assignment->id)
            ->pluck('table_id');
        $tables = Table::whereNotIn('id', $assignedTableIds)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        // Get available workers (current + unassigned)
        $assignedWorkerIds = TableAssignment::whereDate('assigned_date', $date)
            ->where('id', '!=', $assignment->id)
            ->pluck('worker_id');
        $workers = Worker::whereNotIn('id', $assignedWorkerIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $shifts = $this->getShifts();

        return view('assignments.edit', compact('assignment', 'tables', 'workers', 'date', 'shifts'));
    }

    /**
     * Update assignment
     */
    public function update(Request $request, TableAssignment $assignment)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'worker_id' => 'required|exists:workers,id',
        ]);

        $assignment->update([
            'table_id' => $request->table_id,
            'worker_id' => $request->worker_id,
            'shift_id' => $request->shift_id ?? $assignment->shift_id,
            'shift_start' => $request->shift_start ?? $assignment->shift_start,
            'shift_end' => $request->shift_end ?? $assignment->shift_end,
            'status' => $request->status ?? $assignment->status,
            'notes' => $request->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully!',
                'assignment' => $assignment->load(['table', 'worker']),
            ]);
        }

        return redirect()->route('assignments.index', ['date' => $assignment->assigned_date->format('Y-m-d')])
            ->with('success', 'Assignment updated successfully!');
    }

    /**
     * Delete assignment
     */
    public function destroy(TableAssignment $assignment)
    {
        $date = $assignment->assigned_date->format('Y-m-d');
        $assignment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment removed successfully!',
            ]);
        }

        return redirect()->route('assignments.index', ['date' => $date])
            ->with('success', 'Assignment removed successfully!');
    }

    /**
     * Clear all assignments for a specific date
     */
    public function clearDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;

        $deleted = Assignment::whereDate('assigned_date', $date)->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleared {$deleted} assignments for {$date}"
        ]);
    }
}
