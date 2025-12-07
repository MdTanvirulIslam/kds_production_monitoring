@extends('layouts.layout')

@section('styles')
    <style>
        /* Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stat-card.tables { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.workers { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.production { background: linear-gradient(135deg, #ee9ca7 0%, #ffdde1 100%); color: #333; }
        .stat-card.alerts { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
        .stat-card.alerts.no-alerts { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); }

        .stat-card .stat-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.3;
            font-size: 40px;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Welcome Section */
        .welcome-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 15px;
            padding: 20px 25px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .welcome-card h2 {
            color: #fff;
            margin-bottom: 5px;
            font-size: 1.5rem;
        }
        .welcome-card .time-display {
            font-size: 1.8rem;
            font-weight: 300;
            color: #4fc3f7;
        }

        /* Progress Section */
        .progress-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .progress-card .progress {
            height: 25px;
            border-radius: 12px;
            background: #e9ecef;
        }
        .progress-card .progress-bar {
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .progress-info .target-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
        }
        .progress-info .target-label {
            color: #888;
            font-size: 0.8rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .quick-action-btn {
            padding: 8px 15px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-decoration: none;
        }
        .quick-action-btn.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .quick-action-btn.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; }
        .quick-action-btn.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; }

        /* Compact Card */
        .compact-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            height: 100%;
        }
        .compact-card .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .compact-card .card-header-custom h6 {
            margin: 0;
            font-weight: 700;
            color: #333;
            font-size: 0.9rem;
        }
        .compact-card .view-all {
            color: #667eea;
            font-size: 0.75rem;
            text-decoration: none;
        }

        /* Alert Card Compact */
        .alert-compact {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
            border-left: 4px solid #e74c3c;
        }
        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            background: #fff;
            border-radius: 6px;
            margin-bottom: 5px;
            font-size: 0.8rem;
        }
        .alert-item:last-child { margin-bottom: 0; }
        .alert-item .table-num {
            font-weight: 700;
            color: #e74c3c;
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #e74c3c;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        /* Worker Rank Compact */
        .worker-item {
            display: flex;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .worker-item:last-child { border-bottom: none; }
        .worker-item .rank {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            margin-right: 8px;
            border-radius: 50%;
            background: #f0f0f0;
        }
        .worker-item.gold .rank { background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); color: #fff; }
        .worker-item.silver .rank { background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%); color: #fff; }
        .worker-item.bronze .rank { background: linear-gradient(135deg, #b08d57 0%, #744d15 100%); color: #fff; }
        .worker-item .worker-name {
            flex-grow: 1;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .worker-item .production-badge {
            font-size: 0.75rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* Hourly Production */
        .hourly-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }
        .hourly-item {
            text-align: center;
            padding: 6px 4px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .hourly-item.has-data {
            background: linear-gradient(135deg, #667eea22 0%, #764ba222 100%);
            border: 1px solid #667eea44;
        }
        .hourly-item .hour {
            display: block;
            font-size: 0.65rem;
            color: #888;
        }
        .hourly-item .count {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #333;
        }
        .hourly-item .count.zero {
            color: #ccc;
        }
        .hourly-total {
            text-align: right;
            font-size: 0.8rem;
            color: #666;
            padding-top: 8px;
            border-top: 1px dashed #eee;
        }

        /* Shift Tabs for Hourly Production */
        .shift-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 10px;
        }
        .shift-tab-btn {
            flex: 1;
            padding: 6px 8px;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s;
            color: #666;
        }
        .shift-tab-btn:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .shift-tab-btn.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .shift-tab-btn .shift-icon {
            font-size: 12px;
            display: block;
            margin-bottom: 2px;
        }
        .shift-tab-btn .shift-count {
            font-size: 0.65rem;
            opacity: 0.8;
        }

        /* Logs Card */
        .logs-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .logs-card .table {
            font-size: 0.85rem;
            margin-bottom: 0;
        }
        .logs-card .table thead th {
            border-top: none;
            border-bottom: 2px solid #eee;
            font-weight: 600;
            color: #555;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .logs-card .table tbody td {
            vertical-align: middle;
            padding: 8px;
        }
        .log-time {
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
        }

        /* No Data */
        .no-data {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        {{-- Welcome Card --}}
        <div class="col-xl-4 col-lg-4 col-md-12 layout-spacing">
            <div class="welcome-card h-100">
                <h2>Welcome Back! 👋</h2>
                <p class="mb-2 text-white-50">{{ auth()->user()->name }}</p>
                <div class="time-display" id="currentTime">--:--:--</div>
                <small class="text-white-50" id="currentDate"></small>

                <div class="quick-actions">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('assignments.bulk') }}" class="quick-action-btn primary">
                            📋 Assignments
                        </a>
                        <a href="{{ route('reports.index') }}" class="quick-action-btn success">
                            📊 Reports
                        </a>
                    @elseif(auth()->user()->role === 'supervisor')
                        <a href="{{ route('supervisor.scan') }}" class="quick-action-btn primary">
                            📷 Scan QR
                        </a>
                        <a href="{{ route('supervisor.quick-select') }}" class="quick-action-btn success">
                            🎯 Quick Select
                        </a>
                    @endif
                    <a href="{{ route('monitor') }}" class="quick-action-btn warning">
                        📺 Monitor
                    </a>
                </div>
            </div>
        </div>

        {{-- Target Progress --}}
        <div class="col-xl-8 col-lg-8 col-md-12 layout-spacing">
            <div class="progress-card h-100">
                <div class="progress-info">
                    <div>
                        <div class="target-value">{{ number_format($stats['today_production']) }} / {{ number_format($stats['daily_target']) }}</div>
                        <div class="target-label">Daily Production Target</div>
                    </div>
                    <div class="text-end">
                        <div class="target-value {{ $stats['target_progress'] >= 100 ? 'text-success' : '' }}">{{ $stats['target_progress'] }}%</div>
                        <div class="target-label">Progress</div>
                    </div>
                </div>
                <div class="progress">
                    <div class="progress-bar {{ $stats['target_progress'] >= 100 ? 'bg-success' : '' }}"
                         role="progressbar"
                         style="width: {{ min($stats['target_progress'], 100) }}%; background: {{ $stats['target_progress'] < 100 ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '' }};"
                         aria-valuenow="{{ $stats['target_progress'] }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $stats['target_progress'] }}%
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <small class="text-muted">
                        🏭 {{ $stats['total_tables'] }} Tables Active
                    </small>
                    <small class="text-muted">
                        👷 {{ $stats['total_workers'] }} Workers
                    </small>
                    <small class="text-{{ $stats['active_alerts'] > 0 ? 'danger' : 'success' }}">
                        {{ $stats['active_alerts'] > 0 ? '🚨 ' . $stats['active_alerts'] . ' Alerts' : '✅ No Alerts' }}
                    </small>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 layout-spacing">
            <div class="stat-card tables">
                <div class="stat-icon">🏭</div>
                <div class="stat-value">{{ $stats['total_tables'] }}</div>
                <div class="stat-label">Active Tables</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 layout-spacing">
            <div class="stat-card workers">
                <div class="stat-icon">👷</div>
                <div class="stat-value">{{ $stats['total_workers'] }}</div>
                <div class="stat-label">Active Workers</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 layout-spacing">
            <div class="stat-card production">
                <div class="stat-icon">👕</div>
                <div class="stat-value">{{ number_format($stats['today_production']) }}</div>
                <div class="stat-label">Garments</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 layout-spacing">
            <div class="stat-card alerts {{ $stats['active_alerts'] == 0 ? 'no-alerts' : '' }}">
                <div class="stat-icon">🚨</div>
                <div class="stat-value">{{ $stats['active_alerts'] }}</div>
                <div class="stat-label">Alerts</div>
            </div>
        </div>

        {{-- Three Column Row: Alerts | Top Workers | Hourly Production --}}
        <div class="col-xl-4 col-lg-4 col-md-4 layout-spacing">
            <div class="compact-card {{ $alertTables->count() > 0 ? 'alert-compact' : '' }}">
                <div class="card-header-custom">
                    <h6>
                        @if($alertTables->count() > 0)
                            <span class="pulse-dot"></span>
                        @endif
                        🚨 Alerts ({{ $alertTables->count() }})
                    </h6>
                    @if($alertTables->count() > 0)
                        <a href="{{ route('monitor') }}" class="view-all">View All →</a>
                    @endif
                </div>

                @if($alertTables->count() > 0)
                    @foreach($alertTables->take(5) as $table)
                        <div class="alert-item">
                            <div>
                                <span class="table-num">{{ $table->table_number }}</span>
                                <small class="text-muted ms-1">{{ Str::limit($table->currentAssignment?->worker?->name ?? '-', 10) }}</small>
                            </div>
                            <a href="{{ route('tables.show', $table) }}" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.7rem;">View</a>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">
                        <p class="mb-0">✅ All clear!</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-4 layout-spacing">
            <div class="compact-card">
                <div class="card-header-custom">
                    <h6>🏆 Top Workers</h6>
                    <a href="{{ route('workers.index') }}" class="view-all">View All →</a>
                </div>

                @forelse($topWorkers->take(5) as $index => $worker)
                    <div class="worker-item {{ $index == 0 ? 'gold' : ($index == 1 ? 'silver' : ($index == 2 ? 'bronze' : '')) }}">
                        <div class="rank">
                            @if($index == 0) 🥇
                            @elseif($index == 1) 🥈
                            @elseif($index == 2) 🥉
                            @else {{ $index + 1 }}
                            @endif
                        </div>
                        <div class="worker-name">{{ Str::limit($worker->name, 12) }}</div>
                        <div class="production-badge">{{ $worker->today_production }}</div>
                    </div>
                @empty
                    <div class="no-data">
                        <p class="mb-0">No production yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Hourly Production with Shift Tabs --}}
        <div class="col-xl-4 col-lg-4 col-md-4 layout-spacing">
            <div class="compact-card">
                <div class="card-header-custom">
                    <h6>📊 Hourly Production</h6>
                </div>

                {{-- Shift Tabs --}}
                @if(isset($shifts) && count($shifts) > 0)
                    <div class="shift-tabs">
                        @foreach($shifts as $index => $shift)
                            <div class="shift-tab-btn {{ $index == 0 ? 'active' : '' }}" data-shift="{{ $shift->id }}">
                        <span class="shift-icon">
                            @if(strtotime($shift->start_time) < strtotime('12:00'))
                                🌅
                            @elseif(strtotime($shift->start_time) < strtotime('18:00'))
                                ☀️
                            @else
                                🌙
                            @endif
                        </span>
                                {{ Str::limit($shift->name, 8) }}
                                <span class="shift-count">({{ $shiftProduction[$shift->id] ?? 0 }})</span>
                            </div>
                        @endforeach
                    </div>

                    @php
                        // Define hours for each shift
                        $shiftHours = [
                            1 => ['06', '07', '08', '09', '10', '11', '12', '13'], // Morning
                            2 => ['14', '15', '16', '17', '18', '19', '20', '21'], // Day
                            3 => ['22', '23', '00', '01', '02', '03', '04', '05'], // Night
                        ];

                        $hourlyMap = $hourlyProduction ?? [];
                    @endphp

                    {{-- Hourly Grids for Each Shift --}}
                    @foreach($shifts as $index => $shift)
                        <div class="hourly-grid shift-grid" data-shift="{{ $shift->id }}" style="{{ $index != 0 ? 'display: none;' : '' }}">
                            @php
                                $hours = $shiftHours[$shift->id] ?? ['08', '09', '10', '11', '12', '13', '14', '15'];
                            @endphp
                            @foreach($hours as $hour)
                                @php
                                    $count = $hourlyMap[$hour] ?? 0;
                                @endphp
                                <div class="hourly-item {{ $count > 0 ? 'has-data' : '' }}">
                                    <span class="hour">{{ $hour }}h</span>
                                    <span class="count {{ $count == 0 ? 'zero' : '' }}">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="hourly-total shift-total" data-shift="{{ $shift->id }}" style="{{ $index != 0 ? 'display: none;' : '' }}">
                            Total: <strong>{{ number_format($shiftProduction[$shift->id] ?? 0) }}</strong> pcs
                        </div>
                    @endforeach

                @else
                    {{-- Fallback if no shifts --}}
                    @php
                        $hourlyMap = $hourlyProduction ?? [];
                        $defaultHours = ['08', '09', '10', '11', '12', '13', '14', '15', '16', '17'];
                        $totalHourly = is_array($hourlyMap) ? array_sum($hourlyMap) : 0;
                    @endphp
                    <div class="hourly-grid">
                        @foreach($defaultHours as $hour)
                            @php
                                $count = $hourlyMap[$hour] ?? 0;
                            @endphp
                            <div class="hourly-item {{ $count > 0 ? 'has-data' : '' }}">
                                <span class="hour">{{ $hour }}h</span>
                                <span class="count {{ $count == 0 ? 'zero' : '' }}">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="hourly-total">
                        Total: <strong>{{ number_format($totalHourly) }}</strong> pcs
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Production Logs --}}
        <div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
            <div class="logs-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0" style="font-weight: 700;">📋 Recent Production Logs</h6>
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'monitor')
                        <a href="{{ route('reports.index') }}" style="color: #667eea; font-size: 0.8rem; text-decoration: none;">View Reports →</a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Time</th>
                            <th>Table</th>
                            <th>Worker</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Supervisor</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentLogs->take(8) as $log)
                            <tr>
                                <td><span class="log-time">{{ $log->created_at->format('H:i') }}</span></td>
                                <td>
                                    <a href="{{ route('tables.show', $log->table) }}" class="fw-bold text-primary">
                                        {{ $log->table->table_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $log->worker->photo ? asset('storage/'.$log->worker->photo) : asset('assets/src/assets/img/profile-30.png') }}"
                                             class="rounded-circle me-2" width="25" height="25" style="object-fit: cover;">
                                        {{ Str::limit($log->worker->name, 15) }}
                                    </div>
                                </td>
                                <td>{{ $log->product_type ?? '-' }}</td>
                                <td>
                                    <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                                        {{ $log->garments_count }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($log->supervisor->name, 12) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="no-data">
                                        <p class="mb-2">No production logs yet today</p>
                                        @if(auth()->user()->role === 'supervisor')
                                            <a href="{{ route('supervisor.scan') }}" class="btn btn-primary btn-sm">Start Scanning</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Real-time Clock
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds}`;

            const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
        }

        updateClock();
        setInterval(updateClock, 1000);

        // Shift Tab Switching
        document.querySelectorAll('.shift-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const shiftId = this.dataset.shift;

                // Update active tab
                document.querySelectorAll('.shift-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Show/hide grids
                document.querySelectorAll('.shift-grid').forEach(grid => {
                    grid.style.display = grid.dataset.shift === shiftId ? 'grid' : 'none';
                });

                // Show/hide totals
                document.querySelectorAll('.shift-total').forEach(total => {
                    total.style.display = total.dataset.shift === shiftId ? 'block' : 'none';
                });
            });
        });

        // Auto refresh every 60 seconds
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
@endsection
