{{-- resources/views/production-targets/bulk-create.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('production-targets.index') }}">Production Targets</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bulk Create</li>
@endsection

@section('styles')
    <style>
        .shift-checkbox { display: none; }
        .shift-label { border: 2px solid #e0e6ed; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; text-align: center; display: block; }
        .shift-label:hover { border-color: #667eea; background: #f8f9ff; }
        .shift-checkbox:checked + .shift-label { border-color: #667eea; background: linear-gradient(135deg, #667eea11 0%, #764ba211 100%); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }
        .shift-label .icon { font-size: 2.5rem; margin-bottom: 10px; }
        .shift-label .name { font-weight: 700; font-size: 1rem; }
        .shift-label .time { font-size: 0.8rem; color: #888; }
        .shift-label .checkmark { position: absolute; top: 10px; right: 10px; width: 24px; height: 24px; background: #667eea; border-radius: 50%; display: none; align-items: center; justify-content: center; color: #fff; }
        .shift-checkbox:checked + .shift-label .checkmark { display: flex; }
        .form-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .preview-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; color: #fff; }
        .preview-card .label { font-size: 0.8rem; opacity: 0.8; }
        .preview-card .value { font-size: 1.3rem; font-weight: 700; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers me-2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    Bulk Create Production Targets
                </h4>
                <a href="{{ route('production-targets.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back
                </a>
            </div>
        </div>

        <div class="col-xl-8 col-lg-8 layout-spacing">
            <div class="form-card">
                <form action="{{ route('production-targets.bulk-store') }}" method="POST" id="bulkForm">
                    @csrf

                    {{-- Date Range --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-lg @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', today()->format('Y-m-d')) }}" required id="startDate">
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-lg @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', today()->addDays(6)->format('Y-m-d')) }}" required id="endDate">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Skip Weekends --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="skip_weekends" value="1" class="form-check-input" id="skipWeekends" {{ old('skip_weekends') ? 'checked' : '' }}>
                            <label class="form-check-label" for="skipWeekends">
                                <strong>Skip Weekends</strong> (Don't create targets for Saturday & Sunday)
                            </label>
                        </div>
                    </div>

                    {{-- Shift Selection --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Shifts</label>
                        <p class="text-muted mb-3">Choose which shifts to create targets for:</p>
                        <div class="row g-3">
                            @foreach($shifts as $shift)
                                <div class="col-md-4">
                                    <input type="checkbox" name="shifts[]" value="{{ $shift->id }}" class="shift-checkbox"
                                           id="shift_{{ $shift->id }}" {{ in_array($shift->id, old('shifts', [])) ? 'checked' : '' }}>
                                    <label class="shift-label position-relative" for="shift_{{ $shift->id }}">
                                        <span class="checkmark">✓</span>
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
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('shifts')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Targets --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hourly Target (per shift)</label>
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
                            <label class="form-label fw-bold">Daily Target (per shift)</label>
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

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers me-1"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                            Create <span id="totalTargets">0</span> Targets
                        </button>
                        <a href="{{ route('production-targets.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Preview Sidebar --}}
        <div class="col-xl-4 col-lg-4 layout-spacing">
            <div class="preview-card mb-3">
                <div class="label">Date Range</div>
                <div class="value" id="previewDateRange">-</div>
            </div>
            <div class="preview-card mb-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="label">Total Days</div>
                <div class="value"><span id="previewDays">0</span> days</div>
            </div>
            <div class="preview-card mb-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="label">Shifts Selected</div>
                <div class="value"><span id="previewShifts">0</span> shifts</div>
            </div>
            <div class="preview-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="label">Total Targets to Create</div>
                <div class="value"><span id="previewTotal">0</span> targets</div>
            </div>

            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">💡 Bulk Create Tips</h6>
                <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
                    <li>Select a week range for easy planning</li>
                    <li>Skip weekends if your factory doesn't operate</li>
                    <li>Existing targets won't be overwritten</li>
                    <li>You can edit individual targets after creation</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function calculatePreview() {
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const skipWeekends = document.getElementById('skipWeekends').checked;
            const shiftsChecked = document.querySelectorAll('.shift-checkbox:checked').length;

            if (startDate && endDate && startDate <= endDate) {
                // Calculate days
                let days = 0;
                let current = new Date(startDate);
                while (current <= endDate) {
                    if (!skipWeekends || (current.getDay() !== 0 && current.getDay() !== 6)) {
                        days++;
                    }
                    current.setDate(current.getDate() + 1);
                }

                const total = days * shiftsChecked;

                // Update preview
                document.getElementById('previewDateRange').textContent =
                    startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' +
                    endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                document.getElementById('previewDays').textContent = days;
                document.getElementById('previewShifts').textContent = shiftsChecked;
                document.getElementById('previewTotal').textContent = total;
                document.getElementById('totalTargets').textContent = total;
            }
        }

        // Event listeners
        document.getElementById('startDate').addEventListener('change', calculatePreview);
        document.getElementById('endDate').addEventListener('change', calculatePreview);
        document.getElementById('skipWeekends').addEventListener('change', calculatePreview);
        document.querySelectorAll('.shift-checkbox').forEach(cb => {
            cb.addEventListener('change', calculatePreview);
        });

        // Auto-calculate daily from hourly
        document.getElementById('hourlyTarget').addEventListener('change', function() {
            const hourly = parseInt(this.value) || 0;
            document.getElementById('dailyTarget').value = hourly * 8;
        });

        // Initial calculation
        calculatePreview();
    </script>
@endsection
