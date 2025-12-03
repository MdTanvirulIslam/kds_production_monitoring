@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Workers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $worker->name }}</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Worker Profile Card --}}
        <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="text-center mb-4">
                    <img src="{{ $worker->photo_url }}"
                         alt="{{ $worker->name }}"
                         class="rounded-circle mb-3"
                         width="120" height="120"
                         style="object-fit: cover; border: 3px solid #e0e6ed;">
                    <h4>{{ $worker->name }}</h4>
                    <p class="text-muted">{{ $worker->worker_id }}</p>
                    @php
                        $skillBadge = match($worker->skill_level) {
                            'expert' => 'badge-success',
                            'intermediate' => 'badge-warning',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <span class="badge {{ $skillBadge }}">{{ ucfirst($worker->skill_level) }}</span>
                </div>

                <div class="info-list">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phone:</span>
                        <span>{{ $worker->phone ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Email:</span>
                        <span>{{ $worker->email ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Date of Birth:</span>
                        <span>{{ $worker->date_of_birth?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Joining Date:</span>
                        <span>{{ $worker->joining_date->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status:</span>
                        @if($worker->is_active)
                            <span class="badge badge-light-success">Active</span>
                        @else
                            <span class="badge badge-light-danger">Inactive</span>
                        @endif
                    </div>
                </div>

                <hr>

                {{-- Current Assignment --}}
                <h6>Current Assignment</h6>
                @if($worker->currentAssignment)
                    <div class="alert alert-light-primary">
                        <strong>Table:</strong> {{ $worker->currentAssignment->table->table_number }}<br>
                        <strong>Shift:</strong> {{ $worker->currentAssignment->shift_start }} - {{ $worker->currentAssignment->shift_end }}
                    </div>
                @else
                    <p class="text-muted">Not assigned to any table today</p>
                @endif

                <hr>

                {{-- Production Summary --}}
                <h6>Production Summary</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $todayProduction }}</h4>
                        <small class="text-muted">Today</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $weeklyProduction }}</h4>
                        <small class="text-muted">This Week</small>
                    </div>
                </div>

                <hr>

                @if(auth()->user()->role === 'admin')
                    <div class="d-grid gap-2">
                        <a href="{{ route('workers.edit', $worker) }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit me-1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit Worker
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Production Data --}}
        <div class="col-xl-8 col-lg-7 col-md-12 col-sm-12 layout-spacing">
            {{-- Production Chart --}}
            <div class="widget-content widget-content-area br-8 mb-4">
                <h5>📊 Production History (Last 7 Days)</h5>
                <canvas id="productionChart" height="200"></canvas>
            </div>

            {{-- Recent Production Logs --}}
            <div class="widget-content widget-content-area br-8">
                <h5>📋 Recent Production Logs</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Table</th>
                            <th>Garments</th>
                            <th>Supervisor</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($worker->productionLogs as $log)
                            <tr>
                                <td>{{ $log->production_date->format('d M') }}</td>
                                <td>{{ $log->production_hour }}</td>
                                <td>{{ $log->table->table_number }}</td>
                                <td><span class="badge badge-success">{{ $log->garments_count }}</span></td>
                                <td>{{ $log->supervisor->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No production logs found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Production History Chart
        const historyData = @json($productionHistory);
        const dates = historyData.map(d => d.production_date);
        const totals = historyData.map(d => d.total);

        new Chart(document.getElementById('productionChart'), {
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
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
