@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Workers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('workers.show', $worker) }}">{{ $worker->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h3 class="mb-4">✏️ Edit Worker: {{ $worker->name }}</h3>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('workers.update', $worker) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Worker ID <span class="text-danger">*</span></label>
                            <input type="text" name="worker_id" class="form-control" value="{{ old('worker_id', $worker->worker_id) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $worker->name) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $worker->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $worker->email) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $worker->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $worker->joining_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Skill Level <span class="text-danger">*</span></label>
                            <select name="skill_level" class="form-select" required>
                                <option value="beginner" {{ $worker->skill_level == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="intermediate" {{ $worker->skill_level == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="expert" {{ $worker->skill_level == 'expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            @if($worker->photo)
                                <small class="text-muted">Current photo will be replaced if new one is uploaded</small>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $worker->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $worker->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('workers.show', $worker) }}" class="btn btn-light-dark">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Worker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
