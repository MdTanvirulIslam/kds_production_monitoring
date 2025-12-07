@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Tables</a></li>
    <li class="breadcrumb-item active" aria-current="page">All Tables</li>
@endsection

@section('styles')
<style>
    .table-stats { display: flex; gap: 15px; margin-bottom: 20px; }
    .stat-card { 
        flex: 1; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        border-radius: 10px; 
        padding: 15px 20px; 
        color: #fff;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card .value { font-size: 1.8rem; font-weight: 700; }
    .stat-card .label { font-size: 0.75rem; opacity: 0.9; text-transform: uppercase; }
    
    .filter-card { 
        background: #f8f9fa; 
        border-radius: 10px; 
        padding: 15px 20px; 
        margin-bottom: 20px; 
    }
    
    .table-number-badge {
        font-weight: 700;
        font-size: 0.9rem;
        padding: 6px 12px;
        border-radius: 6px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        display: inline-block;
    }
    
    .table-name-cell {
        font-weight: 500;
        color: #333;
    }
    
    .worker-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .worker-cell .worker-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e0e6ed;
    }
    .worker-cell .worker-name {
        font-weight: 500;
        color: #333;
    }
    
    /* Light Status Indicators */
    .light-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .light-status .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        box-shadow: 0 0 8px currentColor;
    }
    .light-status.red { background: #ffe5e7; color: #e74c3c; }
    .light-status.red .dot { background: #e74c3c; }
    .light-status.green { background: #d4f5e9; color: #1abc9c; }
    .light-status.green .dot { background: #1abc9c; }
    .light-status.blue { background: #e0edff; color: #3498db; }
    .light-status.blue .dot { background: #3498db; }
    .light-status.yellow { background: #fff8e1; color: #f39c12; }
    .light-status.yellow .dot { background: #f39c12; }
    .light-status.off { background: #f0f0f0; color: #888; }
    .light-status.off .dot { background: #888; box-shadow: none; }
    
    .production-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .esp-ip {
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        background: #f0f2f5;
        padding: 4px 8px;
        border-radius: 4px;
        color: #555;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-dot.active { background: #28a745; }
    .status-dot.inactive { background: #dc3545; }
    
    /* Action Buttons */
    .action-btns { display: flex; gap: 5px; }
    .btn-action {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid #e0e6ed;
        background: #fff;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-action svg { width: 14px; height: 14px; }
    .btn-action.btn-view { color: #17a2b8; }
    .btn-action.btn-view:hover { background: #17a2b8; border-color: #17a2b8; color: #fff; }
    .btn-action.btn-qr { color: #6c757d; }
    .btn-action.btn-qr:hover { background: #6c757d; border-color: #6c757d; color: #fff; }
    .btn-action.btn-edit { color: #667eea; }
    .btn-action.btn-edit:hover { background: #667eea; border-color: #667eea; color: #fff; }
    .btn-action.btn-delete { color: #e74c3c; }
    .btn-action.btn-delete:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }
    
    .table > thead > tr > th {
        background: #f8f9fa;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        font-weight: 600;
        padding: 12px 15px;
        border-bottom: 2px solid #e0e6ed;
    }
    .table > tbody > tr > td {
        vertical-align: middle;
        padding: 12px 15px;
    }
    .table > tbody > tr:hover {
        background: #f8f9ff;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state svg {
        width: 60px;
        height: 60px;
        color: #ccc;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        All Tables
                    </h4>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('tables.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square me-1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                            Add Table
                        </a>
                    @endif
                </div>

                {{-- Stats Cards --}}
                @php
                    $totalTables = $tables->total();
                    $activeTables = \App\Models\Table::where('is_active', true)->count();
                    $assignedTables = \App\Models\Table::whereHas('currentAssignment')->count();
                    $alertTables = \App\Models\Table::where('current_light_status', 'red')->count();
                @endphp
                <div class="table-stats">
                    <div class="stat-card">
                        <div class="value">{{ $totalTables }}</div>
                        <div class="label">Total Tables</div>
                    </div>
                    <div class="stat-card green">
                        <div class="value">{{ $activeTables }}</div>
                        <div class="label">Active</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="value">{{ $assignedTables }}</div>
                        <div class="label">Assigned Today</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="value">{{ $alertTables }}</div>
                        <div class="label">Red Alerts</div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="filter-card">
                    <form method="GET" action="{{ route('tables.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </span>
                                    <input type="text" name="search" class="form-control" placeholder="Table #, Name, IP..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Light Status</label>
                                <select name="light_status" class="form-select">
                                    <option value="">All Lights</option>
                                    <option value="red" {{ request('light_status') == 'red' ? 'selected' : '' }}>🔴 Red</option>
                                    <option value="green" {{ request('light_status') == 'green' ? 'selected' : '' }}>🟢 Green</option>
                                    <option value="blue" {{ request('light_status') == 'blue' ? 'selected' : '' }}>🔵 Blue</option>
                                    <option value="off" {{ request('light_status') == 'off' ? 'selected' : '' }}>⚫ Off</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Assignment</label>
                                <select name="assigned" class="form-select">
                                    <option value="">All</option>
                                    <option value="yes" {{ request('assigned') == 'yes' ? 'selected' : '' }}>Assigned</option>
                                    <option value="no" {{ request('assigned') == 'no' ? 'selected' : '' }}>Unassigned</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-dark w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter me-1"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Table</th>
                                <th>Current Worker</th>
                                <th>Light Status</th>
                                <th>Today's Output</th>
                                <th>ESP32 IP</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($tables as $table)
                            <tr>
                                <td>
                                    <div>
                                        <span class="table-number-badge">{{ $table->table_number }}</span>
                                        @if($table->table_name)
                                            <div class="table-name-cell mt-1">{{ $table->table_name }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($table->currentAssignment?->worker)
                                        <div class="worker-cell">
                                            <img src="{{ $table->currentAssignment->worker->photo_url ?? asset('assets/src/assets/img/profile-30.png') }}" 
                                                 alt="" class="worker-avatar">
                                            <span class="worker-name">{{ $table->currentAssignment->worker->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">— Unassigned —</span>
                                    @endif
                                </td>
                                <td>
                                    @if($table->current_light_status === 'red')
                                        <span class="light-status red">
                                            <span class="dot"></span> Red
                                        </span>
                                    @elseif($table->current_light_status === 'green')
                                        <span class="light-status green">
                                            <span class="dot"></span> Green
                                        </span>
                                    @elseif($table->current_light_status === 'blue')
                                        <span class="light-status blue">
                                            <span class="dot"></span> Blue
                                        </span>
                                    @elseif($table->current_light_status === 'yellow')
                                        <span class="light-status yellow">
                                            <span class="dot"></span> Yellow
                                        </span>
                                    @else
                                        <span class="light-status off">
                                            <span class="dot"></span> Off
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="production-badge">{{ $table->today_production }} pcs</span>
                                </td>
                                <td>
                                    @if($table->esp32_ip)
                                        <span class="esp-ip">{{ $table->esp32_ip }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($table->is_active)
                                        <span><span class="status-dot active"></span>Active</span>
                                    @else
                                        <span><span class="status-dot inactive"></span>Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-btns justify-content-center">
                                        <a href="{{ route('tables.show', $table) }}" class="btn-action btn-view" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </a>
                                        <a href="{{ route('tables.qr-download', $table) }}" class="btn-action btn-qr" title="Download QR Code">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                        </a>
                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('tables.edit', $table) }}" class="btn-action btn-edit" title="Edit Table">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                        <p class="text-muted mb-0">No tables found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $tables->firstItem() ?? 0 }} to {{ $tables->lastItem() ?? 0 }} of {{ $tables->total() }} tables
                    </div>
                    {{ $tables->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
