@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Assignment</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h3 class="mb-4">➕ Create Assignment</h3>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('assignments.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_date" class="form-control" value="{{ old('assigned_date', $date) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Table <span class="text-danger">*</span></label>
                        <select name="table_id" class="form-select" required>
                            <option value="">Select Table...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">{{ $table->table_number }} - {{ $table->table_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ $tables->count() }} tables available</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Worker <span class="text-danger">*</span></label>
                        <select name="worker_id" class="form-select" required>
                            <option value="">Select Worker...</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->worker_id }} - {{ $worker->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ $workers->count() }} workers available</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shift Start</label>
                            <input type="time" name="shift_start" class="form-control" value="{{ old('shift_start', '08:00') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shift End</label>
                            <input type="time" name="shift_end" class="form-control" value="{{ old('shift_end', '17:00') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('assignments.index', ['date' => $date]) }}" class="btn btn-light-dark">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
