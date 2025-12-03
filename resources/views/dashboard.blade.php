@extends('layouts.layout')

@section('content')


    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">

            <!-- Statistics Cards -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-card-four">
                    <div class="widget-content">
                        <div class="w-header">
                            <div class="w-info">
                                <h6 class="value">{{ $todayProduction }}</h6>
                                <p class="">Today's Production</p>
                            </div>
                            <div class="task-action">
                                <div class="dropdown">
                                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                        <svg>...</svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="w-content">
                            <div class="w-info">
                                <p class="value">
                                    Target: {{ $targetVsActual['target'] }}
                                    <span class="badge badge-{{ $targetVsActual['percentage'] >= 100 ? 'success' : 'warning' }}">
                                    {{ $targetVsActual['percentage'] }}%
                                </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-card-four">
                    <div class="widget-content">
                        <div class="w-header">
                            <div class="w-info">
                                <h6 class="value">{{ $activeWorkers }}</h6>
                                <p class="">Active Workers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-card-four">
                    <div class="widget-content">
                        <div class="w-header">
                            <div class="w-info">
                                <h6 class="value">{{ $activeTables }}/40</h6>
                                <p class="">Active Tables</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-card-four">
                    <div class="widget-content">
                        <div class="w-header">
                            <div class="w-info">
                                <h6 class="value text-danger">{{ $recentAlerts->count() }}</h6>
                                <p class="">Active Alerts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hourly Production Chart -->
            <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5>Hourly Production - Today</h5>
                    </div>
                    <div class="widget-content">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Light Indicators Summary -->
            <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-two">
                    <div class="widget-heading">
                        <h5>Light Indicators - Today</h5>
                    </div>
                    <div class="widget-content">
                        <div class="light-summary">
                            <div class="light-item">
                                <span class="light-color" style="background: red;"></span>
                                <span class="light-label">Red (Issues)</span>
                                <span class="light-count">{{ $lightSummary['red'] ?? 0 }}</span>
                            </div>
                            <div class="light-item">
                                <span class="light-color" style="background: green;"></span>
                                <span class="light-label">Green (Good)</span>
                                <span class="light-count">{{ $lightSummary['green'] ?? 0 }}</span>
                            </div>
                            <div class="light-item">
                                <span class="light-color" style="background: blue;"></span>
                                <span class="light-label">Blue (Help)</span>
                                <span class="light-count">{{ $lightSummary['blue'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-table-two">
                    <div class="widget-heading">
                        <h5>Top Performers - Today</h5>
                    </div>
                    <div class="widget-content">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Worker ID</th>
                                    <th>Name</th>
                                    <th>Production</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($topPerformers as $performer)
                                    <tr>
                                        <td>{{ $performer->worker->worker_id }}</td>
                                        <td>{{ $performer->worker->name }}</td>
                                        <td><span class="badge badge-success">{{ $performer->total_production }}</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Alerts -->
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-table-two">
                    <div class="widget-heading">
                        <h5>Active Alerts (Red Lights)</h5>
                    </div>
                    <div class="widget-content">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Worker</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($recentAlerts as $alert)
                                    <tr>
                                        <td>{{ $alert->table->table_number }}</td>
                                        <td>{{ $alert->worker->name ?? 'N/A' }}</td>
                                        <td>{{ $alert->activated_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('tables.show', $alert->table_id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No active alerts</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Hourly Production Chart
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($hourlyProduction->pluck('hour')) !!},
                datasets: [{
                    label: 'Garments Produced',
                    data: {!! json_encode($hourlyProduction->pluck('total')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>

@endsection
