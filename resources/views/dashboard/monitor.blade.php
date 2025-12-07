@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Live Monitor</li>
@endsection

@section('styles')
    <style>
        /* Header & Current Shift */
        .monitor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .current-shift-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
        }
        .current-shift-badge .shift-icon { font-size: 1.2rem; }
        .current-shift-badge .time-remaining {
            background: rgba(255,255,255,0.2);
            padding: 2px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }

        /* Shift Tabs */
        .shift-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .shift-tab {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
            min-width: 150px;
        }
        .shift-tab:hover { border-color: #667eea; background: #f8f9ff; }
        .shift-tab.active { 
            border-color: #667eea; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: #fff; 
        }
        .shift-tab.current { box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3); }
        .shift-tab .shift-icon { font-size: 1.3rem; }
        .shift-tab .shift-info { flex-grow: 1; }
        .shift-tab .shift-name { font-weight: 600; font-size: 0.95rem; }
        .shift-tab .shift-time { font-size: 0.7rem; opacity: 0.8; }
        .shift-tab .shift-stats {
            text-align: right;
        }
        .shift-tab .shift-stats .production { font-weight: 700; font-size: 1.1rem; }
        .shift-tab .shift-stats .tables-count { font-size: 0.7rem; opacity: 0.8; }
        .shift-tab .alert-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: #fff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Stats Cards */
        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 120px;
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            border: 1px solid #e0e6ed;
        }
        .stat-card .value { font-size: 1.8rem; font-weight: 700; }
        .stat-card .label { font-size: 0.7rem; color: #888; text-transform: uppercase; }
        .stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; }
        .stat-card.danger { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; }
        .stat-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff; }

        /* Legend */
        .legend {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .legend-item.red { background: #ffe5e7; color: #e74c3c; }
        .legend-item.green { background: #d4f5e9; color: #1abc9c; }
        .legend-item.blue { background: #e0edff; color: #3498db; }
        .legend-item.off { background: #f0f0f0; color: #888; }
        .legend-item .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Table Cards */
        .table-card {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #fff;
            position: relative;
        }
        .table-card.light-red {
            border-color: #e7515a;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
            animation: pulse-red 1.5s infinite;
        }
        .table-card.light-green {
            border-color: #1abc9c;
            background: linear-gradient(135deg, #f0fff0 0%, #d4edda 100%);
        }
        .table-card.light-blue {
            border-color: #2196f3;
            background: linear-gradient(135deg, #f0f8ff 0%, #cce5ff 100%);
        }
        .table-card.light-yellow {
            border-color: #f39c12;
            background: linear-gradient(135deg, #fffcf0 0%, #fff3cd 100%);
        }
        .table-card.light-off, .table-card.light- {
            border-color: #e0e6ed;
            background: #fff;
        }
        .table-card .table-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
        }
        .table-card .worker-name {
            font-size: 0.8rem;
            color: #888;
            margin: 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-card .production-count {
            font-size: 1rem;
            font-weight: 700;
            color: #667eea;
        }
        .table-card .shift-indicator {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 0.9rem;
        }
        .table-card .light-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 8px;
        }
        .table-card .light-status.red { background: #ffe5e7; color: #e74c3c; }
        .table-card .light-status.green { background: #d4f5e9; color: #1abc9c; }
        .table-card .light-status.blue { background: #e0edff; color: #3498db; }
        .table-card .light-status.yellow { background: #fff8e1; color: #f39c12; }
        .table-card .light-status.off { background: #f0f0f0; color: #888; }
        
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(231, 81, 90, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(231, 81, 90, 0); }
            100% { box-shadow: 0 0 0 0 rgba(231, 81, 90, 0); }
        }

        /* Responsive Grid */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 15px;
        }

        @media (max-width: 768px) {
            .tables-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .shift-tab {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                {{-- Header --}}
                <div class="monitor-header">
                    <h4 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor me-2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        Live Monitor
                    </h4>
                    
                    <div class="d-flex align-items-center gap-3">
                        @if($currentShift)
                            <div class="current-shift-badge">
                                <span class="shift-icon">
                                    @if(strtotime($currentShift->start_time) < strtotime('12:00'))
                                        🌅
                                    @elseif(strtotime($currentShift->start_time) < strtotime('18:00'))
                                        ☀️
                                    @else
                                        🌙
                                    @endif
                                </span>
                                <span>{{ $currentShift->name }} Shift</span>
                                @if($shiftTimeRemaining)
                                    <span class="time-remaining">{{ $shiftTimeRemaining }} left</span>
                                @endif
                            </div>
                        @endif
                        
                        <span class="badge badge-light-secondary" id="last-update">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock me-1"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            {{ now('Asia/Dhaka')->format('h:i:s A') }}
                        </span>
                        
                        <button class="btn btn-sm btn-primary" onclick="location.reload()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </button>
                    </div>
                </div>

                {{-- Shift Tabs --}}
                <div class="shift-tabs">
                    <div class="shift-tab active" data-shift="all" onclick="filterByShift('all')">
                        <span class="shift-icon">📋</span>
                        <div class="shift-info">
                            <div class="shift-name">All Shifts</div>
                            <div class="shift-time">All tables</div>
                        </div>
                        <div class="shift-stats">
                            <div class="production">{{ number_format($stats['today_production'] ?? 0) }}</div>
                            <div class="tables-count">{{ $stats['total_tables'] }} tables</div>
                        </div>
                    </div>
                    
                    @foreach($shifts as $shift)
                        <div class="shift-tab {{ $currentShift && $currentShift->id == $shift->id ? 'current' : '' }}" 
                             data-shift="{{ $shift->id }}" 
                             onclick="filterByShift({{ $shift->id }})"
                             style="position: relative;">
                            <span class="shift-icon">
                                @if(strtotime($shift->start_time) < strtotime('12:00'))
                                    🌅
                                @elseif(strtotime($shift->start_time) < strtotime('18:00'))
                                    ☀️
                                @else
                                    🌙
                                @endif
                            </span>
                            <div class="shift-info">
                                <div class="shift-name">{{ $shift->name }}</div>
                                <div class="shift-time">
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                </div>
                            </div>
                            <div class="shift-stats">
                                <div class="production">{{ number_format($shiftProduction[$shift->id] ?? 0) }}</div>
                                <div class="tables-count">{{ $shiftTableCounts[$shift->id] ?? 0 }} tables</div>
                            </div>
                            @if(($shiftAlerts[$shift->id] ?? 0) > 0)
                                <span class="alert-badge">{{ $shiftAlerts[$shift->id] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Stats Row --}}
                <div class="stats-row">
                    <div class="stat-card primary">
                        <div class="value">{{ $stats['total_tables'] }}</div>
                        <div class="label">Total Tables</div>
                    </div>
                    <div class="stat-card success">
                        <div class="value">{{ $stats['assigned_tables'] }}</div>
                        <div class="label">Assigned</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="value">{{ $stats['red_alerts'] }}</div>
                        <div class="label">🔴 Alerts</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                        <div class="value">{{ $stats['green_status'] }}</div>
                        <div class="label">🟢 Good</div>
                    </div>
                    <div class="stat-card info">
                        <div class="value">{{ $stats['blue_status'] }}</div>
                        <div class="label">🔵 Help</div>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="legend">
                    <span class="legend-item red"><span class="dot"></span> Alert / Need Help</span>
                    <span class="legend-item green"><span class="dot"></span> Running Good</span>
                    <span class="legend-item blue"><span class="dot"></span> Material Needed</span>
                    <span class="legend-item off"><span class="dot"></span> Normal / Idle</span>
                </div>
            </div>
        </div>

        {{-- Table Grid --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="tables-grid" id="tablesGrid">
                    @foreach($tables as $table)
                        <div class="table-card light-{{ $table->current_light_status }}" 
                             id="table-{{ $table->id }}"
                             data-shift="{{ $table->currentAssignment->shift_id ?? '' }}">
                            
                            {{-- Shift Indicator --}}
                            @if($table->currentAssignment && $table->currentAssignment->shift_id)
                                <span class="shift-indicator">
                                    @php
                                        $assignedShift = $shifts->firstWhere('id', $table->currentAssignment->shift_id);
                                    @endphp
                                    @if($assignedShift)
                                        @if(strtotime($assignedShift->start_time) < strtotime('12:00'))
                                            🌅
                                        @elseif(strtotime($assignedShift->start_time) < strtotime('18:00'))
                                            ☀️
                                        @else
                                            🌙
                                        @endif
                                    @endif
                                </span>
                            @endif
                            
                            <div class="table-number">{{ $table->table_number }}</div>
                            <div class="worker-name">
                                {{ $table->currentAssignment?->worker?->name ?? '— Unassigned —' }}
                            </div>
                            <div class="production-count">
                                {{ $table->today_production }} pcs
                            </div>
                            
                            @php
                                $lightStatus = $table->current_light_status ?: 'off';
                            @endphp
                            <div class="light-status {{ $lightStatus }}">
                                @if($lightStatus === 'red')
                                    🔴 Alert
                                @elseif($lightStatus === 'green')
                                    🟢 Good
                                @elseif($lightStatus === 'blue')
                                    🔵 Help
                                @elseif($lightStatus === 'yellow')
                                    🟡 Warning
                                @else
                                    ⚫ Normal
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($tables->isEmpty())
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid mb-3"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <p class="text-muted">No active tables found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Filter tables by shift
        function filterByShift(shiftId) {
            // Update active tab
            document.querySelectorAll('.shift-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.shift == shiftId) {
                    tab.classList.add('active');
                }
            });

            // Filter table cards
            document.querySelectorAll('.table-card').forEach(card => {
                if (shiftId === 'all') {
                    card.style.display = 'block';
                } else {
                    const cardShift = card.dataset.shift;
                    if (cardShift == shiftId) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }

        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);

        // Update timestamp every second
        setInterval(function() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true 
            });
            document.getElementById('last-update').innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock me-1"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                ${timeStr}
            `;
        }, 1000);

        // Highlight current shift tab on load
        document.addEventListener('DOMContentLoaded', function() {
            const currentShiftTab = document.querySelector('.shift-tab.current');
            if (currentShiftTab) {
                // Add a subtle animation to current shift
                currentShiftTab.style.animation = 'none';
                currentShiftTab.offsetHeight; // Trigger reflow
            }
        });
    </script>
@endsection
