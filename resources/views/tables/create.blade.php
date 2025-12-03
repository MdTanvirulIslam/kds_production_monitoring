@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Tables</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add New Table</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h3 class="mb-4">➕ Add New Table</h3>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('tables.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Table Number <span class="text-danger">*</span></label>
                        <input type="text" name="table_number" class="form-control" placeholder="e.g., T001" value="{{ old('table_number') }}" required>
                        <small class="text-muted">Unique identifier for the table</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Table Name <span class="text-danger">*</span></label>
                        <input type="text" name="table_name" class="form-control" placeholder="e.g., Table 1" value="{{ old('table_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ESP32 IP Address</label>
                        <input type="text" name="esp32_ip" class="form-control" placeholder="e.g., 192.168.1.101" value="{{ old('esp32_ip') }}">
                        <small class="text-muted">IP address of the ESP32 device for this table</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ESP32 Device ID</label>
                        <input type="text" name="esp32_device_id" class="form-control" placeholder="e.g., ESP32_T001" value="{{ old('esp32_device_id') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('tables.index') }}" class="btn btn-light-dark">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Table</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
