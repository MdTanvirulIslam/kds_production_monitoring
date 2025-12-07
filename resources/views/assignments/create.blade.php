{{-- resources/views/assignments/create.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Assignment</li>
@endsection

@section('styles')
    <style>
        .shift-card { border: 2px solid #e0e6ed; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.2s; text-align: center; }
        .shift-card:hover { border-color: #1b55e2; background: #f8f9ff; }
        .shift-card.active { border-color: #1b55e2; background: #e8edff; box-shadow: 0 0 0 3px rgba(27, 85, 226, 0.2); }
        .shift-card .shift-icon { font-size: 24px; margin-bottom: 5px; }
        .shift-card .shift-name { font-weight: 600; font-size: 14px; }
        .shift-card .shift-time { font-size: 12px; color: #888; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-10 col-md-12 mx-auto">
            <div class="widget-content widget-content-area br-8">
                <h5 class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Create New Assignment
                </h5>

                <form action="{{ route('assignments.store') }}" method="POST" id="assignmentForm">
                    @csrf
                    <input type="hidden" name="assigned_date" value="{{ $date }}">
                    <input type="hidden" name="shift_id" id="shiftId" value="1">
                    <input type="hidden" name="shift_start" id="shiftStart" value="06:00">
                    <input type="hidden" name="shift_end" id="shiftEnd" value="14:00">

                    {{-- Date Display --}}
                    <div class="mb-4">
                        <label class="form-label">Assignment Date</label>
                        <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </span>
                            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}" readonly>
                        </div>
                    </div>

                    {{-- Shift Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Select Shift</label>
                        <div class="row" id="shiftSelector">
                            @forelse($shifts ?? [] as $shift)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="shift-card {{ $loop->first ? 'active' : '' }}"
                                         data-shift-id="{{ $shift->id }}"
                                         data-shift-start="{{ $shift->start_time }}"
                                         data-shift-end="{{ $shift->end_time }}">
                                        <div class="shift-icon">
                                            @if(strtotime($shift->start_time) < strtotime('12:00'))
                                                🌅
                                            @elseif(strtotime($shift->start_time) < strtotime('18:00'))
                                                ☀️
                                            @else
                                                🌙
                                            @endif
                                        </div>
                                        <div class="shift-name">{{ $shift->name }}</div>
                                        <div class="shift-time">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="shift-card active" data-shift-id="1" data-shift-start="06:00" data-shift-end="14:00">
                                        <div class="shift-icon">🌅</div>
                                        <div class="shift-name">Morning Shift</div>
                                        <div class="shift-time">06:00 AM - 02:00 PM</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="shift-card" data-shift-id="2" data-shift-start="14:00" data-shift-end="22:00">
                                        <div class="shift-icon">☀️</div>
                                        <div class="shift-name">Day Shift</div>
                                        <div class="shift-time">02:00 PM - 10:00 PM</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="shift-card" data-shift-id="3" data-shift-start="22:00" data-shift-end="06:00">
                                        <div class="shift-icon">🌙</div>
                                        <div class="shift-name">Night Shift</div>
                                        <div class="shift-time">10:00 PM - 06:00 AM</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Table Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Select Table <span class="text-danger">*</span></label>
                        <select name="table_id" class="form-select @error('table_id') is-invalid @enderror" required>
                            <option value="">-- Choose Table --</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                    {{ $table->table_number }} - {{ $table->table_name ?? 'Workstation' }}
                                </option>
                            @endforeach
                        </select>
                        @error('table_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($tables->isEmpty())
                            <small class="text-warning">No unassigned tables available for this date.</small>
                        @endif
                    </div>

                    {{-- Worker Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Select Worker <span class="text-danger">*</span></label>
                        <select name="worker_id" class="form-select @error('worker_id') is-invalid @enderror" required>
                            <option value="">-- Choose Worker --</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('worker_id') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->worker_id }} - {{ $worker->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('worker_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($workers->isEmpty())
                            <small class="text-warning">No unassigned workers available for this date.</small>
                        @endif
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any special notes...">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('assignments.index', ['date' => $date]) }}" class="btn btn-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Back
                        </a>
                        <button type="submit" class="btn btn-primary" {{ $tables->isEmpty() || $workers->isEmpty() ? 'disabled' : '' }}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Create Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Shift Card Selection
        document.querySelectorAll('.shift-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.shift-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                document.getElementById('shiftId').value = this.dataset.shiftId;
                document.getElementById('shiftStart').value = this.dataset.shiftStart;
                document.getElementById('shiftEnd').value = this.dataset.shiftEnd;
            });
        });
    </script>
@endsection
