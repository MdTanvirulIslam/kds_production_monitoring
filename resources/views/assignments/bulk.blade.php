{{-- resources/views/assignments/bulk.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bulk Assignment</li>
@endsection

@section('styles')
    <link href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet">
    <style>
        /* Selection Cards */
        .selection-card {
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 8px;
            background: #fff;
        }
        .selection-card:hover { border-color: #667eea; background: #f8f9ff; }
        .selection-card.selected { border-color: #667eea; background: linear-gradient(135deg, #f0f3ff 0%, #e8edff 100%); }
        .selection-card .form-check-input { pointer-events: none; }
        .selection-card .form-check-input:checked { background-color: #667eea; border-color: #667eea; }

        /* Assignment Row */
        .assignment-row {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.2s;
        }
        .assignment-row:hover {
            background: #f0f2f5;
            border-color: #dee2e6;
            transform: translateX(2px);
        }

        /* Quick Action Buttons */
        .quick-action-btn {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 20px;
        }

        /* Stats Badge */
        .stats-badge {
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Copy Section */
        .copy-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 12px;
            padding: 20px;
        }

        /* Worker Avatar */
        .worker-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e0e6ed;
        }

        /* Shift Cards */
        .shift-card {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            background: #fff;
        }
        .shift-card:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
        }
        .shift-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .shift-card .shift-icon { font-size: 28px; margin-bottom: 8px; display: block; }
        .shift-card .shift-name { font-weight: 600; font-size: 14px; }
        .shift-card .shift-time { font-size: 11px; opacity: 0.8; margin-top: 4px; }

        /* Shift Badge */
        .shift-badge {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 10px;
            background: #e8f4fc;
            color: #1b55e2;
        }

        /* Delete Button - Compact Style */
        .btn-action {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e0e6ed;
            background: #fff;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-action svg { width: 14px; height: 14px; }
        .btn-action.btn-delete { color: #e74c3c; }
        .btn-action.btn-delete:hover {
            background: #e74c3c;
            border-color: #e74c3c;
            color: #fff;
        }

        /* Table Badge */
        .table-badge {
            font-weight: 700;
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .section-header h6 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #888;
        }
        .empty-state svg {
            width: 50px;
            height: 50px;
            stroke: #ccc;
            margin-bottom: 10px;
        }

        /* Card Scrollable Area */
        .scrollable-list {
            max-height: 380px;
            overflow-y: auto;
            padding-right: 5px;
        }
        .scrollable-list::-webkit-scrollbar { width: 5px; }
        .scrollable-list::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 5px; }
        .scrollable-list::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 5px; }
        .scrollable-list::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }

        /* Header Card */
        .header-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px 20px;
        }

        /* Action Buttons Row */
        .action-btn-row .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            padding: 10px 15px;
        }
        .action-btn-row .btn svg { width: 16px; height: 16px; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">

        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8 header-card">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h4 class="mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers me-2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                            Bulk Assignment
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </span>
                            <input type="date" id="assignmentDate" class="form-control" value="{{ $date ?? date('Y-m-d') }}">
                            <button class="btn btn-primary" id="loadDataBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge badge-light-primary stats-badge me-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-1"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            Tables: <span id="totalTables">{{ $tables->count() }}</span>
                        </span>
                        <span class="badge badge-light-success stats-badge me-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Workers: <span id="totalWorkers">{{ $workers->count() }}</span>
                        </span>
                        <span class="badge badge-light-warning stats-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle me-1"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            Unassigned: <span id="unassignedCount">{{ $unassignedTables }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shift Selection --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="section-header">
                    <h6>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Select Shift for Bulk Assignment
                    </h6>
                    <small class="text-muted">
                        Selected: <strong id="selectedShiftDisplay" class="text-primary">Morning Shift (06:00 AM - 02:00 PM)</strong>
                    </small>
                </div>
                <div class="row" id="shiftSelector">
                    @forelse($shifts ?? [] as $shift)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="shift-card {{ $loop->first ? 'active' : '' }}"
                                 data-shift-id="{{ $shift->id }}"
                                 data-shift-start="{{ $shift->start_time }}"
                                 data-shift-end="{{ $shift->end_time }}">
                                <span class="shift-icon">
                                    @if(strtotime($shift->start_time) < strtotime('12:00'))
                                        🌅
                                    @elseif(strtotime($shift->start_time) < strtotime('18:00'))
                                        ☀️
                                    @else
                                        🌙
                                    @endif
                                </span>
                                <div class="shift-name">{{ $shift->name }}</div>
                                <div class="shift-time">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</div>
                            </div>
                        </div>
                    @empty
                        {{-- Default shifts if no shifts table exists --}}
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="shift-card active" data-shift-id="1" data-shift-start="06:00" data-shift-end="14:00">
                                <span class="shift-icon">🌅</span>
                                <div class="shift-name">Morning Shift</div>
                                <div class="shift-time">06:00 AM - 02:00 PM</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="shift-card" data-shift-id="2" data-shift-start="14:00" data-shift-end="22:00">
                                <span class="shift-icon">☀️</span>
                                <div class="shift-name">Day Shift</div>
                                <div class="shift-time">02:00 PM - 10:00 PM</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="shift-card" data-shift-id="3" data-shift-start="22:00" data-shift-end="06:00">
                                <span class="shift-icon">🌙</span>
                                <div class="shift-name">Night Shift</div>
                                <div class="shift-time">10:00 PM - 06:00 AM</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Copy from Previous Day --}}
        <div class="col-12 layout-spacing">
            <div class="copy-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy me-2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy Assignments from Previous Day
                        </h5>
                        <p class="mb-0 opacity-75">Quickly copy all assignments from yesterday or any previous date</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-5">
                                <input type="date" id="copyFromDate" class="form-control" value="{{ date('Y-m-d', strtotime('-1 day')) }}">
                            </div>
                            <div class="col-4">
                                <select class="form-select" id="copyShiftFilter">
                                    <option value="all">All Shifts</option>
                                    @forelse($shifts ?? [] as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                    @empty
                                        <option value="1">Morning</option>
                                        <option value="2">Day</option>
                                        <option value="3">Night</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-3">
                                <button class="btn btn-light w-100" id="copyAssignmentsBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy me-1"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-xl-5 col-lg-6 col-md-12 layout-spacing">
            {{-- Unassigned Tables --}}
            <div class="widget-content widget-content-area br-8 mb-4">
                <div class="section-header">
                    <h6>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Unassigned Tables
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary quick-action-btn" id="selectAllTables">Select All</button>
                        <button class="btn btn-sm btn-outline-secondary quick-action-btn" id="deselectAllTables">Clear</button>
                    </div>
                </div>
                <div id="unassignedTablesList" class="scrollable-list">
                    @foreach($tables as $table)
                        @if(!$table->currentAssignment)
                            <div class="selection-card table-card" data-id="{{ $table->id }}" data-number="{{ $table->table_number }}">
                                <div class="d-flex align-items-center">
                                    <div class="form-check me-3">
                                        <input class="form-check-input table-checkbox" type="checkbox" value="{{ $table->id }}">
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{{ $table->table_number }}</strong>
                                        <small class="text-muted d-block">{{ $table->table_name ?? 'Workstation' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if($unassignedTables == 0)
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <p class="mb-0">All tables are assigned!</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Available Workers --}}
            <div class="widget-content widget-content-area br-8">
                <div class="section-header">
                    <h6>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Available Workers
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary quick-action-btn" id="selectAllWorkers">Select All</button>
                        <button class="btn btn-sm btn-outline-secondary quick-action-btn" id="deselectAllWorkers">Clear</button>
                    </div>
                </div>
                <div id="availableWorkersList" class="scrollable-list">
                    @foreach($workers as $worker)
                        @if(!$worker->currentAssignment)
                            <div class="selection-card worker-card" data-id="{{ $worker->id }}" data-name="{{ $worker->name }}">
                                <div class="d-flex align-items-center">
                                    <div class="form-check me-3">
                                        <input class="form-check-input worker-checkbox" type="checkbox" value="{{ $worker->id }}">
                                    </div>
                                    <img src="{{ $worker->photo ?? asset('assets/src/assets/img/profile-30.png') }}" class="worker-avatar me-2" alt="">
                                    <div class="flex-grow-1">
                                        <strong>{{ $worker->name }}</strong>
                                        <small class="text-muted d-block">{{ $worker->worker_id }} • {{ $worker->skill_level ?? 'General' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if($availableWorkers == 0)
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <p class="mb-0">All workers are assigned!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Assignment Actions --}}
        <div class="col-xl-7 col-lg-6 col-md-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="section-header">
                    <h6>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        Assignment Actions
                    </h6>
                </div>

                {{-- Quick Actions --}}
                <div class="row mb-4 action-btn-row">
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-primary w-100" id="autoAssignBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                            Auto Assign Selected
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-success w-100" id="assignAllBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            Assign All Unassigned
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-danger w-100" id="clearAllBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Clear All for Date
                        </button>
                    </div>
                </div>

                <hr>

                {{-- Manual Pairing --}}
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3 me-2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    Manual Pairing
                </h6>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Select Table</label>
                        <select class="form-select" id="manualTable">
                            <option value="">Choose table...</option>
                            @foreach($tables as $table)
                                @if(!$table->currentAssignment)
                                    <option value="{{ $table->id }}">{{ $table->table_number }} - {{ $table->table_name ?? 'Workstation' }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Select Worker</label>
                        <select class="form-select" id="manualWorker">
                            <option value="">Choose worker...</option>
                            @foreach($workers as $worker)
                                @if(!$worker->currentAssignment)
                                    <option value="{{ $worker->id }}">{{ $worker->worker_id }} - {{ $worker->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Shift</label>
                        <select class="form-select" id="manualShift">
                            @forelse($shifts ?? [] as $shift)
                                <option value="{{ $shift->id }}" data-start="{{ $shift->start_time }}" data-end="{{ $shift->end_time }}">{{ $shift->name }}</option>
                            @empty
                                <option value="1" data-start="06:00" data-end="14:00">Morning (06:00 - 14:00)</option>
                                <option value="2" data-start="14:00" data-end="22:00">Day (14:00 - 22:00)</option>
                                <option value="3" data-start="22:00" data-end="06:00">Night (22:00 - 06:00)</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="manualAssignBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Assign
                        </button>
                    </div>
                </div>

                <hr>

                {{-- Current Assignments Preview --}}
                <div class="section-header">
                    <h6>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        Current Assignments for <span id="currentDateDisplay">{{ $date ?? date('Y-m-d') }}</span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm" id="filterShift" style="width: auto;">
                            <option value="all">All Shifts</option>
                            @forelse($shifts ?? [] as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @empty
                                <option value="1">Morning</option>
                                <option value="2">Day</option>
                                <option value="3">Night</option>
                            @endforelse
                        </select>
                        <span class="badge badge-light-success" id="assignedCount">{{ $assignedCount ?? 0 }} assigned</span>
                    </div>
                </div>

                <div id="currentAssignmentsList" class="scrollable-list" style="max-height: 320px;">
                    @forelse($assignments as $assignment)
                        <div class="assignment-row d-flex justify-content-between align-items-center"
                             data-id="{{ $assignment->id }}"
                             data-shift="{{ $assignment->shift_id ?? '' }}">
                            <div class="d-flex align-items-center">
                                <span class="table-badge me-3">{{ $assignment->table->table_number }}</span>
                                <div>
                                    <strong>{{ $assignment->worker->name }}</strong>
                                    <small class="text-muted d-block">
                                        {{ $assignment->worker->worker_id }}
                                        @if($assignment->shift_start && $assignment->shift_end)
                                            <span class="shift-badge ms-1">
                                                {{ \Carbon\Carbon::parse($assignment->shift_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($assignment->shift_end)->format('h:i A') }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <button type="button" class="btn-action btn-delete" onclick="removeAssignment({{ $assignment->id }})" title="Remove Assignment">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
                    @empty
                        <div class="empty-state" id="noAssignmentsMsg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                            <p class="mb-0">No assignments yet for this date</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const baseUrl = '{{ url("/") }}';
        let currentDate = document.getElementById('assignmentDate').value;

        // Selected shift data
        let selectedShift = {
            id: '1',
            start: '06:00',
            end: '14:00',
            name: 'Morning Shift'
        };

        // Initialize first shift as selected
        const firstShiftCard = document.querySelector('.shift-card.active');
        if (firstShiftCard) {
            selectedShift = {
                id: firstShiftCard.dataset.shiftId,
                start: firstShiftCard.dataset.shiftStart,
                end: firstShiftCard.dataset.shiftEnd,
                name: firstShiftCard.querySelector('.shift-name').textContent
            };
        }

        // Shift Card Selection
        document.querySelectorAll('.shift-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.shift-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                selectedShift = {
                    id: this.dataset.shiftId,
                    start: this.dataset.shiftStart,
                    end: this.dataset.shiftEnd,
                    name: this.querySelector('.shift-name').textContent
                };

                const timeDisplay = this.querySelector('.shift-time').textContent;
                document.getElementById('selectedShiftDisplay').textContent = `${selectedShift.name} (${timeDisplay})`;
            });
        });

        // Selection card click toggle
        document.querySelectorAll('.selection-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected', checkbox.checked);
                }
            });
        });

        // Checkbox change
        document.querySelectorAll('.selection-card input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('.selection-card').classList.toggle('selected', this.checked);
            });
        });

        // Select/Deselect All Tables
        document.getElementById('selectAllTables').addEventListener('click', () => {
            document.querySelectorAll('.table-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('.selection-card').classList.add('selected');
            });
        });
        document.getElementById('deselectAllTables').addEventListener('click', () => {
            document.querySelectorAll('.table-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('.selection-card').classList.remove('selected');
            });
        });

        // Select/Deselect All Workers
        document.getElementById('selectAllWorkers').addEventListener('click', () => {
            document.querySelectorAll('.worker-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('.selection-card').classList.add('selected');
            });
        });
        document.getElementById('deselectAllWorkers').addEventListener('click', () => {
            document.querySelectorAll('.worker-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('.selection-card').classList.remove('selected');
            });
        });

        // Load data for selected date
        document.getElementById('loadDataBtn').addEventListener('click', function() {
            const date = document.getElementById('assignmentDate').value;
            window.location.href = `{{ route('assignments.bulk') }}?date=${date}`;
        });

        // Date input change
        document.getElementById('assignmentDate').addEventListener('change', function() {
            currentDate = this.value;
            document.getElementById('currentDateDisplay').textContent = this.value;
        });

        // Auto Assign Selected
        document.getElementById('autoAssignBtn').addEventListener('click', function() {
            const selectedTables = [...document.querySelectorAll('.table-checkbox:checked')].map(cb => cb.value);
            const selectedWorkers = [...document.querySelectorAll('.worker-checkbox:checked')].map(cb => cb.value);

            if (selectedTables.length === 0 || selectedWorkers.length === 0) {
                Swal.fire('Warning', 'Please select at least one table and one worker', 'warning');
                return;
            }

            Swal.fire({
                title: 'Auto Assign?',
                html: `Assign <strong>${Math.min(selectedTables.length, selectedWorkers.length)}</strong> workers to tables for <strong>${selectedShift.name}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, assign'
            }).then((result) => {
                if (result.isConfirmed) {
                    performBulkAssignment(selectedTables, selectedWorkers);
                }
            });
        });

        // Assign All Unassigned
        document.getElementById('assignAllBtn').addEventListener('click', function() {
            const allTables = [...document.querySelectorAll('.table-checkbox')].map(cb => cb.value);
            const allWorkers = [...document.querySelectorAll('.worker-checkbox')].map(cb => cb.value);

            if (allTables.length === 0 || allWorkers.length === 0) {
                Swal.fire('Info', 'No unassigned tables or workers available', 'info');
                return;
            }

            Swal.fire({
                title: 'Assign All?',
                html: `Assign all <strong>${Math.min(allTables.length, allWorkers.length)}</strong> available pairs for <strong>${selectedShift.name}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, assign all'
            }).then((result) => {
                if (result.isConfirmed) {
                    performBulkAssignment(allTables, allWorkers);
                }
            });
        });

        // Clear All Assignments
        document.getElementById('clearAllBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Clear All Assignments?',
                text: `This will remove all assignments for ${currentDate}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e7515a',
                confirmButtonText: 'Yes, clear all'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("assignments.clear-date") }}', {
                        _token: csrfToken,
                        date: currentDate
                    })
                        .then(response => {
                            if (response.data.success) {
                                Swal.fire('Cleared!', response.data.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', error.response?.data?.message || 'Failed to clear assignments', 'error');
                        });
                }
            });
        });

        // Copy Assignments
        document.getElementById('copyAssignmentsBtn').addEventListener('click', function() {
            const fromDate = document.getElementById('copyFromDate').value;
            const toDate = document.getElementById('assignmentDate').value;
            const shiftFilter = document.getElementById('copyShiftFilter').value;

            if (fromDate === toDate) {
                Swal.fire('Error', 'Source and target dates cannot be the same', 'error');
                return;
            }

            Swal.fire({
                title: 'Copy Assignments?',
                text: `Copy assignments from ${fromDate} to ${toDate}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, copy'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("assignments.copy") }}', {
                        _token: csrfToken,
                        from_date: fromDate,
                        to_date: toDate,
                        shift_filter: shiftFilter
                    })
                        .then(response => {
                            if (response.data.success) {
                                Swal.fire('Copied!', response.data.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', error.response?.data?.message || 'Failed to copy assignments', 'error');
                        });
                }
            });
        });

        // Manual Assign
        document.getElementById('manualAssignBtn').addEventListener('click', function() {
            const tableId = document.getElementById('manualTable').value;
            const workerId = document.getElementById('manualWorker').value;
            const shiftSelect = document.getElementById('manualShift');
            const shiftOption = shiftSelect.options[shiftSelect.selectedIndex];

            if (!tableId || !workerId) {
                Swal.fire('Warning', 'Please select both a table and a worker', 'warning');
                return;
            }

            axios.post('{{ route("assignments.store") }}', {
                _token: csrfToken,
                table_id: tableId,
                worker_id: workerId,
                assigned_date: currentDate,
                shift_id: shiftSelect.value,
                shift_start: shiftOption.dataset.start,
                shift_end: shiftOption.dataset.end
            })
                .then(response => {
                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Assigned!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Failed to create assignment', 'error');
                });
        });

        // Perform Bulk Assignment with Shift
        function performBulkAssignment(tables, workers) {
            axios.post('{{ route("assignments.bulk-store") }}', {
                _token: csrfToken,
                tables: tables,
                workers: workers,
                date: currentDate,
                shift_id: selectedShift.id,
                shift_start: selectedShift.start,
                shift_end: selectedShift.end
            })
                .then(response => {
                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Bulk Assignment Complete!',
                            text: response.data.message,
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Failed to perform bulk assignment', 'error');
                });
        }

        // Remove Single Assignment
        function removeAssignment(assignmentId) {
            Swal.fire({
                title: 'Remove Assignment?',
                text: 'This will unassign the worker from this table.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#888',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${baseUrl}/assignments/${assignmentId}`, {
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    })
                        .then(response => {
                            document.querySelector(`.assignment-row[data-id="${assignmentId}"]`).remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error', error.response?.data?.message || 'Failed to remove assignment', 'error');
                        });
                }
            });
        }

        // Filter assignments by shift
        document.getElementById('filterShift').addEventListener('change', function() {
            const filterValue = this.value;
            document.querySelectorAll('.assignment-row').forEach(row => {
                if (filterValue === 'all' || row.dataset.shift === filterValue) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection
