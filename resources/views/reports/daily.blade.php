@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Daily Report</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between mb-4">
                    <h3 class="mb-0">📅 Daily Report</h3>
                    <form method="POST" action="{{ route('reports.export') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="type" value="daily">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="btn btn-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Export CSV
                        </button>
                    </form>
                </div>

                {{-- Date Selector --}}
                <form method="GET" action="{{ route('reports.daily') }}" class="mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" value="{{ $date }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary">View Report</button>
                        </div>
                        <div class="col-md-7 text-end">
                            <h4 class="mb-0">Total: <span class="badge badge-primary">{{ number_format($totalProduction) }} garments</span></h4>
                        </div>
                    </div>
                </form>

                {{-- Hourly Breakdown Chart --}}
                <div class="mb-4">
                    <h5>Hourly Production Breakdown</h5>
                    <canvas id="hourlyChart" height="150"></canvas>
                </div>

                {{-- Worker-wise Production --}}
                <h5>Worker-wise Production</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Worker</th>
                            <th>Production</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($workerProduction as $index => $worker)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $worker->name }}</td>
                                <td><span class="badge badge-success">{{ $worker->daily_production }}</span></td>
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
        const hourlyData = @json($hourlyProduction);
        const hours = hourlyData.map(d => d.production_hour);
        const totals = hourlyData.map(d => d.total);

        new Chart(document.getElementById('hourlyChart'), {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Garments',
                    data: totals,
                    backgroundColor: 'rgba(27, 85, 226, 0.7)',
                    borderColor: 'rgba(27, 85, 226, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
@endsection
