{{-- resources/views/assignments/edit.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Assignment</li>
@endsection

@section('styles')
    <style>
        .shift-card { border: 2px solid #e0e6ed; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.2s; text-align: center; }
        .shift-card:hover { border-color: #1b55e2; background: #f8f9ff; }
        .shift-card.active { border-color: #1b55e2; background: #e8edff; box-shadow: 0 0 0 3px rgba(27, 85, 226, 0.2); }
        .shift-card .shift-icon { font-size: 24px; margin-bottom: 5px; }
        .shift-card .shift-name { font-weight: 600; font-size: 14px; }
        .shift-card .shift-time { font-size: 12px; color: #888; }
        .current-info { background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .current-info .label { font-size: 12px; color: #888; text-transform: uppercase; }
        .current-info .value { font-weight: 600; font-size: 16px; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-10 col-md-12 mx-auto">
            <div class="widget-content widget-content-area br-8">
                <h5 class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit me-2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Edit Assignment
                </h5>

                {{-- Current Assignment Info --}}
                <div class="current-info">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="label">Current Table</div>
                            <div class="value">{{ $assignment->table->table_number }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="label">Current Worker</div>
                            <div class="value">{{ $assignment->worker->name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="label">Current Shift</div>
                            <div class="value">
                                @if($assignment->shift_start && $assignment->shift_end)
                                    {{ \Carbon\Carbon::parse($assignment->shift_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($assignment->shift_end)->format('h:i A') }}
                                @else
                                    Not Set
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('assignments.update', $assignment->id) }}" method="POST" id="assignmentForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="shift_id" id="shiftId" value="{{ $assignment->shift_id ?? 1 }}">
                    <input type="hidden" name="shift_start" id="shiftStart" value="{{ $assignment->shift_start ?? '06:00' }}">
                    <input type="hidden" name="shift_end" id="shiftEnd" value="{{ $assignment->shift_end ?? '14:00' }}">

                    {{-- Date Display --}}
                    <div class="mb-4">
                        <label class="form-label">Assignment Date</label>
                        <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </span>
                            <input type="text" class="form-control" value="{{ $assignment->assigned_date->format('l, F d, Y') }}" readonly>
                        </div>
                    </div>

                    {{-- Shift Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Select Shift</label>
                        <div class="row" id="shiftSelector">
                            @forelse($shifts ?? [] as $shift)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="shift-card {{ ($assignment->shift_id == $shift->id) || ($loop->first && !$assignment->shift_id) ? 'active' : '' }}"
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
                                @php
                                    $defaultShifts = [
                                        ['id' => 1, 'name' => 'Morning Shift', 'start' => '06:00', 'end' => '14:00', 'icon' => '🌅'],
                                        ['id' => 2, 'name' => 'Day Shift', 'start' => '14:00', 'end' => '22:00', 'icon' => '☀️'],
                                        ['id' => 3, 'name' => 'Night Shift', 'start' => '22:00', 'end' => '06:00', 'icon' => '🌙'],
                                    ];
                                @endphp
                                @foreach($defaultShifts as $index => $shift)
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <div class="shift-card {{ ($assignment->shift_id == $shift['id']) || ($index == 0 && !$assignment->shift_id) ? 'active' : '' }}"
                                             data-shift-id="{{ $shift['id'] }}"
                                             data-shift-start="{{ $shift['start'] }}"
                                             data-shift-end="{{ $shift['end'] }}">
                                            <div class="shift-icon">{{ $shift['icon'] }}</div>
                                            <div class="shift-name">{{ $shift['name'] }}</div>
                                            <div class="shift-time">{{ \Carbon\Carbon::parse($shift['start'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift['end'])->format('h:i A') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforelse
                        </div>
                    </div>

                    {{-- Table Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Select Table <span class="text-danger">*</span></label>
                        <select name="table_id" class="form-select @error('table_id') is-invalid @enderror" required>
                            <option value="{{ $assignment->table_id }}" selected>
                                {{ $assignment->table->table_number }} - {{ $assignment->table->table_name ?? 'Workstation' }} (Current)
                            </option>
                            @foreach($tables as $table)
                                @if($table->id != $assignment->table_id)
                                    <option value="{{ $table->id }}">
                                        {{ $table->table_number }} - {{ $table->table_name ?? 'Workstation' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('table_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Worker Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Select Worker <span class="text-danger">*</span></label>
                        <select name="worker_id" class="form-select @error('worker_id') is-invalid @enderror" required>
                            <option value="{{ $assignment->worker_id }}" selected>
                                {{ $assignment->worker->worker_id }} - {{ $assignment->worker->name }} (Current)
                            </option>
                            @foreach($workers as $worker)
                                @if($worker->id != $assignment->worker_id)
                                    <option value="{{ $worker->id }}">
                                        {{ $worker->worker_id }} - {{ $worker->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('worker_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $assignment->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ $assignment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $assignment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any special notes...">{{ old('notes', $assignment->notes) }}</textarea>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('assignments.index', ['date' => $date]) }}" class="btn btn-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Back
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-danger me-2" onclick="deleteAssignment()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 me-1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Delete
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                Update Assignment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Form --}}
    <form id="deleteForm" action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
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

        // Delete Assignment
        function deleteAssignment() {
            Swal.fire({
                title: 'Delete Assignment?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e7515a',
                confirmButtonText: 'Yes, delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
@endsection
