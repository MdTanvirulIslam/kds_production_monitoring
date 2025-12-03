@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Workers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add New Worker</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mx-auto layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h3 class="mb-4">➕ Add New Worker</h3>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('workers.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Worker ID <span class="text-danger">*</span></label>
                            <input type="text" name="worker_id" class="form-control" placeholder="e.g., W001" value="{{ old('worker_id') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Worker's full name" value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone number" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email address" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Skill Level <span class="text-danger">*</span></label>
                            <select name="skill_level" class="form-select" required>
                                <option value="beginner" {{ old('skill_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="intermediate" {{ old('skill_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="expert" {{ old('skill_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('workers.index') }}" class="btn btn-light-dark">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Worker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
