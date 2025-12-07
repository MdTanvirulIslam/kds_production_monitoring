@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Workers</a></li>
    <li class="breadcrumb-item active" aria-current="page">All Workers</li>
@endsection

@section('styles')
<style>
    .worker-stats { display: flex; gap: 15px; margin-bottom: 20px; }
    .stat-card { 
        flex: 1; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        border-radius: 10px; 
        padding: 15px 20px; 
        color: #fff; 
    }
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
    
    .worker-photo {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e0e6ed;
    }
    
    .worker-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .worker-info .name { font-weight: 600; color: #333; }
    .worker-info .id { font-size: 0.75rem; color: #888; }
    
    .skill-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .skill-badge.expert { background: #d4edda; color: #155724; }
    .skill-badge.intermediate { background: #fff3cd; color: #856404; }
    .skill-badge.beginner { background: #e2e3e5; color: #383d41; }
    
    .production-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
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
    
    .table-assignment {
        background: #f0f2f5;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
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
    }
    .btn-action svg { width: 14px; height: 14px; }
    .btn-action.btn-view { color: #17a2b8; }
    .btn-action.btn-view:hover { background: #17a2b8; border-color: #17a2b8; color: #fff; }
    .btn-action.btn-edit { color: #667eea; }
    .btn-action.btn-edit:hover { background: #667eea; border-color: #667eea; color: #fff; }
    .btn-action.btn-report { color: #28a745; }
    .btn-action.btn-report:hover { background: #28a745; border-color: #28a745; color: #fff; }
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
</style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users me-2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        All Workers
                    </h4>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('workers.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                            Add Worker
                        </a>
                    @endif
                </div>

                {{-- Stats Cards --}}
                @php
                    $totalWorkers = $workers->total();
                    $activeWorkers = \App\Models\Worker::where('is_active', true)->count();
                    $expertWorkers = \App\Models\Worker::where('skill_level', 'expert')->count();
                    $assignedToday = \App\Models\Worker::whereHas('currentAssignment')->count();
                @endphp
                <div class="worker-stats">
                    <div class="stat-card">
                        <div class="value">{{ $totalWorkers }}</div>
                        <div class="label">Total Workers</div>
                    </div>
                    <div class="stat-card green">
                        <div class="value">{{ $activeWorkers }}</div>
                        <div class="label">Active</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="value">{{ $expertWorkers }}</div>
                        <div class="label">Expert Level</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="value">{{ $assignedToday }}</div>
                        <div class="label">Assigned Today</div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="filter-card">
                    <form method="GET" action="{{ route('workers.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </span>
                                    <input type="text" name="search" class="form-control" placeholder="Name, ID, Phone..." value="{{ request('search') }}">
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
                                <label class="form-label small text-muted">Skill Level</label>
                                <select name="skill_level" class="form-select">
                                    <option value="">All Skills</option>
                                    <option value="beginner" {{ request('skill_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="intermediate" {{ request('skill_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="expert" {{ request('skill_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Department</label>
                                <select name="department" class="form-select">
                                    <option value="">All Depts</option>
                                    <option value="sewing" {{ request('department') == 'sewing' ? 'selected' : '' }}>Sewing</option>
                                    <option value="cutting" {{ request('department') == 'cutting' ? 'selected' : '' }}>Cutting</option>
                                    <option value="finishing" {{ request('department') == 'finishing' ? 'selected' : '' }}>Finishing</option>
                                    <option value="quality" {{ request('department') == 'quality' ? 'selected' : '' }}>Quality</option>
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
                                <th>Worker</th>
                                <th>Phone</th>
                                <th>Skill</th>
                                <th>Current Table</th>
                                <th>Today's Output</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($workers as $worker)
                            <tr>
                                <td>
                                    <div class="worker-name-cell">
                                        <img src="{{ $worker->photo_url }}"
                                             alt="{{ $worker->name }}"
                                             class="worker-photo">
                                        <div class="worker-info">
                                            <div class="name">{{ $worker->name }}</div>
                                            <div class="id">#{{ $worker->worker_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $worker->phone ?? '-' }}</td>
                                <td>
                                    <span class="skill-badge {{ $worker->skill_level }}">
                                        {{ ucfirst($worker->skill_level) }}
                                    </span>
                                </td>
                                <td>
                                    @if($worker->currentAssignment?->table)
                                        <span class="table-assignment">
                                            📍 {{ $worker->currentAssignment->table->table_number }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="production-badge">{{ $worker->today_production }} pcs</span>
                                </td>
                                <td>
                                    @if($worker->is_active)
                                        <span class="status-dot active"></span>Active
                                    @else
                                        <span class="status-dot inactive"></span>Inactive
                                    @endif
                                </td>
                                <td>
                                    <div class="action-btns justify-content-center">
                                        <a href="{{ route('workers.show', $worker) }}" class="btn-action btn-view" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </a>
                                        @if(in_array(auth()->user()->role, ['admin', 'monitor']))
                                            <a href="{{ route('reports.worker', $worker) }}" class="btn-action btn-report" title="View Report">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                            </a>
                                        @endif
                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('workers.edit', $worker) }}" class="btn-action btn-edit" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    <p class="text-muted mb-0">No workers found</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $workers->firstItem() ?? 0 }} to {{ $workers->lastItem() ?? 0 }} of {{ $workers->total() }} workers
                    </div>
                    {{ $workers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
