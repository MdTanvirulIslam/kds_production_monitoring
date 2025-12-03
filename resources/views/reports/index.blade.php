@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Reports</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Today's Statistics --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 layout-spacing">
            <div class="widget widget-card-one">
                <div class="widget-content">
                    <div class="w-numeric-value">
                        <div class="w-icon" style="background: #1abc9c;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-box"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        </div>
                        <div class="w-content">
                            <span class="w-value">{{ number_format($todayStats['production']) }}</span>
                            <span class="w-numeric-title">Today's Production</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 layout-spacing">
            <div class="widget widget-card-one">
                <div class="widget-content">
                    <div class="w-numeric-value">
                        <div class="w-icon" style="background: #e2a03f;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-target"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                        </div>
                        <div class="w-content">
                            <span class="w-value">{{ number_format($todayStats['target']) }}</span>
                            <span class="w-numeric-title">Today's Target</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 layout-spacing">
            <div class="widget widget-card-one">
                <div class="widget-content">
                    <div class="w-numeric-value">
                        <div class="w-icon" style="background: #2196f3;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="w-content">
                            <span class="w-value">{{ $todayStats['workers'] }}</span>
                            <span class="w-numeric-title">Active Workers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 layout-spacing">
            <div class="widget widget-card-one">
                <div class="widget-content">
                    <div class="w-numeric-value">
                        <div class="w-icon" style="background: #e7515a;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                        <div class="w-content">
                            <span class="w-value">{{ $todayStats['alerts'] }}</span>
                            <span class="w-numeric-title">Alerts Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h5 class="mb-4">📊 Report Options</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('reports.daily') }}" class="btn btn-outline-primary w-100 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <br>Daily Report
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('reports.monthly') }}" class="btn btn-outline-success w-100 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            <br>Monthly Report
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <form method="POST" action="{{ route('reports.export') }}" class="d-inline w-100">
                            @csrf
                            <input type="hidden" name="type" value="daily">
                            <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                            <button type="submit" class="btn btn-outline-secondary w-100 p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <br>Export Today's Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly Chart --}}
        <div class="col-xl-8 col-lg-8 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h5 class="mb-4">📈 Weekly Production Trend</h5>
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
        </div>

        {{-- Top Workers --}}
        <div class="col-xl-4 col-lg-4 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h5 class="mb-4">🏆 Top Workers This Month</h5>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        @foreach($topWorkers as $index => $worker)
                            <tr>
                                <td>
                                    @if($index == 0) 🥇
                                    @elseif($index == 1) 🥈
                                    @elseif($index == 2) 🥉
                                    @else {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>{{ $worker->name }}</td>
                                <td><span class="badge badge-primary">{{ $worker->monthly_production }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const weeklyData = @json($weeklyProduction);
        const dates = weeklyData.map(d => d.production_date);
        const totals = weeklyData.map(d => d.total);

        new Chart(document.getElementById('weeklyChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Garments',
                    data: totals,
                    backgroundColor: 'rgba(27, 85, 226, 0.2)',
                    borderColor: 'rgba(27, 85, 226, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
@endsection
