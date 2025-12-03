<?php


// ============================================
// COMPLETE DATABASE SEEDER
// File: database/seeders/DatabaseSeeder.php
// ============================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Table;
use App\Models\Worker;
use App\Models\TableAssignment;
use App\Models\ProductionLog;
use App\Models\LightIndicator;
use App\Models\ProductionTarget;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "\n🚀 Starting Database Seeding...\n\n";

        // Step 1: Create Users (Admin, Supervisors, Monitor)
        $this->createUsers();

        // Step 2: Create 40 Tables with QR codes
        $this->createTables();

        // Step 3: Create 50 Workers
        $this->createWorkers();

        // Step 4: Assign Workers to Tables (THIS IS WHAT YOU NEED!)
        $this->assignWorkersToTables();

        // Step 5: Create Production Targets
        $this->createProductionTargets();

        // Step 6: Create Sample Production Logs (Optional)
        $this->createSampleProductionLogs();

        // Step 7: Create Sample Light Indicators (Optional)
        $this->createSampleLightIndicators();

        echo "\n✅ Database seeding completed successfully!\n\n";
        $this->displayLoginCredentials();
    }

    // ============================================
    // STEP 1: CREATE USERS
    // ============================================
    private function createUsers()
    {
        echo "📝 Creating Users...\n";

        // Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin321'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        echo "   ✓ Admin created\n";

        // Create 3 Supervisors
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Supervisor $i",
                'email' => "supervisor$i@gmail.com",
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'phone' => '01712345' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }
        echo "   ✓ 3 Supervisors created\n";

        // Create Monitor
        User::create([
            'name' => 'Monitor User',
            'email' => 'monitor@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'monitor',
            'is_active' => true,
        ]);
        echo "   ✓ Monitor created\n\n";
    }

    // ============================================
    // STEP 2: CREATE TABLES
    // ============================================
    private function createTables()
    {
        echo "🪑 Creating 40 Tables with QR codes...\n";

        for ($i = 1; $i <= 40; $i++) {
            $tableNumber = 'T' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                $table = Table::create([
                    'table_number' => $tableNumber,
                    'table_name' => "Table $i",
                    'qr_code' => 'qr_codes/table_' . $tableNumber . '.png', // Temporary path
                    'esp32_device_id' => "ESP32_$tableNumber",
                    'esp32_ip' => '192.168.0.' . (100 + $i),
                    'current_light_status' => 'off',
                    'is_active' => true,
                ]);

                // Generate QR code if method exists
                if (method_exists($table, 'generateQRCode')) {
                    $table->generateQRCode();
                }

                echo "   ✓ Table $tableNumber created\n";

            } catch (\Exception $e) {
                echo "   ✗ Error creating table $tableNumber: " . $e->getMessage() . "\n";
            }
        }

        echo "   ✓ Tables creation completed\n\n";
    }

    // ============================================
    // STEP 3: CREATE WORKERS
    // ============================================
    private function createWorkers()
    {
        echo "👷 Creating 50 Workers...\n";

        $skillLevels = ['Beginner', 'Intermediate', 'Expert'];

        for ($i = 1; $i <= 50; $i++) {
            Worker::create([
                'worker_id' => 'W' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => "Worker $i",
                'phone' => '01812345' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => "worker$i@factory.com",
                'joining_date' => now()->subDays(rand(30, 365)),
                'skill_level' => $skillLevels[array_rand($skillLevels)],
                'is_active' => true,
            ]);
        }

        echo "   ✓ 50 Workers created\n\n";
    }

    // ============================================
    // STEP 4: ASSIGN WORKERS TO TABLES (IMPORTANT!)
    // ============================================
    private function assignWorkersToTables()
    {
        echo "🔗 Assigning Workers to Tables...\n";

        $tables = Table::all();
        $workers = Worker::where('is_active', true)->get();

        // TODAY'S ASSIGNMENTS (Active)
        // Assign first 40 workers to 40 tables for today
        echo "   → Assigning workers for TODAY (Active assignments)...\n";

        foreach ($tables as $index => $table) {
            if (isset($workers[$index])) {
                TableAssignment::create([
                    'table_id' => $table->id,
                    'worker_id' => $workers[$index]->id,
                    'assigned_date' => today(),
                    'shift_start' => '08:00:00',
                    'shift_end' => '17:00:00',
                    'status' => 'active',
                    'notes' => 'Morning shift - ' . today()->format('Y-m-d'),
                ]);
            }
        }
        echo "   ✓ 40 active assignments created for today\n";

        // YESTERDAY'S ASSIGNMENTS (Completed)
        echo "   → Creating completed assignments for YESTERDAY...\n";

        foreach ($tables->take(35) as $index => $table) {
            if (isset($workers[$index])) {
                TableAssignment::create([
                    'table_id' => $table->id,
                    'worker_id' => $workers[$index]->id,
                    'assigned_date' => today()->subDay(),
                    'shift_start' => '08:00:00',
                    'shift_end' => '17:00:00',
                    'status' => 'completed',
                    'notes' => 'Completed shift - ' . today()->subDay()->format('Y-m-d'),
                ]);
            }
        }
        echo "   ✓ 35 completed assignments created for yesterday\n";

        // LAST WEEK'S ASSIGNMENTS (Sample historical data)
        echo "   → Creating historical assignments for LAST WEEK...\n";

        for ($day = 2; $day <= 7; $day++) {
            $date = today()->subDays($day);

            // Randomly assign 30-35 workers each day
            $assignmentCount = rand(30, 35);

            foreach ($tables->take($assignmentCount) as $index => $table) {
                if (isset($workers[$index])) {
                    TableAssignment::create([
                        'table_id' => $table->id,
                        'worker_id' => $workers[$index]->id,
                        'assigned_date' => $date,
                        'shift_start' => '08:00:00',
                        'shift_end' => '17:00:00',
                        'status' => 'completed',
                        'notes' => 'Historical data - ' . $date->format('Y-m-d'),
                    ]);
                }
            }
        }
        echo "   ✓ Historical assignments created for last week\n\n";
    }

    // ============================================
    // STEP 5: CREATE PRODUCTION TARGETS
    // ============================================
    private function createProductionTargets()
    {
        echo "🎯 Creating Production Targets...\n";

        // Create targets for today and next 30 days
        for ($i = 0; $i <= 30; $i++) {
            $date = today()->addDays($i);

            ProductionTarget::create([
                'target_date' => $date,
                'hourly_target' => 10, // 10 garments per hour
                'daily_target' => 80,  // 80 garments per day (8 hours)
                'notes' => 'Standard daily target',
            ]);
        }

        echo "   ✓ Production targets created for 31 days\n\n";
    }

    // ============================================
    // STEP 6: CREATE SAMPLE PRODUCTION LOGS
    // ============================================
    private function createSampleProductionLogs()
    {
        echo "📊 Creating Sample Production Logs...\n";

        $supervisors = User::where('role', 'supervisor')->get();

        // Get today's active assignments
        $todayAssignments = TableAssignment::where('assigned_date', today())
            ->where('status', 'active')
            ->with(['table', 'worker'])
            ->get();

        // Create production logs for today (9 AM to current hour)
        $currentHour = now()->hour;
        $startHour = 9; // 9 AM

        if ($currentHour >= $startHour) {
            foreach ($todayAssignments->take(20) as $assignment) {
                for ($hour = $startHour; $hour <= min($currentHour, 17); $hour++) {
                    ProductionLog::create([
                        'table_id' => $assignment->table_id,
                        'worker_id' => $assignment->worker_id,
                        'supervisor_id' => $supervisors->random()->id,
                        'production_date' => today(),
                        'production_hour' => sprintf('%02d:00:00', $hour),
                        'garments_count' => rand(8, 12), // Random between 8-12 garments
                        'product_type' => ['Shirt', 'Pant', 'Jacket'][rand(0, 2)],
                        'notes' => 'Auto-generated sample data',
                    ]);
                }
            }
            echo "   ✓ Production logs created for today\n";
        }

        // Create production logs for yesterday (full day)
        $yesterdayAssignments = TableAssignment::where('assigned_date', today()->subDay())
            ->where('status', 'completed')
            ->with(['table', 'worker'])
            ->get();

        foreach ($yesterdayAssignments as $assignment) {
            for ($hour = 9; $hour <= 17; $hour++) {
                ProductionLog::create([
                    'table_id' => $assignment->table_id,
                    'worker_id' => $assignment->worker_id,
                    'supervisor_id' => $supervisors->random()->id,
                    'production_date' => today()->subDay(),
                    'production_hour' => sprintf('%02d:00:00', $hour),
                    'garments_count' => rand(8, 12),
                    'product_type' => ['Shirt', 'Pant', 'Jacket'][rand(0, 2)],
                ]);
            }
        }
        echo "   ✓ Production logs created for yesterday\n\n";
    }

    // ============================================
    // STEP 7: CREATE SAMPLE LIGHT INDICATORS
    // ============================================
    private function createSampleLightIndicators()
    {
        echo "💡 Creating Sample Light Indicators...\n";

        $supervisors = User::where('role', 'supervisor')->get();
        $todayAssignments = TableAssignment::where('assigned_date', today())
            ->where('status', 'active')
            ->with(['table', 'worker'])
            ->get();

        // Create some active alerts (red lights)
        foreach ($todayAssignments->take(5) as $assignment) {
            LightIndicator::create([
                'table_id' => $assignment->table_id,
                'worker_id' => $assignment->worker_id,
                'supervisor_id' => $supervisors->random()->id,
                'light_color' => 'red',
                'reason' => 'Quality Issue - Need Attention',
                'activated_at' => now()->subMinutes(rand(5, 60)),
                'deactivated_at' => null, // Still active
            ]);

            // Update table status
            $assignment->table->update(['current_light_status' => 'red']);
        }
        echo "   ✓ 5 active red light alerts created\n";

        // Create some completed indicators (green lights)
        foreach ($todayAssignments->skip(5)->take(10) as $assignment) {
            $activatedTime = now()->subHours(rand(1, 4));
            $deactivatedTime = $activatedTime->copy()->addMinutes(rand(15, 45));

            LightIndicator::create([
                'table_id' => $assignment->table_id,
                'worker_id' => $assignment->worker_id,
                'supervisor_id' => $supervisors->random()->id,
                'light_color' => 'green',
                'reason' => 'Good Work - On Track',
                'activated_at' => $activatedTime,
                'deactivated_at' => $deactivatedTime,
                'duration_seconds' => $activatedTime->diffInSeconds($deactivatedTime),
            ]);
        }
        echo "   ✓ 10 completed green light indicators created\n\n";
    }

    // ============================================
    // DISPLAY LOGIN CREDENTIALS
    // ============================================
    private function displayLoginCredentials()
    {
        echo "═══════════════════════════════════════════════════\n";
        echo "           LOGIN CREDENTIALS                       \n";
        echo "═══════════════════════════════════════════════════\n\n";

        echo "👤 ADMIN:\n";
        echo "   Email: admin@gmail.com\n";
        echo "   Password: admin321\n\n";

        echo "👤 SUPERVISORS:\n";
        echo "   Email: supervisor1@factory.com\n";
        echo "   Email: supervisor2@factory.com\n";
        echo "   Email: supervisor3@factory.com\n";
        echo "   Password: password (for all)\n\n";

        echo "👤 MONITOR:\n";
        echo "   Email: monitor@gmail.com\n";
        echo "   Password: password\n\n";

        echo "═══════════════════════════════════════════════════\n";
        echo "           SYSTEM OVERVIEW                         \n";
        echo "═══════════════════════════════════════════════════\n\n";

        $totalTables = Table::count();
        $totalWorkers = Worker::count();
        $activeAssignments = TableAssignment::where('status', 'active')
            ->whereDate('assigned_date', today())
            ->count();
        $todayProduction = ProductionLog::whereDate('production_date', today())->sum('garments_count');
        $activeAlerts = LightIndicator::where('light_color', 'red')
            ->whereNull('deactivated_at')
            ->count();

        echo "📊 Tables: $totalTables\n";
        echo "👷 Workers: $totalWorkers\n";
        echo "🔗 Active Assignments Today: $activeAssignments\n";
        echo "📈 Today's Production: $todayProduction garments\n";
        echo "🚨 Active Alerts: $activeAlerts\n\n";

        echo "═══════════════════════════════════════════════════\n";
        echo "Access dashboard at: http://192.168.0.101:8000\n";
        echo "═══════════════════════════════════════════════════\n\n";
    }
}

