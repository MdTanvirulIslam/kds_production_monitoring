{{-- resources/views/production-targets/create.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('production-targets.index') }}">Production Targets</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Target</li>
@endsection

@section('styles')
    <style>
        .shift-card { border: 2px solid #e0e6ed; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; text-align: center; height: 100%; }
        .shift-card:hover { border-color: #667eea; background: #f8f9ff; }
        .shift-card.active { border-color: #667eea; background: linear-gradient(135deg, #667eea11 0%, #764ba211 100%); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }
        .shift-card.disabled { opacity: 0.5; cursor: not-allowed; background: #f0f0f0; }
        .shift-card .icon { font-size: 2.5rem; margin-bottom: 10px; }
        .shift-card .name { font-weight: 700; font-size: 1rem; }
        .shift-card .time { font-size: 0.8rem; color: #888; }
        .shift-card .badge-exists { position: absolute; top: 10px; right: 10px; }
        .form-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .target-preview { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; color: #fff; }
        .target-preview .label { font-size: 0.8rem; opacity: 0.8; }
        .target-preview .value { font-size: 1.5rem; font-weight: 700; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Create Production Target
                </h4>
                <a href="{{ route('production-targets.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back
                </a>
            </div>
        </div>

        <div class="col-xl-8 col-lg-8 layout-spacing">
            <div class="form-card">
                <form action="{{ route('production-targets.store') }}" method="POST">
                    @csrf

                    {{-- Date --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Target Date</label>
                        <input type="date" name="target_date" class="form-control form-control-lg @error('target_date') is-invalid @enderror"
                               value="{{ old('target_date', $date) }}" required>
                        @error('target_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Shift Selection --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Shift</label>
                        <div class="row g-3">
                            @foreach($shifts as $shift)
                                @php
                                    $isDisabled = in_array($shift->id, $existingTargets);
                                @endphp
                                <div class="col-md-4">
                                    <div class="shift-card {{ $isDisabled ? 'disabled' : '' }} position-relative"
                                         data-shift-id="{{ $shift->id }}"
                                         @if(!$isDisabled) onclick="selectShift({{ $shift->id }})" @endif>
                                        @if($isDisabled)
                                            <span class="badge bg-warning badge-exists">Exists</span>
                                        @endif
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
                        <input type="hidden" name="shift_id" id="shift_id" value="{{ old('shift_id') }}">
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
                                       value="{{ old('hourly_target', 100) }}" min="1" required id="hourlyTarget">
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
                                       value="{{ old('daily_target', 800) }}" min="1" required id="dailyTarget">
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
                               value="{{ old('product_type') }}" placeholder="e.g., T-Shirts, Pants, Jackets">
                        @error('product_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Notes <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                  placeholder="Any additional notes about this target">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Create Target
                        </button>
                        <a href="{{ route('production-targets.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Preview Sidebar --}}
        <div class="col-xl-4 col-lg-4 layout-spacing">
            <div class="target-preview mb-3">
                <div class="label">Selected Shift</div>
                <div class="value" id="previewShift">Please select a shift</div>
            </div>
            <div class="target-preview mb-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="label">Hourly Target</div>
                <div class="value"><span id="previewHourly">100</span> pcs</div>
            </div>
            <div class="target-preview" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="label">Daily Target</div>
                <div class="value"><span id="previewDaily">800</span> pcs</div>
            </div>

            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">💡 Tips</h6>
                <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
                    <li>Set realistic targets based on historical data</li>
                    <li>Each shift can have different targets</li>
                    <li>Daily target = Hourly × Working hours</li>
                    <li>Use bulk create for weekly targets</li>
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

            // Update preview
            const card = document.querySelector(`.shift-card[data-shift-id="${shiftId}"]`);
            document.getElementById('previewShift').textContent = card.querySelector('.name').textContent;
        }

        // Update preview on input change
        document.getElementById('hourlyTarget').addEventListener('input', function() {
            document.getElementById('previewHourly').textContent = this.value || 0;
        });

        document.getElementById('dailyTarget').addEventListener('input', function() {
            document.getElementById('previewDaily').textContent = this.value || 0;
        });

        // Auto-calculate daily from hourly (8 hours per shift)
        document.getElementById('hourlyTarget').addEventListener('change', function() {
            const hourly = parseInt(this.value) || 0;
            document.getElementById('dailyTarget').value = hourly * 8;
            document.getElementById('previewDaily').textContent = hourly * 8;
        });
    </script>
@endsection
