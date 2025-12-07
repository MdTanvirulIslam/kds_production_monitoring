{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Reports</li>
@endsection

@section('styles')
    <style>
        .report-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); height: 100%; transition: transform 0.3s; }
        .report-card:hover { transform: translateY(-3px); }
        .report-card .icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 15px; }
        .report-card .icon.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .report-card .icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .report-card .icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .report-card .icon.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .report-card h5 { font-weight: 700; margin-bottom: 8px; }
        .report-card p { color: #888; font-size: 0.9rem; margin-bottom: 15px; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; color: #fff; text-align: center; }
        .stat-box .value { font-size: 2rem; font-weight: 700; }
        .stat-box .label { font-size: 0.8rem; opacity: 0.9; text-transform: uppercase; }
        .quick-stat { background: #f8f9fa; border-radius: 10px; padding: 15px; text-align: center; }
        .quick-stat .value { font-size: 1.5rem; font-weight: 700; color: #333; }
        .quick-stat .label { font-size: 0.75rem; color: #888; }
        .worker-rank { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .worker-rank:last-child { border-bottom: none; }
        .worker-rank .rank { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-right: 12px; font-size: 0.8rem; }
        .worker-rank .rank.gold { background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); color: #fff; }
        .worker-rank .rank.silver { background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%); color: #fff; }
        .worker-rank .rank.bronze { background: linear-gradient(135deg, #b08d57 0%, #744d15 100%); color: #fff; }
        .worker-rank .name { flex-grow: 1; font-weight: 500; }
        .worker-rank .production { font-weight: 700; color: #667eea; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2 me-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Reports Dashboard
                </h4>
                <div>
                    <a href="{{ route('reports.daily') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar me-1"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Daily Report
                    </a>
                    <a href="{{ route('reports.monthly') }}" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        Monthly Report
                    </a>
                </div>
            </div>
        </div>

        {{-- Today's Stats --}}
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-box">
                <div class="value">{{ number_format($todayStats['production']) }}</div>
                <div class="label">Today's Production</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-box" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="value">{{ number_format($todayStats['target']) }}</div>
                <div class="label">Daily Target</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="value">{{ $todayStats['workers'] }}</div>
                <div class="label">Active Workers</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="value">{{ $todayStats['alerts'] }}</div>
                <div class="label">Alerts Today</div>
            </div>
        </div>

        {{-- Report Cards --}}
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="report-card">
                <div class="icon purple">📊</div>
                <h5>Daily Report</h5>
                <p>View detailed hourly production, worker performance, and alerts for any date.</p>
                <a href="{{ route('reports.daily') }}" class="btn btn-primary btn-sm">View Report →</a>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="report-card">
                <div class="icon green">📈</div>
                <h5>Monthly Report</h5>
                <p>Analyze monthly trends, compare daily outputs, and track worker rankings.</p>
                <a href="{{ route('reports.monthly') }}" class="btn btn-success btn-sm">View Report →</a>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="report-card">
                <div class="icon orange">👷</div>
                <h5>Worker Reports</h5>
                <p>Individual worker performance tracking and history analysis.</p>
                <a href="{{ route('workers.index') }}" class="btn btn-warning btn-sm">Select Worker →</a>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 layout-spacing">
            <div class="report-card">
                <div class="icon blue">📥</div>
                <h5>Export Data</h5>
                <p>Download production data as CSV for external analysis and record keeping.</p>
                <div class="dropdown">
                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Export CSV
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('reports.export', ['type' => 'daily', 'date' => today()->format('Y-m-d')]) }}">Today's Report</a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.export', ['type' => 'monthly', 'month' => now()->format('Y-m')]) }}">This Month</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Weekly Production Chart --}}
        <div class="col-xl-8 col-lg-7 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity me-2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    Last 7 Days Production
                </h6>
                <div class="row">
                    @foreach($weeklyProduction as $day)
                        <div class="col">
                            <div class="quick-stat">
                                <div class="value">{{ number_format($day->total) }}</div>
                                <div class="label">{{ \Carbon\Carbon::parse($day->production_date)->format('D') }}</div>
                            </div>
                        </div>
                    @endforeach
                    @if($weeklyProduction->isEmpty())
                        <div class="col-12 text-center text-muted py-4">
                            No production data for the last 7 days
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Workers --}}
        <div class="col-xl-4 col-lg-5 layout-spacing">
            <div class="widget-content widget-content-area br-8 h-100">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award me-2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    Top Workers This Month
                </h6>
                @forelse($topWorkers as $index => $worker)
                    <div class="worker-rank">
                        <div class="rank {{ $index == 0 ? 'gold' : ($index == 1 ? 'silver' : ($index == 2 ? 'bronze' : '')) }}">
                            @if($index == 0) 🥇
                            @elseif($index == 1) 🥈
                            @elseif($index == 2) 🥉
                            @else {{ $index + 1 }}
                            @endif
                        </div>
                        <div class="name">{{ Str::limit($worker->name, 15) }}</div>
                        <div class="production">{{ number_format($worker->monthly_production) }}</div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        No production data this month
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
