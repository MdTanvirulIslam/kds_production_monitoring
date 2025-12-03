@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Live Monitor</li>
@endsection

@section('styles')
    <style>
        .table-card {
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #fff;
        }
        .table-card.light-red {
            border-color: #e7515a;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
            animation: pulse-red 1.5s infinite;
        }
        .table-card.light-green {
            border-color: #1abc9c;
            background: linear-gradient(135deg, #f0fff0 0%, #d4edda 100%);
        }
        .table-card.light-blue {
            border-color: #2196f3;
            background: linear-gradient(135deg, #f0f8ff 0%, #cce5ff 100%);
        }
        .table-card.light-off {
            border-color: #e0e6ed;
            background: #fff;
        }
        .table-number {
            font-size: 1.4rem;
            font-weight: bold;
            color: #3b3f5c;
        }
        .worker-name {
            font-size: 0.85rem;
            color: #888ea8;
            margin: 5px 0;
        }
        .production-count {
            font-size: 1.1rem;
            font-weight: bold;
            color: #1abc9c;
        }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(231, 81, 90, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(231, 81, 90, 0); }
            100% { box-shadow: 0 0 0 0 rgba(231, 81, 90, 0); }
        }
        .monitor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area">
                <div class="monitor-header">
                    <h3 class="mb-0">🖥️ Live Monitor</h3>
                    <div>
                        <span class="badge badge-light-secondary" id="last-update">Last update: {{ now()->format('H:i:s') }}</span>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="mb-4">
                    <span class="badge badge-danger me-2">🔴 Alert</span>
                    <span class="badge badge-success me-2">🟢 Good</span>
                    <span class="badge badge-info me-2">🔵 Help</span>
                    <span class="badge badge-secondary me-2">⚫ Normal</span>
                </div>
            </div>
        </div>

        {{-- Table Grid --}}
        @foreach($tables->chunk(5) as $row)
            @foreach($row as $table)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 layout-spacing">
                    <div class="table-card light-{{ $table->current_light_status }}" id="table-{{ $table->id }}">
                        <div class="table-number">{{ $table->table_number }}</div>
                        <div class="worker-name">
                            {{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}
                        </div>
                        <div class="production-count">
                            {{ $table->getTodayProduction() }} pcs
                        </div>
                        <div class="light-indicator mt-2">
                            @if($table->current_light_status === 'red')
                                <span class="badge badge-danger">🔴 Alert</span>
                            @elseif($table->current_light_status === 'green')
                                <span class="badge badge-success">🟢 Good</span>
                            @elseif($table->current_light_status === 'blue')
                                <span class="badge badge-info">🔵 Help</span>
                            @else
                                <span class="badge badge-secondary">⚫ Normal</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
@endsection

@section('scripts')
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);

        // Update timestamp
        setInterval(function() {
            document.getElementById('last-update').textContent = 'Last update: ' + new Date().toLocaleTimeString();
        }, 1000);
    </script>
@endsection
