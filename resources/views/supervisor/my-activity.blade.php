@extends('layouts.layout')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="#">Supervisor</a></li>
<li class="breadcrumb-item active" aria-current="page">My Activity</li>
@endsection

@section('content')
<div class="row layout-top-spacing">
    {{-- Summary Cards --}}
    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 layout-spacing">
        <div class="widget widget-card-one">
            <div class="widget-content">
                <div class="w-numeric-value">
                    <div class="w-icon" style="background: #1abc9c;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="w-content">
                        <span class="w-value">{{ number_format($totalGarments) }}</span>
                        <span class="w-numeric-title">Total Garments Logged</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 layout-spacing">
        <div class="widget widget-card-one">
            <div class="widget-content">
                <div class="w-numeric-value">
                    <div class="w-icon" style="background: #2196f3;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div class="w-content">
                        <span class="w-value">{{ $productionLogs->count() }}</span>
                        <span class="w-numeric-title">Production Entries</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 layout-spacing">
        <div class="widget widget-card-one">
            <div class="widget-content">
                <div class="w-numeric-value">
                    <div class="w-icon" style="background: #e7515a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-sun"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    </div>
                    <div class="w-content">
                        <span class="w-value">{{ $lightIndicators->count() }}</span>
                        <span class="w-numeric-title">Light Indicators Set</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Production Logs Table --}}
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <h5 class="mb-4">📋 My Production Logs Today</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Table</th>
                            <th>Worker</th>
                            <th>Garments</th>
                            <th>Product Type</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productionLogs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('H:i') }}</td>
                            <td><span class="badge badge-light-info">{{ $log->table->table_number ?? '-' }}</span></td>
                            <td>{{ $log->worker->name ?? '-' }}</td>
                            <td><span class="badge badge-success">{{ $log->garments_count }}</span></td>
                            <td>{{ $log->product_type ?? '-' }}</td>
                            <td>{{ $log->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No production logged today</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Light Indicators Table --}}
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <h5 class="mb-4">💡 Light Indicators Set Today</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Table</th>
                            <th>Worker</th>
                            <th>Color</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lightIndicators as $indicator)
                        <tr>
                            <td>{{ $indicator->activated_at->format('H:i') }}</td>
                            <td><span class="badge badge-light-info">{{ $indicator->table->table_number ?? '-' }}</span></td>
                            <td>{{ $indicator->worker->name ?? '-' }}</td>
                            <td>
                                @if($indicator->light_color === 'red')
                                    <span class="badge badge-danger">🔴 Red</span>
                                @elseif($indicator->light_color === 'green')
                                    <span class="badge badge-success">🟢 Green</span>
                                @elseif($indicator->light_color === 'blue')
                                    <span class="badge badge-info">🔵 Blue</span>
                                @else
                                    <span class="badge badge-secondary">⚫ Off</span>
                                @endif
                            </td>
                            <td>{{ $indicator->reason ?? '-' }}</td>
                            <td>
                                @if($indicator->is_active)
                                    <span class="badge badge-light-success">Active</span>
                                @else
                                    <span class="badge badge-light-secondary">Deactivated</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No light indicators set today</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
