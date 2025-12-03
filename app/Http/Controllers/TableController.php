<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Worker;
use App\Models\TableAssignment;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    /**
     * Display all tables
     */
    public function index(Request $request)
    {
        $query = Table::with(['currentAssignment.worker']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('table_number', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        $tables = $query->orderBy('table_number')->paginate(20);

        return view('tables.index', compact('tables'));
    }

    /**
     * Show create form (Admin only)
     */
    public function create()
    {
        return view('tables.create');
    }

    /**
     * Store new table (Admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|unique:tables,table_number|max:10',
            'table_name' => 'required|max:100',
            'esp32_ip' => 'nullable|ip',
            'esp32_device_id' => 'nullable|max:50',
            'notes' => 'nullable',
        ]);

        // Generate QR code
        $qrCode = "TABLE:{$validated['table_number']}:" . time();
        $validated['qr_code'] = $qrCode;

        $table = Table::create($validated);

        return redirect()->route('tables.show', $table)
            ->with('success', 'Table created successfully!');
    }

    /**
     * Show single table
     */
    public function show(Table $table)
    {
        $table->load(['currentAssignment.worker', 'productionLogs' => function ($query) {
            $query->today()->latest()->take(10);
        }, 'lightIndicators' => function ($query) {
            $query->latest()->take(10);
        }]);

        // Get today's production
        $todayProduction = $table->productionLogs()->today()->sum('garments_count');

        // Get hourly breakdown
        $hourlyData = $table->productionLogs()
            ->today()
            ->selectRaw('production_hour, SUM(garments_count) as total')
            ->groupBy('production_hour')
            ->get();

        return view('tables.show', compact('table', 'todayProduction', 'hourlyData'));
    }

    /**
     * Show edit form (Admin only)
     */
    public function edit(Table $table)
    {
        return view('tables.edit', compact('table'));
    }

    /**
     * Update table (Admin only)
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'table_number' => 'required|max:10|unique:tables,table_number,' . $table->id,
            'table_name' => 'required|max:100',
            'esp32_ip' => 'nullable|ip',
            'esp32_device_id' => 'nullable|max:50',
            'is_active' => 'boolean',
            'notes' => 'nullable',
        ]);

        $table->update($validated);

        return redirect()->route('tables.show', $table)
            ->with('success', 'Table updated successfully!');
    }

    /**
     * Delete table (Admin only)
     */
    public function destroy(Table $table)
    {
        $table->delete();
        return redirect()->route('tables.index')
            ->with('success', 'Table deleted successfully!');
    }

    /**
     * Download single QR code as PNG
     */
    public function downloadQR(Table $table)
    {
        // Use SVG format - no imagick required
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($table->qr_code);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $table->table_number . '_qr.svg"');
    }

    public function regenerateQR(Table $table)
    {
        $table->qr_code = 'TABLE:' . $table->table_number . ':' . $table->id . ':' . time();
        $table->save();

        return back()->with('success', 'QR Code regenerated successfully!');
    }
}
