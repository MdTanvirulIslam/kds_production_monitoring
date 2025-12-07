<?php

namespace App\Http\Controllers;

use App\Models\ProductionTarget;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionTargetController extends Controller
{
    /**
     * Get shifts from database or return defaults
     */
    private function getShifts()
    {
        if (class_exists('App\Models\Shift')) {
            try {
                $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
                if ($shifts->count() > 0) {
                    return $shifts;
                }
            } catch (\Exception $e) {
                // Table might not exist
            }
        }

        return collect([
            (object)['id' => 1, 'name' => 'Morning Shift', 'start_time' => '06:00', 'end_time' => '14:00'],
            (object)['id' => 2, 'name' => 'Day Shift', 'start_time' => '14:00', 'end_time' => '22:00'],
            (object)['id' => 3, 'name' => 'Night Shift', 'start_time' => '22:00', 'end_time' => '06:00'],
        ]);
    }

    /**
     * Display a listing of targets
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $targets = ProductionTarget::whereBetween('target_date', [$startDate, $endDate])
            ->with('shift')
            ->orderBy('target_date', 'desc')
            ->orderBy('shift_id')
            ->get()
            ->groupBy(function ($target) {
                return $target->target_date->format('Y-m-d');
            });

        $shifts = $this->getShifts();

        return view('production-targets.index', compact('targets', 'month', 'shifts'));
    }

    /**
     * Show form for creating a new target
     */
    public function create(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $shifts = $this->getShifts();

        // Get existing targets for this date
        $existingTargets = ProductionTarget::whereDate('target_date', $date)
            ->pluck('shift_id')
            ->toArray();

        return view('production-targets.create', compact('date', 'shifts', 'existingTargets'));
    }

    /**
     * Store a newly created target
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_date' => 'required|date',
            'shift_id' => 'nullable|integer',
            'hourly_target' => 'required|integer|min:1',
            'daily_target' => 'required|integer|min:1',
            'product_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if target already exists for this date and shift
        $exists = ProductionTarget::where('target_date', $validated['target_date'])
            ->where(function ($query) use ($validated) {
                if (isset($validated['shift_id']) && $validated['shift_id']) {
                    $query->where('shift_id', $validated['shift_id']);
                } else {
                    $query->whereNull('shift_id');
                }
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Target already exists for this date and shift.');
        }

        ProductionTarget::create($validated);

        return redirect()->route('production-targets.index')
            ->with('success', 'Production target created successfully.');
    }

    /**
     * Show the form for editing the target
     */
    public function edit(ProductionTarget $productionTarget)
    {
        $shifts = $this->getShifts();

        return view('production-targets.edit', compact('productionTarget', 'shifts'));
    }

    /**
     * Update the target
     */
    public function update(Request $request, ProductionTarget $productionTarget)
    {
        $validated = $request->validate([
            'target_date' => 'required|date',
            'shift_id' => 'nullable|integer',
            'hourly_target' => 'required|integer|min:1',
            'daily_target' => 'required|integer|min:1',
            'product_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if another target exists for this date and shift
        $exists = ProductionTarget::where('target_date', $validated['target_date'])
            ->where('id', '!=', $productionTarget->id)
            ->where(function ($query) use ($validated) {
                if (isset($validated['shift_id']) && $validated['shift_id']) {
                    $query->where('shift_id', $validated['shift_id']);
                } else {
                    $query->whereNull('shift_id');
                }
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Another target already exists for this date and shift.');
        }

        $productionTarget->update($validated);

        return redirect()->route('production-targets.index')
            ->with('success', 'Production target updated successfully.');
    }

    /**
     * Remove the target
     */
    public function destroy(ProductionTarget $productionTarget)
    {
        $productionTarget->delete();

        return redirect()->route('production-targets.index')
            ->with('success', 'Production target deleted successfully.');
    }

    /**
     * Bulk create targets for a week
     */
    public function bulkCreate(Request $request)
    {
        $shifts = $this->getShifts();

        return view('production-targets.bulk-create', compact('shifts'));
    }

    /**
     * Store bulk targets
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shifts' => 'required|array|min:1',
            'shifts.*' => 'integer',
            'hourly_target' => 'required|integer|min:1',
            'daily_target' => 'required|integer|min:1',
            'product_type' => 'nullable|string|max:100',
            'skip_weekends' => 'nullable|boolean',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $skipWeekends = $request->boolean('skip_weekends');
        $created = 0;
        $skipped = 0;

        while ($startDate <= $endDate) {
            // Skip weekends if option is set
            if ($skipWeekends && $startDate->isWeekend()) {
                $startDate->addDay();
                continue;
            }

            foreach ($validated['shifts'] as $shiftId) {
                // Check if already exists
                $exists = ProductionTarget::where('target_date', $startDate->format('Y-m-d'))
                    ->where('shift_id', $shiftId)
                    ->exists();

                if (!$exists) {
                    ProductionTarget::create([
                        'target_date' => $startDate->format('Y-m-d'),
                        'shift_id' => $shiftId,
                        'hourly_target' => $validated['hourly_target'],
                        'daily_target' => $validated['daily_target'],
                        'product_type' => $validated['product_type'] ?? null,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }

            $startDate->addDay();
        }

        return redirect()->route('production-targets.index')
            ->with('success', "Created {$created} targets. Skipped {$skipped} (already exist).");
    }

    /**
     * Copy targets from one date to another
     */
    public function copy(Request $request)
    {
        $validated = $request->validate([
            'source_date' => 'required|date',
            'target_date' => 'required|date',
        ]);

        $sourceTargets = ProductionTarget::whereDate('target_date', $validated['source_date'])->get();

        if ($sourceTargets->isEmpty()) {
            return back()->with('error', 'No targets found for the source date.');
        }

        $created = 0;
        $skipped = 0;

        foreach ($sourceTargets as $source) {
            $exists = ProductionTarget::where('target_date', $validated['target_date'])
                ->where('shift_id', $source->shift_id)
                ->exists();

            if (!$exists) {
                ProductionTarget::create([
                    'target_date' => $validated['target_date'],
                    'shift_id' => $source->shift_id,
                    'hourly_target' => $source->hourly_target,
                    'daily_target' => $source->daily_target,
                    'product_type' => $source->product_type,
                    'notes' => $source->notes,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return back()->with('success', "Copied {$created} targets. Skipped {$skipped} (already exist).");
    }
}
