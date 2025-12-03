@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Tables</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $table->table_number }}</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Table Info Card --}}
        <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="text-center mb-4">
                    {{-- QR Code Display (SVG format - no imagick needed) --}}
                    <div class="qr-code-container mb-3" style="background: #fff; padding: 20px; border-radius: 10px; display: inline-block; border: 2px solid #e0e6ed;">
                        {!! QrCode::format('svg')->size(200)->errorCorrection('H')->generate($table->qr_code) !!}
                    </div>
                    <h4>{{ $table->table_number }}</h4>
                    <p class="text-muted">{{ $table->table_name }}</p>

                    {{-- QR Code Value (for reference) --}}
                    <small class="text-muted d-block mb-2">
                        <code>{{ $table->qr_code }}</code>
                    </small>
                </div>

                <div class="info-list">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status:</span>
                        @if($table->is_active)
                            <span class="badge badge-light-success">Active</span>
                        @else
                            <span class="badge badge-light-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Light Status:</span>
                        @if($table->current_light_status === 'red')
                            <span class="badge badge-danger">🔴 Red - Alert</span>
                        @elseif($table->current_light_status === 'green')
                            <span class="badge badge-success">🟢 Green - Good</span>
                        @elseif($table->current_light_status === 'blue')
                            <span class="badge badge-info">🔵 Blue - Help</span>
                        @else
                            <span class="badge badge-secondary">⚫ Off - Normal</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ESP32 IP:</span>
                        <span>{{ $table->esp32_ip ?? 'Not configured' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Device ID:</span>
                        <span>{{ $table->esp32_device_id ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Today's Production:</span>
                        <span class="badge badge-primary">{{ $todayProduction }} pcs</span>
                    </div>
                </div>

                <hr>

                {{-- Current Worker --}}
                <h6>👤 Current Worker</h6>
                @if($table->currentAssignment?->worker)
                    <div class="d-flex align-items-center p-2" style="background: #f8f9fa; border-radius: 8px;">
                        <div class="me-3">
                            <img src="{{ $table->currentAssignment->worker->photo_url ?? asset('assets/src/assets/img/profile-30.png') }}"
                                 alt="{{ $table->currentAssignment->worker->name }}"
                                 class="rounded-circle"
                                 width="50" height="50"
                                 style="object-fit: cover;">
                        </div>
                        <div>
                            <strong>{{ $table->currentAssignment->worker->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $table->currentAssignment->worker->worker_id }}</small>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light-warning">
                        <small>No worker assigned today</small>
                    </div>
                @endif

                <hr>

                {{-- Actions --}}
                <div class="d-grid gap-2">
                    <a href="{{ route('tables.qr-download', $table) }}" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download QR Code (SVG)
                    </a>

                    <button onclick="window.print()" class="btn btn-outline-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer me-1"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Print QR Code
                    </button>

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('tables.edit', $table) }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit me-1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit Table
                        </a>

                        <form method="POST" action="{{ route('tables.regenerate-qr', $table) }}" onsubmit="return confirm('Regenerate QR code? The old QR will stop working.');">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw me-1"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                Regenerate QR Code
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Production Data --}}
        <div class="col-xl-8 col-lg-7 col-md-12 col-sm-12 layout-spacing">
            {{-- Hourly Production Chart --}}
            <div class="widget-content widget-content-area br-8 mb-4">
                <h5>📊 Hourly Production Today</h5>
                <canvas id="hourlyChart" height="200"></canvas>
            </div>

            {{-- Recent Production Logs --}}
            <div class="widget-content widget-content-area br-8">
                <h5>📋 Recent Production Logs</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Time</th>
                            <th>Worker</th>
                            <th>Garments</th>
                            <th>Product</th>
                            <th>Supervisor</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($table->productionLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('H:i') }}</td>
                                <td>{{ $log->worker->name ?? '-' }}</td>
                                <td><span class="badge badge-success">{{ $log->garments_count }}</span></td>
                                <td>{{ $log->product_type ?? '-' }}</td>
                                <td>{{ $log->supervisor->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No production logs today</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            .sidebar-wrapper, .header-container, .footer-wrapper, .breadcrumb-style-one,
            .btn, button, form, .widget-content:not(:first-child) {
                display: none !important;
            }
            .qr-code-container {
                border: 3px solid #000 !important;
                padding: 30px !important;
            }
            .col-xl-4 {
                width: 100% !important;
                max-width: 400px !important;
                margin: 0 auto !important;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Hourly Production Chart
        const hourlyData = @json($hourlyData);
        const hours = hourlyData.map(d => d.production_hour);
        const totals = hourlyData.map(d => d.total);

        new Chart(document.getElementById('hourlyChart'), {
            type: 'bar',
            data: {
                labels: hours.length ? hours : ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                datasets: [{
                    label: 'Garments',
                    data: totals.length ? totals : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(27, 85, 226, 0.7)',
                    borderColor: 'rgba(27, 85, 226, 1)',
                    borderWidth: 1
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
