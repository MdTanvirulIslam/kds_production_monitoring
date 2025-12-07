{{-- resources/views/production-targets/index.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Production Targets</li>
@endsection

@section('styles')
    <style>
        .target-card { 
            background: #fff; 
            border-radius: 12px; 
            padding: 20px; 
            box-shadow: 0 3px 15px rgba(0,0,0,0.08); 
            margin-bottom: 15px; 
        }
        .target-card .date-header { 
            font-size: 1.1rem; 
            font-weight: 700; 
            margin-bottom: 15px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #f0f0f0; 
        }
        .target-card .date-header .day { 
            color: #667eea; 
        }
        .shift-row { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }
        .shift-row:last-child { 
            margin-bottom: 0; 
        }
        .shift-row:hover {
            background: #f0f2f5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .shift-row .shift-icon { 
            font-size: 1.5rem; 
            margin-right: 15px;
            width: 40px;
            text-align: center;
        }
        .shift-row .shift-info { 
            flex-grow: 1; 
        }
        .shift-row .shift-name { 
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
        }
        .shift-row .shift-time { 
            font-size: 0.8rem; 
            color: #888; 
        }
        .shift-row .targets { 
            display: flex; 
            gap: 25px;
            margin-right: 20px;
        }
        .shift-row .target-item { 
            text-align: center;
            min-width: 70px;
        }
        .shift-row .target-item .value { 
            font-size: 1.1rem; 
            font-weight: 700; 
            color: #667eea; 
        }
        .shift-row .target-item .label { 
            font-size: 0.65rem; 
            color: #888; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Improved Action Buttons */
        .shift-row .actions { 
            display: flex;
            gap: 6px;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid;
            background: #fff;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-action svg {
            width: 14px;
            height: 14px;
        }
        .btn-action.btn-edit {
            border-color: #e0e6ed;
            color: #667eea;
        }
        .btn-action.btn-edit:hover {
            background: #667eea;
            border-color: #667eea;
            color: #fff;
        }
        .btn-action.btn-delete {
            border-color: #e0e6ed;
            color: #e74c3c;
        }
        .btn-action.btn-delete:hover {
            background: #e74c3c;
            border-color: #e74c3c;
            color: #fff;
        }
        
        .stat-box { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            border-radius: 12px; 
            padding: 20px; 
            color: #fff; 
            text-align: center;
            transition: transform 0.2s ease;
        }
        .stat-box:hover {
            transform: translateY(-2px);
        }
        .stat-box .value { 
            font-size: 2rem; 
            font-weight: 700; 
        }
        .stat-box .label { 
            font-size: 0.8rem; 
            opacity: 0.9; 
        }
        .no-target { 
            background: #fff; 
            border: 2px dashed #e0e6ed; 
            border-radius: 12px; 
            padding: 40px 20px; 
            text-align: center; 
            color: #888; 
        }
        .no-target svg {
            color: #ccc;
            margin-bottom: 15px;
        }
        .no-target h5 {
            color: #555;
            margin-bottom: 10px;
        }
        .product-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            background: #e8edff;
            color: #667eea;
            margin-left: 8px;
        }
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-target me-2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                            Production Targets
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('production-targets.index') }}" method="GET" class="d-flex gap-2">
                            <input type="month" name="month" class="form-control" value="{{ $month }}">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('production-targets.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Add Target
                        </a>
                        <a href="{{ route('production-targets.bulk-create') }}" class="btn btn-outline-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers me-1"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                            Bulk Create
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        @php
            $totalTargets = $targets->flatten()->count();
            $totalDaily = $targets->flatten()->sum('daily_target');
            $avgDaily = $totalTargets > 0 ? round($totalDaily / $targets->count()) : 0;
        @endphp
        <div class="col-xl-4 col-lg-4 col-md-4 layout-spacing">
            <div class="stat-box">
                <div class="value">{{ $targets->count() }}</div>
                <div class="label">Days with Targets</div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-4 layout-spacing">
            <div class="stat-box" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="value">{{ $totalTargets }}</div>
                <div class="label">Total Shift Targets</div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-4 layout-spacing">
            <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="value">{{ number_format($avgDaily) }}</div>
                <div class="label">Avg Daily Target</div>
            </div>
        </div>

        {{-- Quick Copy Form --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy me-2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    Quick Copy Targets
                </h6>
                <form action="{{ route('production-targets.copy') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Copy From</label>
                        <input type="date" name="source_date" class="form-control" value="{{ today()->subDay()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Copy To</label>
                        <input type="date" name="target_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy me-1"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy Targets
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Targets List --}}
        <div class="col-12 layout-spacing">
            @forelse($targets as $date => $dayTargets)
                <div class="target-card">
                    <div class="date-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="day">{{ \Carbon\Carbon::parse($date)->format('l') }}</span>
                            - {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                            @if(\Carbon\Carbon::parse($date)->isToday())
                                <span class="badge bg-primary ms-2">Today</span>
                            @endif
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                Total: {{ number_format($dayTargets->sum('daily_target')) }} pcs/day
                            </span>
                        </div>
                    </div>

                    @foreach($dayTargets as $target)
                        <div class="shift-row">
                            <div class="shift-icon">
                                @if($target->shift)
                                    @if(strtotime($target->shift->start_time) < strtotime('12:00'))
                                        🌅
                                    @elseif(strtotime($target->shift->start_time) < strtotime('18:00'))
                                        ☀️
                                    @else
                                        🌙
                                    @endif
                                @else
                                    📊
                                @endif
                            </div>
                            <div class="shift-info">
                                <div class="shift-name">{{ $target->shift->name ?? 'All Shifts' }}</div>
                                <div class="shift-time">
                                    @if($target->shift)
                                        {{ \Carbon\Carbon::parse($target->shift->start_time)->format('h:i A') }} -
                                        {{ \Carbon\Carbon::parse($target->shift->end_time)->format('h:i A') }}
                                    @else
                                        Full Day
                                    @endif
                                    @if($target->product_type)
                                        <span class="product-badge">{{ $target->product_type }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="targets">
                                <div class="target-item">
                                    <div class="value">{{ number_format($target->hourly_target) }}</div>
                                    <div class="label">Per Hour</div>
                                </div>
                                <div class="target-item">
                                    <div class="value">{{ number_format($target->daily_target) }}</div>
                                    <div class="label">Per Day</div>
                                </div>
                            </div>
                            <div class="actions">
                                <a href="{{ route('production-targets.edit', $target) }}" class="btn-action btn-edit" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </a>
                                <form action="{{ route('production-targets.destroy', $target) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this target?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="no-target">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-target"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                    <h5>No Targets Found</h5>
                    <p class="mb-3">No production targets set for {{ \Carbon\Carbon::parse($month)->format('F Y') }}</p>
                    <a href="{{ route('production-targets.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add First Target
                    </a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
