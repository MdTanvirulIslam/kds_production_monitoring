{{-- resources/views/reports/worker.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Worker Report</li>
@endsection

@section('styles')
    <style>
        .worker-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 25px; color: #fff; }
        .worker-header .avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); }
        .worker-header .name { font-size: 1.5rem; font-weight: 700; }
        .worker-header .id { opacity: 0.8; }
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 3px 15px rgba(0,0,0,0.08); text-align: center; height: 100%; }
        .stat-card .value { font-size: 1.8rem; font-weight: 700; color: #667eea; }
        .stat-card .label { font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .stat-card.success .value { color: #2ecc71; }
        .stat-card.warning .value { color: #e74c3c; }
        .daily-chart { display: flex; align-items: flex-end; height: 150px; gap: 3px; overflow-x: auto; padding-bottom: 25px; }
        .daily-chart .bar-item { min-width: 25px; text-align: center; }
        .daily-chart .bar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 3px 3px 0 0; width: 20px; margin: 0 auto; }
        .daily-chart .bar-label { font-size: 0.6rem; color: #888; margin-top: 5px; transform: rotate(-45deg); white-space: nowrap; }
        .log-row { background: #f8f9fa; border-radius: 8px; padding: 12px 15px; margin-bottom: 8px; }
        .log-row:hover { background: #e9ecef; }
        .log-row .time { font-family: monospace; background: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .log-row .table-badge { font-weight: 600; color: #667eea; }
        .log-row .count { font-weight: 700; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Worker Header --}}
        <div class="col-12 layout-spacing">
            <div class="worker-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="{{ $worker->photo ? asset('storage/'.$worker->photo) : asset('assets/src/assets/img/profile-30.png') }}"
                             class="avatar" alt="{{ $worker->name }}">
                    </div>
                    <div class="col">
                        <div class="name">{{ $worker->name }}</div>
                        <div class="id">{{ $worker->worker_id }}</div>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark me-1">{{ $worker->skill_level ?? 'General' }}</span>
                            <span class="badge bg-light text-dark">{{ $worker->department ?? 'Production' }}</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('workers.show', $worker) }}" class="btn btn-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user me-1"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Date Range Filter --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <form action="{{ route('reports.worker', $worker) }}" method="GET" class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter me-1"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card">
                <div class="value">{{ number_format($summary['total_production']) }}</div>
                <div class="label">Total Production</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card">
                <div class="value">{{ number_format($summary['avg_daily']) }}</div>
                <div class="label">Avg Daily</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card success">
                <div class="value">{{ $summary['green_lights'] }}</div>
                <div class="label">Green Lights</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card warning">
                <div class="value">{{ $summary['red_alerts'] }}</div>
                <div class="label">Red Alerts</div>
            </div>
        </div>

        {{-- Daily Production Chart --}}
        <div class="col-xl-8 col-lg-7 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity me-2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    Daily Production
                </h6>

                @php
                    $maxDaily = $dailyProduction->max('total') ?: 1;
                @endphp

                <div class="daily-chart">
                    @forelse($dailyProduction as $day)
                        <div class="bar-item">
                            <div class="bar" style="height: {{ ($day->total / $maxDaily) * 120 }}px;" title="{{ $day->total }}"></div>
                            <div class="bar-label">{{ \Carbon\Carbon::parse($day->production_date)->format('d') }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted w-100 py-4">No production data for this period</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-xl-4 col-lg-5 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Performance Summary
                </h6>

                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Working Days</span>
                        <strong>{{ $summary['working_days'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Output</span>
                        <strong>{{ number_format($summary['total_production']) }} pcs</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Daily Average</span>
                        <strong>{{ number_format($summary['avg_daily']) }} pcs</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">🟢 Green Lights</span>
                        <strong class="text-success">{{ $summary['green_lights'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">🔴 Red Alerts</span>
                        <strong class="text-danger">{{ $summary['red_alerts'] }}</strong>
                    </div>
                </div>

                @if($summary['working_days'] > 0)
                    <div class="text-center mt-4">
                        <div class="text-muted mb-1">Efficiency Score</div>
                        <div style="font-size: 2rem; font-weight: 700; color: {{ $summary['red_alerts'] == 0 ? '#2ecc71' : ($summary['red_alerts'] < 5 ? '#f39c12' : '#e74c3c') }};">
                            {{ $summary['red_alerts'] == 0 ? '⭐ Excellent' : ($summary['red_alerts'] < 5 ? '👍 Good' : '⚠️ Needs Improvement') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Production Logs --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list me-2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    Recent Production Logs
                </h6>

                @forelse($recentLogs as $log)
                    <div class="log-row d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <span class="time">{{ $log->production_hour }}</span>
                            <span class="table-badge">{{ $log->table->table_number }}</span>
                            <span class="text-muted">{{ $log->product_type ?? '-' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted">{{ $log->production_date->format('M d, Y') }}</span>
                            <span class="count badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                        {{ $log->garments_count }} pcs
                    </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        No production logs found
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($recentLogs->hasPages())
                    <div class="mt-4">
                        {{ $recentLogs->appends(['start_date' => $startDate, 'end_date' => $endDate])->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
