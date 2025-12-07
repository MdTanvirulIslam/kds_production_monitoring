{{-- resources/views/reports/monthly.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Monthly Report</li>
@endsection

@section('styles')
    <style>
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 3px 15px rgba(0,0,0,0.08); text-align: center; height: 100%; }
        .stat-card .value { font-size: 1.8rem; font-weight: 700; }
        .stat-card .label { font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .stat-card.primary .label { color: rgba(255,255,255,0.8); }
        .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; }
        .stat-card.success .label { color: rgba(255,255,255,0.8); }
        .daily-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
        .daily-cell { background: #f8f9fa; border-radius: 6px; padding: 8px 4px; text-align: center; min-height: 60px; }
        .daily-cell.has-data { background: linear-gradient(135deg, #667eea22 0%, #764ba222 100%); border: 1px solid #667eea44; }
        .daily-cell .date { font-size: 0.7rem; color: #888; }
        .daily-cell .value { font-size: 0.85rem; font-weight: 700; }
        .daily-cell .value.zero { color: #ccc; }
        .week-header { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 5px; }
        .week-header div { text-align: center; font-size: 0.7rem; font-weight: 600; color: #888; text-transform: uppercase; }
        .worker-row { display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
        .worker-row:last-child { border-bottom: none; }
        .worker-row .rank { width: 28px; height: 28px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; margin-right: 12px; }
        .worker-row .rank.gold { background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); color: #fff; }
        .worker-row .rank.silver { background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%); color: #fff; }
        .worker-row .rank.bronze { background: linear-gradient(135deg, #b08d57 0%, #744d15 100%); color: #fff; }
        .worker-row .info { flex-grow: 1; }
        .worker-row .name { font-weight: 600; }
        .worker-row .id { font-size: 0.75rem; color: #888; }
        .worker-row .production { font-weight: 700; color: #667eea; font-size: 1.1rem; }
        .alert-stat { display: flex; align-items: center; padding: 10px; background: #f8f9fa; border-radius: 8px; margin-bottom: 8px; }
        .alert-stat .color-dot { width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; }
        .alert-stat .color-dot.red { background: #e74c3c; }
        .alert-stat .color-dot.green { background: #2ecc71; }
        .alert-stat .color-dot.blue { background: #3498db; }
        .alert-stat .color-dot.yellow { background: #f1c40f; }
        .alert-stat .label { flex-grow: 1; font-weight: 500; }
        .alert-stat .count { font-weight: 700; }
        .weekly-bar { display: flex; align-items: flex-end; height: 150px; gap: 10px; }
        .weekly-bar .bar-item { flex: 1; text-align: center; }
        .weekly-bar .bar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 6px 6px 0 0; margin: 0 auto; width: 40px; transition: height 0.3s; }
        .weekly-bar .bar-label { font-size: 0.75rem; color: #888; margin-top: 8px; }
        .weekly-bar .bar-value { font-size: 0.8rem; font-weight: 600; margin-bottom: 5px; }
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            Monthly Report
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('reports.monthly') }}" method="GET" class="d-flex gap-2">
                            <input type="month" name="month" class="form-control" value="{{ $month }}">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('reports.export', ['type' => 'monthly', 'month' => $month]) }}" class="btn btn-outline-primary">
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
                <div class="value">{{ number_format($summary['total_production']) }}</div>
                <div class="label">Total Production</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card success">
                <div class="value">{{ number_format($summary['avg_daily']) }}</div>
                <div class="label">Avg Daily</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card">
                <div class="value">{{ $summary['working_days'] }}</div>
                <div class="label">Working Days</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-card">
                <div class="value">{{ $summary['active_workers'] }}</div>
                <div class="label">Active Workers</div>
            </div>
        </div>

        {{-- Weekly Totals Chart --}}
        <div class="col-xl-8 col-lg-7 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2 me-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Weekly Production
                </h6>

                @php
                    $maxWeekly = $weeklyTotals->max('total') ?: 1;
                @endphp

                <div class="weekly-bar">
                    @forelse($weeklyTotals as $week)
                        <div class="bar-item">
                            <div class="bar-value">{{ number_format($week->total) }}</div>
                            <div class="bar" style="height: {{ ($week->total / $maxWeekly) * 120 }}px;"></div>
                            <div class="bar-label">Week {{ $week->week_number }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted w-100">No data available</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Alert Statistics --}}
        <div class="col-xl-4 col-lg-5 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell me-2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    Light Indicators
                </h6>

                @php
                    $colors = ['red' => 'Red Alerts', 'green' => 'Green Status', 'blue' => 'Blue Status', 'yellow' => 'Yellow Warnings'];
                @endphp

                @foreach($colors as $color => $label)
                    <div class="alert-stat">
                        <div class="color-dot {{ $color }}"></div>
                        <div class="label">{{ $label }}</div>
                        <div class="count">{{ $alertStats[$color] ?? 0 }}</div>
                    </div>
                @endforeach

                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Indicators</span>
                        <strong>{{ $alertStats->sum() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daily Production Calendar --}}
        <div class="col-xl-8 col-lg-7 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar me-2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Daily Production - {{ \Carbon\Carbon::parse($month)->format('F Y') }}
                </h6>

                <div class="week-header">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>

                @php
                    $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
                    $endDate = \Carbon\Carbon::parse($month)->endOfMonth();
                    $startDayOfWeek = $startDate->dayOfWeek;
                    $productionMap = $dailyProduction->pluck('total', 'production_date')->toArray();
                @endphp

                <div class="daily-grid">
                    {{-- Empty cells for days before month starts --}}
                    @for($i = 0; $i < $startDayOfWeek; $i++)
                        <div class="daily-cell" style="background: transparent;"></div>
                    @endfor

                    {{-- Days of the month --}}
                    @for($day = 1; $day <= $endDate->day; $day++)
                        @php
                            $dateStr = $startDate->copy()->day($day)->format('Y-m-d');
                            $production = $productionMap[$dateStr] ?? 0;
                        @endphp
                        <div class="daily-cell {{ $production > 0 ? 'has-data' : '' }}">
                            <div class="date">{{ $day }}</div>
                            <div class="value {{ $production == 0 ? 'zero' : '' }}">{{ $production > 0 ? number_format($production) : '-' }}</div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- Top Workers --}}
        <div class="col-xl-4 col-lg-5 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award me-2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    Top 10 Workers
                </h6>
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($topWorkers as $index => $worker)
                        <div class="worker-row">
                            <div class="rank {{ $index == 0 ? 'gold' : ($index == 1 ? 'silver' : ($index == 2 ? 'bronze' : '')) }}">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="info">
                                <div class="name">{{ Str::limit($worker->name, 15) }}</div>
                                <div class="id">{{ $worker->worker_id }}</div>
                            </div>
                            <div class="production">{{ number_format($worker->monthly_production) }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No production data
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Table Performance --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Table Performance
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Table</th>
                            <th>Name</th>
                            <th>Monthly Production</th>
                            <th>% of Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tablePerformance->take(20) as $index => $table)
                            <tr>
                                <td>
                                    @if($index == 0) 🥇
                                    @elseif($index == 1) 🥈
                                    @elseif($index == 2) 🥉
                                    @else {{ $index + 1 }}
                                    @endif
                                </td>
                                <td><strong>{{ $table->table_number }}</strong></td>
                                <td>{{ $table->table_name ?? '-' }}</td>
                                <td>
                                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                                    {{ number_format($table->monthly_production) }}
                                </span>
                                </td>
                                <td>{{ $summary['total_production'] > 0 ? round(($table->monthly_production / $summary['total_production']) * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No production data</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
