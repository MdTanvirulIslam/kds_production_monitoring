{{-- resources/views/production-targets/edit.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('production-targets.index') }}">Production Targets</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Target</li>
@endsection

@section('styles')
    <style>
        .shift-card { border: 2px solid #e0e6ed; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; text-align: center; height: 100%; }
        .shift-card:hover { border-color: #667eea; background: #f8f9ff; }
        .shift-card.active { border-color: #667eea; background: linear-gradient(135deg, #667eea11 0%, #764ba211 100%); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }
        .shift-card .icon { font-size: 2.5rem; margin-bottom: 10px; }
        .shift-card .name { font-weight: 700; font-size: 1rem; }
        .shift-card .time { font-size: 0.8rem; color: #888; }
        .form-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .current-info { background: #f8f9fa; border-radius: 10px; padding: 15px; margin-bottom: 20px; }
        .current-info .label { font-size: 0.75rem; color: #888; text-transform: uppercase; }
        .current-info .value { font-weight: 600; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit me-2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Edit Production Target
                </h4>
                <a href="{{ route('production-targets.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back
                </a>
            </div>
        </div>

        <div class="col-xl-8 col-lg-8 layout-spacing">
            <div class="form-card">
                {{-- Current Info --}}
                <div class="current-info">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="label">Current Date</div>
                            <div class="value">{{ $productionTarget->target_date->format('M d, Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="label">Current Shift</div>
                            <div class="value">{{ $productionTarget->shift->name ?? 'All Shifts' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="label">Current Target</div>
                            <div class="value">{{ number_format($productionTarget->daily_target) }} pcs/day</div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('production-targets.update', $productionTarget) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Date --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Target Date</label>
                        <input type="date" name="target_date" class="form-control form-control-lg @error('target_date') is-invalid @enderror"
                               value="{{ old('target_date', $productionTarget->target_date->format('Y-m-d')) }}" required>
                        @error('target_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Shift Selection --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Shift</label>
                        <div class="row g-3">
                            @foreach($shifts as $shift)
                                <div class="col-md-4">
                                    <div class="shift-card {{ $productionTarget->shift_id == $shift->id ? 'active' : '' }}"
                                         data-shift-id="{{ $shift->id }}"
                                         onclick="selectShift({{ $shift->id }})">
                                        <div class="icon">
                                            @if(strtotime($shift->start_time) < strtotime('12:00'))
                                                🌅
                                            @elseif(strtotime($shift->start_time) < strtotime('18:00'))
                                                ☀️
                                            @else
                                                🌙
                                            @endif
                                        </div>
                                        <div class="name">{{ $shift->name }}</div>
                                        <div class="time">
                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} -
                                            {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="shift_id" id="shift_id" value="{{ old('shift_id', $productionTarget->shift_id) }}">
                        @error('shift_id')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Targets --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hourly Target</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="hourly_target" class="form-control @error('hourly_target') is-invalid @enderror"
                                       value="{{ old('hourly_target', $productionTarget->hourly_target) }}" min="1" required>
                                <span class="input-group-text">pcs/hour</span>
                            </div>
                            @error('hourly_target')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Daily Target</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="daily_target" class="form-control @error('daily_target') is-invalid @enderror"
                                       value="{{ old('daily_target', $productionTarget->daily_target) }}" min="1" required>
                                <span class="input-group-text">pcs/day</span>
                            </div>
                            @error('daily_target')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Product Type --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Product Type <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="text" name="product_type" class="form-control @error('product_type') is-invalid @enderror"
                               value="{{ old('product_type', $productionTarget->product_type) }}" placeholder="e.g., T-Shirts, Pants, Jackets">
                        @error('product_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Notes <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                  placeholder="Any additional notes about this target">{{ old('notes', $productionTarget->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                Update Target
                            </button>
                            <a href="{{ route('production-targets.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                        <form action="{{ route('production-targets.destroy', $productionTarget) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this target?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 me-1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-xl-4 col-lg-4 layout-spacing">
            <div class="widget-content widget-content-area br-8 mb-3">
                <h6 class="mb-3">📊 Target Info</h6>
                <div class="mb-2">
                    <span class="text-muted">Created:</span>
                    <strong>{{ $productionTarget->created_at->format('M d, Y H:i') }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Last Updated:</span>
                    <strong>{{ $productionTarget->updated_at->format('M d, Y H:i') }}</strong>
                </div>
            </div>

            <div class="widget-content widget-content-area br-8">
                <h6 class="mb-3">💡 Tips</h6>
                <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
                    <li>Adjust targets based on worker availability</li>
                    <li>Consider product complexity when setting targets</li>
                    <li>Review historical data for realistic goals</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function selectShift(shiftId) {
            // Remove active from all
            document.querySelectorAll('.shift-card').forEach(card => {
                card.classList.remove('active');
            });

            // Add active to selected
            document.querySelector(`.shift-card[data-shift-id="${shiftId}"]`).classList.add('active');

            // Set hidden input
            document.getElementById('shift_id').value = shiftId;
        }
    </script>
@endsection
