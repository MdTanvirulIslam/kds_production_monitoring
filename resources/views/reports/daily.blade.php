{{-- resources/views/reports/daily.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Daily Report</li>
@endsection

@section('styles')
    <style>
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 3px 15px rgba(0,0,0,0.08); text-align: center; }
        .stat-card .value { font-size: 2rem; font-weight: 700; }
        .stat-card .label { font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .stat-card.primary .label { color: rgba(255,255,255,0.8); }
        .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; }
        .stat-card.success .label { color: rgba(255,255,255,0.8); }
        .hourly-bar { height: 120px; display: flex; align-items: flex-end; justify-content: center; }
        .hourly-bar .bar { width: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 4px 4px 0 0; margin: 0 2px; transition: height 0.3s; }
        .hourly-bar .bar:hover { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        .hourly-label { font-size: 0.7rem; color: #888; text-align: center; margin-top: 5px; }
        .hourly-value { font-size: 0.75rem; font-weight: 600; text-align: center; margin-bottom: 3px; }
        .worker-row { display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
        .worker-row:last-child { border-bottom: none; }
        .worker-row .rank { width: 28px; height: 28px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; margin-right: 12px; }
        .worker-row .rank.top { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .worker-row .info { flex-grow: 1; }
        .worker-row .name { font-weight: 600; }
        .worker-row .id { font-size: 0.75rem; color: #888; }
        .worker-row .production { font-weight: 700; color: #667eea; font-size: 1.1rem; }
        .alert-item { background: #fff5f5; border-left: 3px solid #e74c3c; padding: 10px 15px; margin-bottom: 8px; border-radius: 0 8px 8px 0; }
        .alert-item .time { font-size: 0.75rem; color: #888; }
        .alert-item .table { font-weight: 700; color: #e74c3c; }
        .shift-filter { display: flex; gap: 8px; margin-bottom: 15px; }
        .shift-btn { padding: 8px 16px; border: 2px solid #e0e6ed; border-radius: 8px; background: #fff; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .shift-btn:hover { border-color: #667eea; }
        .shift-btn.active { border-color: #667eea; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h4 class="mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar me-2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Daily Report
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('reports.daily') }}" method="GET" class="d-flex gap-2">
                            <input type="date" name="date" class="form-control" value="{{ $date }}">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('reports.export', ['type' => 'daily', 'date' => $date]) }}" class="btn btn-outline-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card primary">
                <div class="value">{{ number_format($totalProduction) }}</div>
                <div class="label">Total Production</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card success">
                <div class="value">{{ number_format($target->daily_target ?? 0) }}</div>
                <div class="label">Daily Target</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card">
                <div class="value {{ $target ? ($totalProduction >= $target->daily_target ? 'text-success' : 'text-warning') : '' }}">
                    {{ $target && $target->daily_target > 0 ? round(($totalProduction / $target->daily_target) * 100, 1) : 0 }}%
                </div>
                <div class="label">Target Achievement</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card">
                <div class="value">{{ $workerProduction->count() }}</div>
                <div class="label">Active Workers</div>
            </div>
        </div>

        {{-- Hourly Production Chart --}}
        <div class="col-xl-8 col-lg-7 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Hourly Production
                </h6>

                @php
                    $maxHourly = $hourlyProduction->max('total') ?: 1;
                @endphp

                <div class="d-flex justify-content-between align-items-end" style="min-height: 180px;">
                    @for($hour = 6; $hour <= 22; $hour++)
                        @php
                            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                            $hourData = $hourlyProduction->firstWhere('production_hour', $hourStr);
                            $count = $hourData ? $hourData->total : 0;
                            $height = $maxHourly > 0 ? ($count / $maxHourly) * 100 : 0;
                        @endphp
                        <div class="text-center" style="flex: 1;">
                            <div class="hourly-value">{{ $count > 0 ? $count : '' }}</div>
                            <div class="hourly-bar">
                                <div class="bar" style="height: {{ max($height, 5) }}px;" title="{{ $count }} pcs"></div>
                            </div>
                            <div class="hourly-label">{{ $hour }}h</div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        <div class="col-xl-4 col-lg-5 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle me-2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Alerts ({{ $alerts->count() }})
                </h6>
                <div style="max-height: 200px; overflow-y: auto;">
                    @forelse($alerts->take(10) as $alert)
                        <div class="alert-item">
                            <div class="d-flex justify-content-between">
                                <span class="table">{{ $alert->table->table_number }}</span>
                                <span class="time">{{ $alert->activated_at->format('H:i') }}</span>
                            </div>
                            <small class="text-muted">{{ $alert->worker->name ?? 'N/A' }}</small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            ✅ No alerts on this day
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Worker Production --}}
        <div class="col-xl-6 col-lg-6 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users me-2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Worker Production
                </h6>
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($workerProduction as $index => $worker)
                        <div class="worker-row">
                            <div class="rank {{ $index < 3 ? 'top' : '' }}">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="info">
                                <div class="name">{{ $worker->name }}</div>
                                <div class="id">{{ $worker->worker_id }}</div>
                            </div>
                            <div class="production">{{ number_format($worker->daily_production) }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No production recorded
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Table Production --}}
        <div class="col-xl-6 col-lg-6 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Table Production
                </h6>
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($tableProduction as $index => $table)
                        <div class="worker-row">
                            <div class="rank {{ $index < 3 ? 'top' : '' }}">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="info">
                                <div class="name">{{ $table->table_number }}</div>
                                <div class="id">{{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}</div>
                            </div>
                            <div class="production">{{ number_format($table->daily_production) }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No production recorded
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
