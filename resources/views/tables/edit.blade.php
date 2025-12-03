@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Tables</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tables.show', $table) }}">{{ $table->table_number }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h3 class="mb-4">✏️ Edit Table: {{ $table->table_number }}</h3>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('tables.update', $table) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Table Number <span class="text-danger">*</span></label>
                        <input type="text" name="table_number" class="form-control" value="{{ old('table_number', $table->table_number) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Table Name <span class="text-danger">*</span></label>
                        <input type="text" name="table_name" class="form-control" value="{{ old('table_name', $table->table_name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ESP32 IP Address</label>
                        <input type="text" name="esp32_ip" class="form-control" value="{{ old('esp32_ip', $table->esp32_ip) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ESP32 Device ID</label>
                        <input type="text" name="esp32_device_id" class="form-control" value="{{ old('esp32_device_id', $table->esp32_device_id) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $table->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $table->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('tables.show', $table) }}" class="btn btn-light-dark">Cancel</a>
                        <div>
                            <form method="POST" action="{{ route('tables.destroy', $table) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this table?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger me-2">Delete</button>
                            </form>
                            <button type="submit" class="btn btn-primary">Update Table</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
